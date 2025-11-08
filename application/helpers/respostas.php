<style>
    /* Setinha que gira ao abrir/fechar */
    .chevron {
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 7px solid #6c757d;
        transition: transform .2s ease;
    }

    .btn[aria-expanded="true"] .chevron {
        transform: rotate(180deg);
    }

    .card-header .btn {
        padding: .75rem 1.25rem;
        text-decoration: none;
    }
</style>

<div class="card mb-2">
    <div class="card-header" id="informacoes_envio_header">
        <button class="btn btn-block text-left d-flex align-items-center justify-content-between"
            data-toggle="collapse" data-target="#informacoes_envio" aria-expanded="true" aria-controls="informacoes_envio">
            <h3>Protocolo <?= $envio['protocolo'] ?></h3>
            <span class="chevron"></span>
        </button>
    </div>
    <div id="informacoes_envio" class="collapse show" aria-labelledby="informacoes_envio_header">
        <div class="card-body">
            <p class="text-left"><strong>Empresa:</strong> <?= $envio['empresa'] ?></p>
            <p class="text-left"><strong>Respostas enviadas por:</strong> <?= !empty($envio['nome']) ? $envio['nome'] : 'Anônimo' ?></p>
            <?php if (!empty($envio['email']) && $envio['consent_email']): ?>
                <p class="text-left"><strong>E-mail:</strong> <?= $envio['email'] ?></p>
            <?php endif; ?>
            <?php if (!empty($envio['telefone']) && $envio['consent_tel']): ?>
                <p class="text-left"><strong>Telefone:</strong> <?= Util::mascaraTelefone($envio['telefone']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="card mb-2">
    <div id="resposta_envio_header" class="card-header text-white" style="background-color: <?= $envio['cor'] ?> !important;">
        <button class="btn btn-block text-left d-flex align-items-center justify-content-between text-white"
            data-toggle="collapse" data-target="#resposta_envio" aria-expanded="true" aria-controls="resposta_envio">
            <h3><?= $envio['nome_formulario'] ?></h3>
            <span class="chevron" style="border-top: 7px solid #fff;"></span>
        </button>
    </div>
    <div id="resposta_envio" class="collapse show" aria-labelledby="resposta_envio_header">
        <div class="card-body" style="--base:<?= $envio['cor'] ?>; --pct:30%; background:color-mix(in srgb, var(--base) var(--pct), white); !important">
            <div class="row justify-content-center">
                <div class="col-md-12 text-left">
                    <?php $grupo = '';
                    foreach ($envio['respostas'] as $key => $value):
                        if ($grupo != $value['grupo']): $grupo = $value['grupo']; ?>
                            <h4 class="section-title mb-3"><?= trim(mb_strtoupper($value['grupo'], 'UTF-8')); ?></h4>
                        <?php endif; ?>
                        <div class="mb-4 pb-3">
                            <h5 class="fw-bold"><?= trim($value['pergunta']) ?></h5>
                            <?php if ($value['tipo'] == 'multi_select'):
                                $lista = explode("|:|", $value['resposta']);
                            ?>
                                <ul style="width:80%;margin-left:10%;padding:0">
                                    <?php if (count($lista)) foreach ($lista as $itemLista): ?>
                                        <li><?= trim($itemLista) ?></li>
                                    <?php endforeach ?>
                                </ul>
                            <?php else: ?>
                                <textarea disabled class="form-control" rows="5"><?= trim($value['resposta']) ?></textarea>
                            <?php endif; ?>

                            <?php if (!empty($value['comentario'])): ?>
                                <p class="comentario"><strong>OBS:</strong> <?= trim($value['comentario']) ?></p>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>

                    <?php if (!empty($envio['arquivos'])): ?>
                        <h4>Anexos:</h4>
                        <div class="row">
                            <?php $arquivos = explode('|:|', $envio['arquivos']);
                            foreach ($arquivos as $item):
                            ?>
                                <?= Formulario::render_card($item); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>