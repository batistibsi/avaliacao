<?php
$liberado_interno = isset($liberado_interno) ? $liberado_interno : false;
?>
<style>
    /* Opcional: setinha que gira */
    .panel-title a {
        display: block;
        text-decoration: none !important;
        position: relative;
        padding-right: 24px;
        font-weight: bold;
        color: #000 !important;
    }

    .panel-title a:after {
        content: "\25BC";
        /* setinha ▼ */
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%) rotate(0deg);
        transition: transform .2s;
        font-size: 12px;
    }

    .panel-title a.collapsed:after {
        transform: translateY(-50%) rotate(-90deg);
    }
</style>
<div class="card mb-4">
    <div id="historico_header" class="card-header">
        <button class="btn btn-block text-left d-flex align-items-center justify-content-between"
            data-toggle="collapse" data-target="#historico" aria-expanded="true" aria-controls="historico">
            <h3>Histórico</h3>
            <span class="chevron"></span>
        </button>
    </div>
    <div id="historico" class="collapse show" aria-labelledby="historico_header">
        <div class="card-body">
            <div id="accordion_historico">
                <? $ultima = array_pop($historico);
                if (count($historico)): foreach ($historico as $tarefa): ?>
                        <div class="card">
                            <div class="card-header panel-title" id="heading_historico_<?= $tarefa['id_tarefa'] ?>" style="background-color:<?= $tarefa['cor'] ?> !important">
                                <a data-toggle="collapse" data-target="#collapse_historico_<?= $tarefa['id_tarefa'] ?>" aria-expanded="true" aria-controls="collapse_historico_<?= $tarefa['id_tarefa'] ?>">
                                    <span class="d-flex justify-content-between">
                                        <span class="text-uppercase" style="color:#fff !important"><i class="fa fa-eye mr-2"></i> <?= $tarefa['tipo_tarefa'] ?></span>
                                        <span class="lead"><?= ($liberado_interno) ? $tarefa['executor'] : '' ?> em <?= Util::formatData($tarefa['data_fechamento']) ?> <?= Util::formatHora($tarefa['data_fechamento']) ?></span>
                                    </span>
                                </a>
                            </div>

                            <div id="collapse_historico_<?= $tarefa['id_tarefa'] ?>" class="collapse" aria-labelledby="heading_historico_<?= $tarefa['id_tarefa'] ?>">
                                <div class="card-body text-left" style="--base:<?= $tarefa['cor'] ?>; --pct:30%; background:color-mix(in srgb, var(--base) var(--pct), white); !important">
                                    <p><strong>Comentário:</strong> <?= $tarefa['comentario'] ?></p>
                                    <?php if ($liberado_interno): ?>
                                        <p><strong>Comentário Interno: </strong><?= $tarefa['comentario_interno'] ?></p>
                                    <? endif; ?>
                                </div>
                                <?php if (!empty($tarefa['arquivos'])): ?>
                                    <div class="row">
                                        <?php $arquivos = explode('|:|', $tarefa['arquivos']);
                                        foreach ($arquivos as $item):
                                        ?>
                                            <?= Formulario::render_card($item); ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <? endforeach;
                else: ?>
                    <h6>Ainda não trabalhado</h6>
                <? endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(function() {
            var $all = $('#accordion_historico .collapse');
            $all.last().collapse('show'); // abre o último
        });
    });
</script>