<?php
class Grupo
{
        public static $erro;

        public static function buscaId($id_grupo)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_grupo a where a.id_grupo = " . $id_grupo;

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return $registros[0];
                }

                self::$erro = "Registro não encontrado!";
                return false;
        }

        public static function estatistica($inicio, $fim, $id_grupo)
        {

                $db = Zend_Registry::get('db');

                $where = " where b.data_envio between '" . $inicio . " 00:00:00' and '" . $fim . " 23:59:59' ";
                $where .= !empty($id_grupo) ? " and b.id_grupo in(" . $id_grupo . ") " : "";

                $select = "select avg(a.resposta::integer*a.peso) as total
			from avaliacao_resposta a
			inner join avaliacao_envio b on a.id_envio = b.id_envio
                        inner join avaliacao_grupo c on c.id_grupo = b.id_grupo
			" . $where . ";";

                $registros = $db->fetchAll($select);

                $mediaGeral = 0;

                if (count($registros)) {
                        $mediaGeral = (float) $registros[0]['total'];
                }

                $select = "select count(*) as total
			from avaliacao_envio b
                        inner join avaliacao_grupo c on c.id_grupo = b.id_grupo
			" . $where . ";";

                $registros = $db->fetchAll($select);

                $qtdAvaliacoes = 0;

                if (count($registros)) {
                        $qtdAvaliacoes = (int) $registros[0]['total'];
                }

                $select = "select c.nome, avg(a.resposta::integer*a.peso) as total
			from avaliacao_resposta a
			inner join avaliacao_envio b on a.id_envio = b.id_envio
                        inner join avaliacao_grupo c on c.id_grupo = b.id_grupo
			" . $where . "
			group by c.nome
                        order by total;";

                $registros = $db->fetchAll($select);

                $piorGrupo = false;
                $melhorGrupo = false;
                $mediaPorGrupo = [];

                if (count($registros)) {
                        $piorGrupo = $registros[0];

                        foreach ($registros as $value) {
                                $mediaPorGrupo['labels'][] = $value['nome'];
                                $mediaPorGrupo['valores'][] = (float)round($value['total'], 2);
                        }

                        $melhorGrupo = $value;
                }

                $select = "select to_char(b.data_envio, 'YYYY-MM') as mes, avg(a.resposta::integer*a.peso) as total
			from avaliacao_resposta a
			inner join avaliacao_envio b on a.id_envio = b.id_envio
			" . $where . "
			group by mes;";

                $registros = $db->fetchAll($select);

                $evolucaoGeral = [];

                if (count($registros)) {
                        foreach ($registros as $key => $value) {
                                $evolucaoGeral['labels'][] = $value['mes'];
                                $evolucaoGeral['valores'][] = (float) round($value['total'], 2);
                        }
                }

                $select = "select a.bloco as nome, avg(a.resposta::integer*a.peso) as total
			from avaliacao_resposta a
			inner join avaliacao_envio b on a.id_envio = b.id_envio
                        inner join avaliacao_grupo c on c.id_grupo = b.id_grupo
			" . $where . "
			group by a.bloco
                        order by total;";

                $registros = $db->fetchAll($select);

                $mediaPorDimensao = [];

                if (count($registros)) {
                        foreach ($registros as $value) {
                                $mediaPorDimensao['labels'][] = $value['nome'];
                                $mediaPorDimensao['valores'][] = (float) round($value['total'], 2);
                        }
                }

                $dadosColetivo = [
                        'kpis' => [
                                'mediaGeral'   => $mediaGeral,
                                'melhorGrupo'  => [
                                        'nome'  => $melhorGrupo ? $melhorGrupo['nome'] : 'NE',
                                        'media' => $melhorGrupo ? (float) round($melhorGrupo['total'], 2) : 0
                                ],
                                'piorGrupo'    => [
                                        'nome'  => $piorGrupo ? $piorGrupo['nome'] : 'NE',
                                        'media' => $piorGrupo ? (float) round($piorGrupo['total'], 2) : 0
                                ],
                                'qtdAvaliacoes' => $qtdAvaliacoes,
                        ],
                        'mediaPorGrupo' => $mediaPorGrupo,
                        'mediaPorDimensao' => $mediaPorDimensao,
                        'evolucaoGeral' => $evolucaoGeral,
                ];

                return $dadosColetivo;
        }

        public static function isGerente($id_usuario, $id_grupo)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.id_grupo 
                        from avaliacao_grupo a
                        where a.id_usuario = " . $id_usuario . " 
                        and a.id_grupo = " . $id_grupo;

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return true;
                }

                self::$erro = "Registro não encontrado!";
                return false;
        }

        public static function isGerenteDe($id_usuario, $id_membro)
        {
                $db = Zend_Registry::get('db');

                $select = "select b.id_usuario 
                        from avaliacao_grupo a
                        inner join avaliacao_grupo_usuario b on a.id_grupo = b.id_grupo
                        where a.id_usuario = " . $id_usuario . " 
                        and b.id_usuario = " . $id_membro;

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return true;
                }

                self::$erro = "Registro não encontrado!";
                return false;
        }

        public static function membro($id_usuario)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.id_grupo from avaliacao_grupo_usuario a where a.id_usuario = " . $id_usuario;

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return self::buscaId($registros[0]['id_grupo']);
                }

                self::$erro = "Registro não encontrado!";
                return false;
        }

        public static function uniqueNome($id_grupo, $nome)
        {
                $db = Zend_Registry::get('db');

                $select = "select * from avaliacao_grupo where ativo and id_grupo <> " . $id_grupo . " and nome = '" . $nome . "'";

                $registros = $db->fetchAll($select);

                if (count($registros) == 0) return true;

                return false;
        }


        public static function insert($nome, $campos, $membros)
        {
                if (strlen($nome) > 255 || strlen($nome) < 2) {
                        self::$erro = 'Nome inválido!';
                        return false;
                }

                if (!self::uniqueNome(0, $nome)) {
                        self::$erro = 'Já existe um registro com o nome: ' . $nome . '.';
                        return false;
                }

                $db = Zend_Registry::get('db');

                $data = array(
                        "nome" => $nome,
                        "id_usuario" => $campos['id_usuario'],
                        "id_formulario" => $campos['id_formulario']
                );

                $db->insert("avaliacao_grupo", $data);

                $select = "select a.id_grupo from avaliacao_grupo a where a.nome = '" . $nome . "'";

                $retorno = $db->fetchAll($select);

                $id_grupo = $retorno[0]['id_grupo'];

                if (count($membros)) {
                        foreach ($membros as $value) {
                                $db->delete('avaliacao_grupo_usuario', 'id_usuario=' . $value);
                                $data = array(
                                        "id_grupo" => $id_grupo,
                                        "id_usuario" => $value
                                );
                                $db->insert("avaliacao_grupo_usuario", $data);
                        }
                }

                return true;
        }

        public static function update($id_grupo, $nome, $campos, $membros)
        {
                if (strlen($nome) > 255 || strlen($nome) < 2) {
                        self::$erro = 'Nome inválido!';
                        return false;
                }

                if (!self::uniqueNome($id_grupo, $nome)) {
                        self::$erro = 'Já existe um registro com o nome: ' . $nome . '.';
                        return false;
                }

                $db = Zend_Registry::get('db');

                $data = array(
                        "nome" => $nome,
                        "id_usuario" => $campos['id_usuario'],
                        "id_formulario" => $campos['id_formulario']
                );

                $db->update("avaliacao_grupo", $data, "id_grupo = " . $id_grupo);

                $db->delete('avaliacao_grupo_usuario', 'id_grupo=' . $id_grupo);

                if (count($membros)) {
                        foreach ($membros as $value) {
                                $db->delete('avaliacao_grupo_usuario', 'id_usuario=' . $value);
                                $data = array(
                                        "id_grupo" => $id_grupo,
                                        "id_usuario" => $value
                                );
                                $db->insert("avaliacao_grupo_usuario", $data);
                        }
                }

                return true;
        }

        public static function desativar($id_grupo)
        {
                $db = Zend_Registry::get('db');

                $data = array(
                        "ativo" => false
                );

                $db->update("avaliacao_grupo", $data, "id_grupo = " . $id_grupo);

                return true;
        }



        public static function lista()
        {
                $db = Zend_Registry::get('db');

                $where = Zend_Registry::get('permissao') > 1 ? " and a.id_usuario = " . Zend_Registry::get('id_usuario') : "";

                $select = "select a.* from avaliacao_grupo a where a.ativo " . $where . " order by a.nome";

                $retorno = $db->fetchAll($select);

                return $retorno;
        }

        public static function membros($id_grupo)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.*, b.nome, b.email
                                from avaliacao_grupo_usuario a
                                inner join avaliacao_usuario b on a.id_usuario = b.id_usuario 
                                where a.id_grupo = " . $id_grupo . "
                                order by b.nome";

                $retorno = $db->fetchAll($select);

                $arrAux = [];

                if (count($retorno)) {
                        foreach ($retorno as $value) {
                                $arrAux[$value['id_usuario']] = $value;
                        }
                }

                return $arrAux;
        }
}
