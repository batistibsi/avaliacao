<?php
class Tarefa
{
        public const NAO_INICIADA = 1;
        public static $erro;

        public static $max_size_file = 5;

        public static function buscaId($id_tarefa)
        {
                $db = Zend_Registry::get('db');

                $where = " where a.id_tarefa = " . $id_tarefa;

                $select = self::getSelect($where);

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return $registros[0];
                }

                self::$erro = "Registro não encontrado!";
                return false;
        }

        public static function uploadArquivos()
        {
                // 3) Processar múltiplos arquivos: 'arquivos[]'
                $arquivosSalvos = [];
                if (!empty($_FILES['arquivos'])) {
                        if (count($_FILES) > 10) die('Limite de arquivos exedidos, máximo de 10');
                        $destinoBase = '/var/www/avaliacao/uploads/' . date('Ymd');
                        if (!is_dir($destinoBase)) mkdir($destinoBase, 0775, true);
                        $files = $_FILES['arquivos'];
                        for ($i = 0; $i < count($files['name']); $i++) {
                                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

                                $tmp  = $files['tmp_name'][$i];
                                $name = $files['name'][$i];
                                $type = $files['type'][$i];

                                // validação simples
                                $permitidos = ['image/jpeg', 'image/png', 'application/pdf'];
                                if (!in_array($type, $permitidos)) continue;

                                $ext = pathinfo($name, PATHINFO_EXTENSION);
                                $base = pathinfo($name, PATHINFO_FILENAME);
                                $base = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $base);

                                $final = $base . '_' . date('Ymd_His') . '_' . substr(uniqid('', true), -6) . '.' . $ext;
                                $alvo = $destinoBase . '/' . $final;

                                if (move_uploaded_file($tmp, $alvo)) {
                                        $arquivosSalvos[] = date('Ymd') . '/' . $final;
                                        // aqui você pode gravar em tabela de anexos, relacionando com a tarefa
                                }
                        }
                }

                return $arquivosSalvos;
        }


        public static function salvar($id_tarefa, $campos, $id_proximo_passo = null)
        {
                $db = Zend_Registry::get('db');

                $db->beginTransaction();

                $tarefa = self::buscaId($id_tarefa);

                if (!$tarefa) {
                        return false;
                }

                $id_envio = $tarefa['id_envio'];

                if (Zend_Registry::get('permissao') != 1) {
                        $id_proximo_passo = self::buscaProximoPasso($tarefa['id_tipo_tarefa'], Zend_Registry::get('permissao'));
                }

                if (!$id_proximo_passo) {
                        die('Proximo passo não definido');
                }

                $result = self::fechar($id_tarefa, $campos);

                if ($result) {
                        $result = self::criar($id_envio, $id_proximo_passo);
                }

                if ($result) {
                        $db->commit();
                        if ($tarefa['consent_email'] && !empty($tarefa['email'])) {

                                $protocolo = Formulario::gerarProtocolo($tarefa['id_envio'], $tarefa['id_empresa']);

                                $link_descadastrar = Formulario::urlDescadastrar($tarefa['id_empresa'], $protocolo);
                                $link = Empresa::urlSite($tarefa['id_empresa'], 'protocolo.php') . '&protocolo=' . $protocolo;

                                $msg = '<p>Hey, você tem atualização no seu protocolo <strong>' . $protocolo . '</strong>.'
                                        . '<p>Clique aqui e confira:</p>'
                                        . '<p><a target="_blank" href="' . $link . '">' . $link . '</a></p>'
                                        . '<br></br><p>Para não receber mais mensagens, clique neste link:</p>'
                                        . '<p><a target="_blank" href="' . $link_descadastrar . '">' . $link_descadastrar . '</a></p>';

                                Email::enviar($tarefa['email'], 'Movimento no Protocolo', $msg);
                        }
                }

                return $result;
        }

        public static function buscaProximoPasso($id_tipo_tarefa, $id_perfil)
        {
                $db = Zend_Registry::get('db');

                $select = "SELECT id_tipo_tarefa_sequencia
                                FROM avaliacao_tipo_tarefa_permissao
                                where id_tipo_tarefa = " . $id_tipo_tarefa . " and id_perfil = " . $id_perfil . ";";

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return $registros[0]['id_tipo_tarefa_sequencia'];
                }

                self::$erro = "Proximo Passo não encontrado!";
                return false;
        }

        public static function listaTipos($kanban = true)
        {
                $db = Zend_Registry::get('db');

                $where = $kanban ? " where a.kanban " : " where a.id_tipo_tarefa <> " . self::NAO_INICIADA . " ";

                $join = "";
                if (Zend_Registry::get('permissao') > 1) {
                        $join .= " inner join avaliacao_tipo_tarefa_permissao p on a.id_tipo_tarefa = p.id_tipo_tarefa and p.realizar and p.id_perfil =  " . Zend_Registry::get('permissao') . " ";
                }

                $select = "select * from avaliacao_tipo_tarefa a " . $join . $where . ' order by a.id_tipo_tarefa ';

                $registros = $db->fetchAll($select);

                $arrAux = [];

                if (count($registros)) {
                        foreach ($registros as $key => $value) {
                                $arrAux[$value['id_tipo_tarefa']] = $value;
                        }
                }

                return $arrAux;
        }

        public static function criar($id_envio, $id_tipo_tarefa)
        {
                $db = Zend_Registry::get('db');

                $data = array('id_envio' => $id_envio, 'id_tipo_tarefa' => $id_tipo_tarefa);

                $db->insert('avaliacao_tarefa', $data);

                return true;
        }

        public static function fechar($id_tarefa, $campos)
        {
                $db = Zend_Registry::get('db');

                $data = array(
                        'comentario' => $campos['comentario'],
                        'comentario_interno' => $campos['comentario_interno'],
                        'data_fechamento' => date('Y-m-d H:i:s'),
                        "id_usuario" => Zend_Registry::get('id_usuario'),
                        'arquivos' => $campos['arquivos']
                );

                $db->update('avaliacao_tarefa', $data, ' id_tarefa = ' . $id_tarefa);

                return true;
        }

        private static function getSelect($where)
        {
                $join = "";

                if (Zend_Registry::get('permissao') > 1) {
                        $where .= " and c.id_empresa = " . Zend_Registry::get('id_empresa') . " ";
                        $join .= " inner join avaliacao_tipo_tarefa_permissao p on a.id_tipo_tarefa = p.id_tipo_tarefa and p.id_perfil = " . Zend_Registry::get('permissao') . " ";
                }

                $query = "select a.*,
                                c.consent_email,
                                c.email,
                                b.nome as tipo_tarefa,
                                b.kanban,
                                b.cor,
                                c.data_envio,
                                c.id_empresa,
                                d.descricao as formulario,
                                e.titulo as empresa,
                                u.nome as executor
                        from avaliacao_tarefa a 
                        inner join avaliacao_tipo_tarefa b on a.id_tipo_tarefa = b.id_tipo_tarefa
                        inner join avaliacao_envio c on a.id_envio = c.id_envio
                        inner join avaliacao_formulario d on c.id_formulario = d.id_formulario
                        inner join avaliacao_empresa e on e.id_empresa = c.id_empresa 
                        " . $join . "
                        left join avaliacao_usuario u on a.id_usuario = u.id_usuario
                        " . $where . "
                        order by c.data_envio, a.data_abertura";

                return $query;
        }

        public static function listar()
        {
                $db = Zend_Registry::get('db');

                $where = " where a.data_fechamento is null ";

                if (Zend_Registry::get('permissao') > 1) {
                        $where .= " and p.realizar ";
                }

                $select = self::getSelect($where);

                $registros = $db->fetchAll($select);

                $arrAux = [];

                if (count($registros)) {
                        foreach ($registros as $value) {
                                if (!isset($arrAux[$value['id_tipo_tarefa']])) $arrAux[$value['id_tipo_tarefa']] = [];
                                $value['protocolo'] = Formulario::gerarProtocolo($value['id_envio'], $value['id_empresa']);
                                $arrAux[$value['id_tipo_tarefa']][] = $value;
                        }
                }

                return $arrAux;
        }

        public static function historico($id_envio)
        {
                $db = Zend_Registry::get('db');

                $where = " where a.id_envio = " . $id_envio;

                if (Zend_Registry::get('permissao') > 1) {
                        $where .= " and p.visualizar ";
                }

                $select = self::getSelect($where);

                $registros = $db->fetchAll($select);

                return $registros;
        }
}
