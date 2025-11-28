<?php
class Envio
{
        public static $erro;

        public static function buscaId($id_envio)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_envio a where a.id_envio = " . $id_envio;

                $registros = $db->fetchAll($select);

                if (count($registros)) {
                        return $registros[0];
                }

                self::$erro = "Registro não encontrado!";
                return false;
        }

        public static function buscaRespostas($id_envio)
        {
                $db = Zend_Registry::get('db');

                $select = "select a.* from avaliacao_resposta a where a.id_envio = " . $id_envio;

                $registros = $db->fetchAll($select);

                return $registros;
        }

        public static function normalizePerguntasFiles($root)
        {
                $result = [];
                if (empty($root) || !isset($root['name'])) {
                        return $result;
                }
                foreach ($root['name'] as $qid => $level1) {
                        foreach ($level1 as $fieldName => $fieldValue) {
                                // anexos[] vem como array, audio vem como string
                                if (is_array($fieldValue)) {
                                        // anexos
                                        foreach ($fieldValue as $idx => $name) {
                                                $error = $root['error'][$qid][$fieldName][$idx];
                                                if ($error === UPLOAD_ERR_NO_FILE) continue;
                                                $result[$qid][$fieldName][] = [
                                                        'name'     => $name,
                                                        'type'     => $root['type'][$qid][$fieldName][$idx],
                                                        'tmp_name' => $root['tmp_name'][$qid][$fieldName][$idx],
                                                        'error'    => $error,
                                                        'size'     => $root['size'][$qid][$fieldName][$idx],
                                                ];
                                        }
                                } else {
                                        // campo único (audio)
                                        $error = $root['error'][$qid][$fieldName];
                                        if ($error === UPLOAD_ERR_NO_FILE) continue;
                                        $result[$qid][$fieldName] = [
                                                'name'     => $fieldValue,
                                                'type'     => $root['type'][$qid][$fieldName],
                                                'tmp_name' => $root['tmp_name'][$qid][$fieldName],
                                                'error'    => $error,
                                                'size'     => $root['size'][$qid][$fieldName],
                                        ];
                                }
                        }
                }
                return $result;
        }


        public static function insert($campos, $respostas)
        {

                if (!$respostas || !count($respostas)) {
                        self::$erro = "Não há respostas no envio!";
                        return false;
                }
                if (!$campos['id_formulario']) {
                        self::$erro = "Formulario não informado!";
                        return false;
                }
                $estrutura = Formulario::buscarBlocos($campos['id_formulario']);

                if (!$estrutura) {
                        self::$erro = "Estrutura de formulario não encontrada!";
                        return false;
                }

                if (!$campos['id_grupo']) {
                        self::$erro = "Grupo não informado!";
                        return false;
                }

                if (!$campos['id_usuario']) {
                        self::$erro = "Usuário não informado!";
                        return false;
                }
                if (!$campos['id_avaliador']) {
                        self::$erro = "Avaliador não informado!";
                        return false;
                }
                if (!$campos['data_envio']) {
                        self::$erro = "Data não informada!";
                        return false;
                }

                $uploadBaseDir = '/var/www/avaliacao/uploads';

                $db = Zend_Registry::get('db');
                $db->beginTransaction();

                $query = "INSERT INTO avaliacao_envio(id_grupo, id_formulario, id_usuario, id_avaliador, data_envio)
                        VALUES (" . $campos['id_grupo'] . ",
                                " . $campos['id_formulario'] . ",
                                " . $campos['id_usuario'] . ",
                                " . $campos['id_avaliador'] . ",
                                '" . $campos['data_envio'] . "')
                    	RETURNING id_envio;";

                $registros = $db->fetchAll($query);

                $id_envio = $registros[0]['id_envio'];

                // Garante a pasta base da avaliação
                $avaliacaoDir = $uploadBaseDir . '/' . date('Ymd') . '/' . $id_envio;
                if (!is_dir($avaliacaoDir) && !mkdir($avaliacaoDir, 0775, true) && !is_dir($avaliacaoDir)) {
                        throw new RuntimeException('Não foi possível criar a pasta de uploads.');
                }

                // ==== 2) NORMALIZA FILES POR PERGUNTA ====
                $filesByPergunta = [];
                if (isset($_FILES['itens'])) {
                        $filesByPergunta = self::normalizePerguntasFiles($_FILES['itens']);
                }

                foreach ($estrutura as $bloco) {
                        if (!$bloco['itens'] || !count($bloco['itens'])) continue;

                        foreach ($bloco['itens'] as $pergunta) {

                                $id_pergunta = $pergunta['id_pergunta'];

                                if (!isset($respostas[$id_pergunta]['nota'])) {
                                        self::$erro = "Problemas ao resgatar a resposta";
                                        $db->rollback();
                                        return false;
                                }

                                $query = "INSERT INTO avaliacao_resposta(id_envio, pergunta, bloco, resposta, observacao, peso)
                                        VALUES (" . $id_envio . ",
                                                '" . $pergunta['pergunta'] . "',
                                                '" . $bloco['nome'] . "',
                                                " . $respostas[$id_pergunta]['nota'] . ",
                                                '" . $respostas[$id_pergunta]['comentario'] . "',
                                                " . $pergunta['peso'] . ")
                                        RETURNING id_resposta;";

                                $registros = $db->fetchAll($query);

                                $id_resposta = $registros[0]['id_resposta'];

                                // 3.2) arquivos da pergunta
                                $files = $filesByPergunta[$id_pergunta] ?? [];

                                // Pasta específica da pergunta
                                $perguntaDir = $avaliacaoDir . '/' . $id_pergunta;
                                if (!is_dir($perguntaDir) && !mkdir($perguntaDir, 0775, true) && !is_dir($perguntaDir)) {
                                        throw new RuntimeException("Não foi possível criar a pasta da pergunta {$qid}.");
                                }

                                // anexos[]
                                if (!empty($files['anexos'])) {
                                        foreach ($files['anexos'] as $file) {
                                                if ($file['error'] !== UPLOAD_ERR_OK) {
                                                        // pode ignorar ou lançar erro; aqui vamos ignorar com segurança
                                                        continue;
                                                }

                                                $nomeOriginal = $file['name'];
                                                $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                                                $nomeDestino = uniqid('anexo_', true) . ($ext ? '.' . $ext : '');
                                                $caminhoDestino = $perguntaDir . '/' . $nomeDestino;

                                                if (!move_uploaded_file($file['tmp_name'], $caminhoDestino)) {
                                                        throw new RuntimeException("Falha ao mover anexo da pergunta {$qid}.");
                                                }

                                                $query = "INSERT INTO avaliacao_arquivo(id_resposta, tipo, nome_original, caminho_arquivo, mime_type, tamanho_bytes)
                                                        VALUES (" . $id_resposta . ",
                                                                'anexo',
                                                                '" . $nomeOriginal . "',
                                                                '" . $caminhoDestino . "',
                                                                '" . $file['type'] . "',
                                                                " . $file['size'] . ")
                                                        RETURNING id_arquivo;";

                                                $registros = $db->fetchAll($query);
                                        }
                                }

                                // audio
                                if (!empty($files['audio'])) {
                                        $file = $files['audio'];
                                        if ($file['error'] === UPLOAD_ERR_OK) {
                                                $nomeOriginal = $file['name'] ?: ('audio_' . $qid . '.webm');
                                                $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                                                if (!$ext) $ext = 'webm';

                                                $nomeDestino = uniqid('audio_', true) . '.' . $ext;
                                                $caminhoDestino = $perguntaDir . '/' . $nomeDestino;

                                                if (!move_uploaded_file($file['tmp_name'], $caminhoDestino)) {
                                                        throw new RuntimeException("Falha ao mover áudio da pergunta {$qid}.");
                                                }

                                                $query = "INSERT INTO avaliacao_arquivo(id_resposta, tipo, nome_original, caminho_arquivo, mime_type, tamanho_bytes)
                                                        VALUES (" . $id_resposta . ",
                                                                'audio',
                                                                '" . $nomeOriginal . "',
                                                                '" . $caminhoDestino . "',
                                                                '" . $file['type'] . "',
                                                                " . $file['size'] . ")
                                                        RETURNING id_arquivo;";

                                                $registros = $db->fetchAll($query);
                                        }
                                }
                        }
                }

                $db->commit();

                return true;
        }

        public static function buscaNota($id_envio)
        {

                $respostas = self::buscaRespostas($id_envio);

                $nota = 0;

                if (count($respostas)) {
                        foreach ($respostas as $key => $value) {
                                $nota += ((int)$value['resposta'] * (int)$value['peso']);
                        }
                }

                return $nota;
        }

        public static function lista($id_usuario = false)
        {
                $db = Zend_Registry::get('db');

                $where = $id_usuario ? " where a.id_usuario = " . $id_usuario . " " : "";

                $select = "select a.*,
                                b.nome as formulario,
                                c.nome as grupo,
                                d.nome as avaliador
                                from avaliacao_envio a 
                                inner join avaliacao_formulario b on a.id_formulario = b.id_formulario
                                inner join avaliacao_grupo c on a.id_grupo = c.id_grupo
                                inner join avaliacao_usuario d on a.id_avaliador = d.id_usuario
                                " . $where . " 
                                order by a.data_envio";

                $retorno = $db->fetchAll($select);

                if (count($retorno)) {
                        foreach ($retorno as $key => $value) {
                                $retorno[$key]['nota'] = self::buscaNota($value['id_envio']);
                        }
                }

                return $retorno;
        }
}
