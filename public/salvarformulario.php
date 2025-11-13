<?php
// api-avaliacoes-blocos.php

// ==== CONFIGURAÇÃO BÁSICA ====
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Método não permitido']);
    exit;
}

// Ajuste conexão:
$dsn  = 'pgsql:host=localhost;port=5432;dbname=seu_banco';
$user = 'seu_usuario';
$pass = 'sua_senha';

// Pasta onde os arquivos serão salvos
$uploadBaseDir = __DIR__ . '/uploads/avaliacoes'; // lembre de dar permissão de escrita

// ==== FUNÇÃO PARA NORMALIZAR $_FILES['perguntas'] ====
/**
 * Transforma a estrutura complexa de $_FILES['perguntas'] em:
 * [
 *   'q1' => [
 *      'anexos' => [ [name,type,tmp_name,error,size], ... ],
 *      'audio'  => [name,type,tmp_name,error,size]
 *   ],
 *   ...
 * ]
 */
function normalizePerguntasFiles(array $root) {
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

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->beginTransaction();

    // ==== 1) REGISTRO PRINCIPAL DA AVALIACAO ====
    $anonimo   = filter_var($_POST['anonimo'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
    $origem    = $_POST['origem'] ?? null;
    $timestamp = $_POST['timestamp'] ?? null;

    $ip        = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // created_at: se vier timestamp ISO, usa; senão now()
    $createdAt = $timestamp ? date('Y-m-d H:i:sP', strtotime($timestamp)) : date('Y-m-d H:i:sP');

    // cria um protocolo simples: AV-YYYYMMDD-HHMMSS-XXXX
    $stmt = $pdo->query("SELECT nextval('avaliacoes_id_seq')"); // ou sua sequence
    $nextId = (int)$stmt->fetchColumn();
    $protocolo = 'AV-' . date('Ymd-His') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("
        INSERT INTO avaliacoes (id, protocolo, anonimo, origem, created_at, ip, user_agent)
        VALUES (:id, :protocolo, :anonimo, :origem, :created_at, :ip, :user_agent)
        RETURNING id
    ");
    $stmt->execute([
        ':id'         => $nextId,
        ':protocolo'  => $protocolo,
        ':anonimo'    => $anonimo,
        ':origem'     => $origem,
        ':created_at' => $createdAt,
        ':ip'         => $ip,
        ':user_agent' => $userAgent,
    ]);
    $avaliacaoId = (int)$stmt->fetchColumn();

    // Garante a pasta base da avaliação
    $avaliacaoDir = $uploadBaseDir . '/' . $protocolo;
    if (!is_dir($avaliacaoDir) && !mkdir($avaliacaoDir, 0775, true) && !is_dir($avaliacaoDir)) {
        throw new RuntimeException('Não foi possível criar a pasta de uploads.');
    }

    // ==== 2) NORMALIZA FILES POR PERGUNTA ====
    $filesByPergunta = [];
    if (isset($_FILES['perguntas'])) {
        $filesByPergunta = normalizePerguntasFiles($_FILES['perguntas']);
    }

    // ==== 3) PERCORRE PERGUNTAS ====
    $perguntas = $_POST['perguntas'] ?? [];

    $stmtPerg = $pdo->prepare("
        INSERT INTO avaliacoes_perguntas (avaliacao_id, pergunta_id, nota, comentario)
        VALUES (:avaliacao_id, :pergunta_id, :nota, :comentario)
        RETURNING id
    ");

    $stmtArq = $pdo->prepare("
        INSERT INTO avaliacoes_arquivos (avaliacao_pergunta_id, tipo, nome_original, caminho_arquivo, mime_type, tamanho_bytes)
        VALUES (:avaliacao_pergunta_id, :tipo, :nome_original, :caminho_arquivo, :mime_type, :tamanho_bytes)
    ");

    foreach ($perguntas as $qid => $dadosPerg) {
        $qid = (string)$qid; // ex: "q1", "q2"...

        $nota = isset($dadosPerg['nota']) ? (int)$dadosPerg['nota'] : null;
        if (!$nota || $nota < 1 || $nota > 10) {
            throw new InvalidArgumentException("Nota inválida para pergunta {$qid}");
        }

        $comentario = trim($dadosPerg['comentario'] ?? '');

        // 3.1) grava pergunta
        $stmtPerg->execute([
            ':avaliacao_id' => $avaliacaoId,
            ':pergunta_id'  => $qid,
            ':nota'         => $nota,
            ':comentario'   => $comentario !== '' ? $comentario : null,
        ]);
        $avaliacaoPergId = (int)$stmtPerg->fetchColumn();

        // Pasta específica da pergunta
        $perguntaDir = $avaliacaoDir . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $qid);
        if (!is_dir($perguntaDir) && !mkdir($perguntaDir, 0775, true) && !is_dir($perguntaDir)) {
            throw new RuntimeException("Não foi possível criar a pasta da pergunta {$qid}.");
        }

        // 3.2) arquivos da pergunta
        $files = $filesByPergunta[$qid] ?? [];

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

                $stmtArq->execute([
                    ':avaliacao_pergunta_id' => $avaliacaoPergId,
                    ':tipo'                  => 'anexo',
                    ':nome_original'         => $nomeOriginal,
                    ':caminho_arquivo'       => $caminhoDestino,
                    ':mime_type'             => $file['type'] ?? null,
                    ':tamanho_bytes'         => $file['size'] ?? null,
                ]);
            }
        }

        // audio
        if (!empty($files['audio'])) {
            $file = $files['audio'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $nomeOriginal = $file['name'] ?: ('audio_'.$qid.'.webm');
                $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                if (!$ext) $ext = 'webm';

                $nomeDestino = uniqid('audio_', true) . '.' . $ext;
                $caminhoDestino = $perguntaDir . '/' . $nomeDestino;

                if (!move_uploaded_file($file['tmp_name'], $caminhoDestino)) {
                    throw new RuntimeException("Falha ao mover áudio da pergunta {$qid}.");
                }

                $stmtArq->execute([
                    ':avaliacao_pergunta_id' => $avaliacaoPergId,
                    ':tipo'                  => 'audio',
                    ':nome_original'         => $nomeOriginal,
                    ':caminho_arquivo'       => $caminhoDestino,
                    ':mime_type'             => $file['type'] ?? null,
                    ':tamanho_bytes'         => $file['size'] ?? null,
                ]);
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        'ok'         => true,
        'protocolo'  => $protocolo,
        'avaliacao_id' => $avaliacaoId,
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'ok'   => false,
        'erro' => 'Erro ao processar avaliação',
        'msg'  => $e->getMessage(), // em produção talvez você oculte
    ]);
}


/*

CREATE TABLE avaliacoes (
    id              bigserial PRIMARY KEY,
    protocolo       varchar(40) UNIQUE NOT NULL,
    anonimo         boolean NOT NULL DEFAULT true,
    origem          varchar(100),
    created_at      timestamptz NOT NULL DEFAULT now(),
    ip              inet,
    user_agent      text
);

CREATE TABLE avaliacoes_perguntas (
    id              bigserial PRIMARY KEY,
    avaliacao_id    bigint NOT NULL REFERENCES avaliacoes(id) ON DELETE CASCADE,
    pergunta_id     varchar(100) NOT NULL,
    nota            smallint NOT NULL CHECK (nota BETWEEN 1 AND 10),
    comentario      text
);

CREATE TABLE avaliacoes_arquivos (
    id                      bigserial PRIMARY KEY,
    avaliacao_pergunta_id   bigint NOT NULL REFERENCES avaliacoes_perguntas(id) ON DELETE CASCADE,
    tipo                    varchar(20) NOT NULL, -- 'anexo' ou 'audio'
    nome_original           text NOT NULL,
    caminho_arquivo         text NOT NULL,
    mime_type               text,
    tamanho_bytes           bigint
);


*/
