<?php
// ====== EXEMPLO de entrada (substitua pelo seu) ======
$blocos = [
  [
    'titulo' => 'Atendimento',
    'perguntas' => [
      ['id' => 'q1', 'texto' => 'Como você avalia o atendimento?'],
      ['id' => 'q2', 'texto' => 'Agilidade do suporte?'],
    ],
  ],
  [
    'titulo' => 'Produto',
    'perguntas' => [
      ['id' => 'q3', 'texto' => 'Qualidade do produto?'],
      ['id' => 'q4', 'texto' => 'Custo-benefício?'],
    ],
  ],
];
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Avaliação por Blocos (Anônimo)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap 4 -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

  <style>
    /* Base claro */
    body{ background:#f7fafc; color:#1f2937; }
    .card{ border:1px solid #e5e7eb; border-radius:1rem; box-shadow:0 8px 22px rgba(0,0,0,.06); }
    .muted{ color:#6b7280; }

    /* Rostos (1 linha + scroll) */
    .faces-group{ display:flex; flex-wrap:nowrap; overflow-x:auto; -webkit-overflow-scrolling:touch; padding-bottom:.25rem; margin-bottom:.25rem; scrollbar-width:thin; }
    .faces-group::-webkit-scrollbar{ height:6px; }
    .faces-group::-webkit-scrollbar-thumb{ background:#cbd5e1; border-radius:999px; }
    .faces-group::-webkit-scrollbar-track{ background:transparent; }
    .face-item{ position:relative; margin:.35rem; flex:0 0 auto; }
    .face-item input{ position:absolute; opacity:0; pointer-events:none; }
    .face-item label{ width:56px; height:56px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:28px; line-height:1; cursor:pointer; user-select:none; background:#fff; border:1px solid #e5e7eb; transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease; }
    .face-item label:hover{ transform:translateY(-2px); box-shadow:0 8px 18px rgba(0,0,0,.08); }
    .face-item input:focus + label{ box-shadow:0 0 0 .2rem rgba(59,130,246,.35); }
    .face-item input:checked + label{ color:#111827; border-color:transparent; box-shadow:0 10px 22px rgba(0,0,0,.10); }
    @media (max-width: 576px){ .face-item{margin:.25rem;} .face-item label{width:48px;height:48px;font-size:24px;border-radius:12px;} }
    @media (max-width: 360px){ .face-item{margin:.2rem;}  .face-item label{width:42px;height:42px;font-size:22px;border-radius:10px;} }

    /* Barra/Badge */
    .badge-score{ font-weight:700; background:#111827; color:#fff; border-radius:.5rem; padding:.2rem .55rem; font-size:.95rem; }
    .progress{ height:.6rem; border-radius:999px; background:#e5e7eb; }
    .progress-bar{ transition:width .25s ease; }

    /* Botões */
    .btn-brand{ background:linear-gradient(135deg,#60a5fa,#34d399); color:#0b1220; border:0; border-radius:.8rem; font-weight:700; padding:.55rem 1.1rem; transition:transform .12s ease, box-shadow .12s ease; }
    .btn-brand:hover{ transform:translateY(-1px); box-shadow:0 10px 18px rgba(0,0,0,.12); }

    /* Uploader */
    .uploader{ border:2px dashed #cbd5e1; background:#fff; border-radius:.8rem; padding:1rem; transition: border-color .15s ease, background .15s ease; }
    .uploader.dragover{ border-color:#60a5fa; background:#eef6ff; }
    .uploader .hint{ color:#6b7280; }
    .file-list .file-item{ display:flex; align-items:center; justify-content:space-between; border:1px solid #e5e7eb; background:#fff; border-radius:.5rem; padding:.5rem .75rem; margin-top:.5rem; }
    .file-name{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:65%; }

    /* Gravador de Áudio */
    .recorder{ border:1px solid #e5e7eb; background:#fff; border-radius:.8rem; padding:1rem; }
    .rec-btn{ width:56px; height:56px; border-radius:50%; border:0; outline:0; background:#ef4444; color:#fff; font-weight:700; }
    .rec-btn.recording{ animation:pulse 1.1s infinite; }
    @keyframes pulse{ 0%{box-shadow:0 0 0 0 rgba(239,68,68,.6);} 70%{box-shadow:0 0 0 14px rgba(239,68,68,0);} 100%{box-shadow:0 0 0 0 rgba(239,68,68,0);} }
    .rec-timer{ font-variant-numeric:tabular-nums; }

    /* Collapse header */
    .collapse-toggle{ font-weight:600; }
    .collapse-toggle .arrow{ display:inline-block; transition:transform .15s ease; }
    .collapse-toggle[aria-expanded="true"] .arrow{ transform:rotate(90deg); }

    /* Layout perguntas */
    .question{ padding:1rem; border:1px dashed #e5e7eb; border-radius:.8rem; margin-bottom:1rem; background:#fff; }
    .block-title{ margin:1.25rem 0 .75rem; }
  </style>
</head>
<body>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card">
        <div class="card-body p-4 p-md-5">
          <h4 class="mb-3">Avaliação</h4>
          <p class="muted mb-4">Cada pergunta possui as mesmas opções: rostos 1–10, comentário, anexos e áudio (todos opcionais, exceto a nota).</p>

          <form id="formAvaliacao" novalidate>
            <?php foreach ($blocos as $bIndex => $bloco): ?>
              <h5 class="block-title"><?= htmlspecialchars($bloco['titulo']) ?></h5>

              <?php foreach ($bloco['perguntas'] as $pIndex => $p): 
                $qid = htmlspecialchars($p['id'] ?? ('q'.$bIndex.'_'.$pIndex));
                $texto = htmlspecialchars($p['texto']);
              ?>
              <div class="question bg-light" data-qid="<?= $qid ?>">
                <div class="d-flex align-items-center mb-2">
                  <span class="badge badge-secondary mr-2"><?= ($pIndex+1) ?></span>
                  <strong><?= $texto ?></strong>
                </div>

                <!-- Rostos 1..10 -->
                <div class="faces-group" role="radiogroup" aria-label="Avaliação 1 a 10" data-qid="<?= $qid ?>"></div>

                <div class="d-flex align-items-center mt-2">
                  <div class="flex-grow-1">
                    <div class="progress"><div class="progress-bar" style="width:0%" aria-valuemin="0" aria-valuemax="100"></div></div>
                  </div>
                  <div class="ml-3"><span class="badge-score" data-role="badge">–</span></div>
                </div>

                <div class="d-flex justify-content-between mt-2">
                  <small class="muted">Muito ruim</small><small class="muted">Excelente</small>
                </div>
                <div class="text-danger mt-1 d-none" data-role="erro-nota">Selecione uma nota (1 a 10).</div>

                <!-- Toggle Collapse -->
                <div class="mt-2">
                  <button class="btn btn-link collapse-toggle p-0" type="button" data-toggle="collapse" data-target="#extras-<?= $qid ?>" aria-expanded="false" aria-controls="extras-<?= $qid ?>">
                    <span class="arrow">▸</span> Adicionar detalhes (opcional)
                  </button>
                </div>

                <!-- Extras (fechado) -->
                <div id="extras-<?= $qid ?>" class="collapse mt-3">
                  <!-- Comentário -->
                  <div class="form-group mb-3">
                    <label for="comentario-<?= $qid ?>">Comentário</label>
                    <textarea id="comentario-<?= $qid ?>" class="form-control" rows="3" name="perguntas[<?= $qid ?>][comentario]" placeholder="O que motivou essa avaliação?"></textarea>
                    <small class="form-text text-muted">Máx. 500 caracteres.</small>
                  </div>

                  <!-- Uploader -->
                  <div class="form-group mb-3">
                    <label class="font-weight-bold mb-2">Anexos</label>
                    <div class="uploader text-center" data-role="uploader" data-qid="<?= $qid ?>">
                      <p class="mb-2 font-weight-bold">Arraste arquivos aqui</p>
                      <p class="hint mb-2">ou</p>
                      <button type="button" class="btn btn-outline-secondary btn-sm" data-action="select-files">Selecionar arquivos</button>
                      <input type="file" class="d-none" multiple data-role="file-input">
                      <div class="mt-2 small text-muted">Até 5 arquivos • Máx. 10 MB cada • Imagens, PDF, áudio, vídeo</div>
                    </div>
                    <div class="file-list" data-role="file-list"></div>
                    <div class="text-danger mt-1 d-none" data-role="erro-arquivos"></div>
                  </div>

                  <!-- Áudio -->
                  <div class="form-group">
                    <label class="font-weight-bold mb-2 d-block">Gravar áudio</label>
                    <div class="recorder d-flex align-items-center" data-role="recorder" data-qid="<?= $qid ?>">
                      <button type="button" class="rec-btn mr-3" data-action="rec">●</button>
                      <div>
                        <div class="rec-status small text-muted" data-role="rec-status">Aguardando…</div>
                        <div class="rec-timer h5 mb-0" data-role="rec-timer">00:00</div>
                        <div class="mt-2">
                          <audio controls class="d-none" preload="metadata" data-role="audio"></audio>
                          <button type="button" class="btn btn-sm btn-outline-danger d-none" data-action="redo">Descartar áudio</button>
                        </div>
                      </div>
                    </div>
                    <div class="text-danger mt-1 d-none" data-role="erro-audio"></div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endforeach; ?>

            <div class="d-flex align-items-center mt-3">
              <button type="submit" class="btn btn-brand">Enviar avaliação</button>
              <button type="button" id="limpar" class="btn btn-outline-secondary ml-3">Limpar</button>
              <small class="muted ml-auto">Atalho: ← → para mudar rostos</small>
            </div>
          </form>

          <div class="mt-4 d-none" id="resultado">
            <div class="alert alert-success mb-0">
              Obrigado! Recebemos sua avaliação.
            </div>
          </div>

        </div>
      </div>
      <p class="text-center mt-3 mb-0 muted">Anônimo — pronto para integrar ao seu endpoint.</p>
    </div>
  </div>
</div>

<!-- jQuery e Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
(function(){
  /* ====== Configs globais ====== */
  var MAX_FILES = 5, MAX_SIZE_MB = 10;
  var ACCEPTED_PREFIX = ['image/','audio/','video/','application/pdf'];

  /* ====== Estados por pergunta ====== */
  var filesQueues = {};   // {qid: File[]}
  var audioStates = {};   // {qid: {rec, chunks, blob, url, timer, seconds}}

  /* ====== Helpers ====== */
  function hslByScore(v){ var hue = 0 + (v-1)*(130/9); return 'hsl('+hue+', 75%, 60%)'; }
  function fmtTime(s){ var m=Math.floor(s/60), r=s%60; return (m<10?'0':'')+m+':' + (r<10?'0':'')+r; }
  function isAccepted(file){ return ACCEPTED_PREFIX.some(function(p){ return file.type.startsWith(p); }); }
  function sizeMB(file){ return file.size/(1024*1024); }
  function escapeHtml(str){ return String(str).replace(/[&<>"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];}); }

  /* ====== Inicializa rostos por pergunta ====== */
  $('.faces-group').each(function(){
    var $fg = $(this), qid = $fg.data('qid');
    for(var i=1;i<=10;i++){
      var id = qid+'-nota'+i;
      $fg.append(
        '<div class="face-item">'+
          '<input type="radio" name="perguntas['+qid+'][nota]" id="'+id+'" value="'+i+'">'+
          '<label for="'+id+'" title="'+i+' de 10" aria-label="'+i+' de 10" data-val="'+i+'">'+
            (["😡","😠","😦","😕","😐","🙂","😊","😃","😄","🤩"][i-1])+
          '</label>'+
        '</div>'
      );
    }
  });

  /* ====== UI: change nota (delegado por pergunta) ====== */
  $('#formAvaliacao').on('change', 'input[type=radio]', function(){
    var $q = $(this).closest('.question');
    var nota = parseInt(this.value,10);
    // barra + badge
    var $bar = $q.find('.progress-bar');
    var $badge = $q.find('[data-role=badge]');
    var pct = (nota/10)*100, cor = hslByScore(nota);
    $bar.css({width:pct+'%', background:cor}).attr('aria-valuenow', pct);
    $badge.text(nota+'/10');
    $q.find('.face-item label').css('background','#fff');
    $q.find('label[for="'+this.id+'"]').css('background', cor.replace('60%','90%'));
    $q.find('[data-role=erro-nota]').addClass('d-none');
  });

  /* ====== Setas em labels (acessibilidade por pergunta) ====== */
  $('#formAvaliacao').on('keydown','.face-item label',function(e){
    var $q = $(this).closest('.question');
    var qid = $q.data('qid');
    var code = e.which || e.keyCode;
    var $input = $('#'+$(this).attr('for'));
    if(code===13 || code===32){ e.preventDefault(); $input.prop('checked',true).trigger('change'); }
    if(code===37 || code===39){
      e.preventDefault();
      var $sel = $q.find('input[type=radio]:checked');
      var val = parseInt($sel.val()||0,10);
      val = code===39 ? Math.min(10, val+1 || 1) : Math.max(1, (val||2)-1);
      $('#'+qid+'-nota'+val).prop('checked',true).trigger('change').next('label').focus();
    }
  });

  /* ====== Collapse seta/label ====== */
  $('#formAvaliacao').on('show.bs.collapse','.collapse', function(){
    $(this).prev().find('.arrow').text('▾');
  }).on('hide.bs.collapse','.collapse', function(){
    $(this).prev().find('.arrow').text('▸');
  });

  /* ====== Uploader (delegado em cada pergunta) ====== */
  $('#formAvaliacao').on('click','[data-action=select-files]', function(){
    $(this).closest('.uploader').find('[data-role=file-input]').trigger('click');
  });

  $('#formAvaliacao').on('change','[data-role=file-input]', function(e){
    var $u = $(this).closest('.uploader'), qid = $u.data('qid');
    addFiles(qid, Array.from(e.target.files), $u);
    $(this).val('');
  });

  // Drag & drop
  ['dragenter','dragover','dragleave','drop'].forEach(function(evt){
    $('#formAvaliacao').on(evt,'[data-role=uploader]', function(e){ e.preventDefault(); e.stopPropagation(); });
  });
  ['dragenter','dragover'].forEach(function(evt){
    $('#formAvaliacao').on(evt,'[data-role=uploader]', function(){ $(this).addClass('dragover'); });
  });
  ['dragleave','drop'].forEach(function(evt){
    $('#formAvaliacao').on(evt,'[data-role=uploader]', function(){ $(this).removeClass('dragover'); });
  });
  $('#formAvaliacao').on('drop','[data-role=uploader]', function(e){
    var dt = e.originalEvent.dataTransfer;
    if(dt && dt.files){ addFiles($(this).data('qid'), Array.from(dt.files), $(this)); }
  });

  function addFiles(qid, list, $uploader){
    if(!filesQueues[qid]) filesQueues[qid] = [];
    var $q = $uploader.closest('.question');
    var $erro = $q.find('[data-role=erro-arquivos]').addClass('d-none').text('');
    list.forEach(function(file){
      if(filesQueues[qid].length >= MAX_FILES){ $erro.text('Limite de '+MAX_FILES+' arquivos atingido.').removeClass('d-none'); return; }
      if(sizeMB(file) > MAX_SIZE_MB){ $erro.text('Arquivo "'+file.name+'" excede '+MAX_SIZE_MB+' MB.').removeClass('d-none'); return; }
      if(!isAccepted(file)){ $erro.text('Tipo não permitido: '+file.name).removeClass('d-none'); return; }
      filesQueues[qid].push(file);
    });
    renderFileList(qid, $q);
  }

  function renderFileList(qid, $q){
    var $list = $q.find('[data-role=file-list]').empty();
    (filesQueues[qid]||[]).forEach(function(file, idx){
      var nice = sizeMB(file)>=1 ? sizeMB(file).toFixed(1)+' MB' : ((file.size/1024|0)+' KB');
      var $item = $('<div class="file-item">\
        <div class="file-name"><strong>'+escapeHtml(file.name)+'</strong><div class="small text-muted">'+(file.type||'tipo desconhecido')+' • '+nice+'</div></div>\
        <div class="file-actions">\
          <button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-file" data-qid="'+qid+'" data-idx="'+idx+'">Remover</button>\
        </div>\
      </div>');
      $list.append($item);
    });
  }

  $('#formAvaliacao').on('click','[data-action=remove-file]', function(){
    var qid = $(this).data('qid'), idx = parseInt($(this).data('idx'),10);
    if(filesQueues[qid]){ filesQueues[qid].splice(idx,1); }
    renderFileList(qid, $(this).closest('.question'));
  });

  /* ====== Gravador de áudio por pergunta ====== */
  $('#formAvaliacao').on('click','[data-action=rec]', async function(){
    var $rec = $(this).closest('[data-role=recorder]'), qid = $rec.data('qid');
    var st = audioStates[qid] || (audioStates[qid] = {rec:null, chunks:[], blob:null, url:null, timer:null, seconds:0});
    var $btn = $(this), $status = $rec.find('[data-role=rec-status]'), $timer = $rec.find('[data-role=rec-timer]');
    var $audio = $rec.find('[data-role=audio]'), $redo = $rec.find('[data-action=redo]'), $erro = $rec.closest('.question').find('[data-role=erro-audio]').addClass('d-none').text('');

    if(st.rec && st.rec.state === 'recording'){
      // parar
      st.rec.stop(); st.rec.stream.getTracks().forEach(function(t){t.stop();});
      $btn.removeClass('recording');
      clearInterval(st.timer);
    }else{
      // iniciar
      if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){ $erro.text('Navegador sem suporte a áudio.').removeClass('d-none'); return; }
      try{
        const stream = await navigator.mediaDevices.getUserMedia({ audio:true });
        st.rec = new MediaRecorder(stream);
        st.chunks = [];
        st.rec.ondataavailable = e => { if(e.data.size>0) st.chunks.push(e.data); };
        st.rec.onstop = function(){
          st.blob = new Blob(st.chunks, {type:'audio/webm'});
          if(st.url) URL.revokeObjectURL(st.url);
          st.url = URL.createObjectURL(st.blob);
          $audio.attr('src', st.url).removeClass('d-none');
          $redo.removeClass('d-none');
          $status.text('Áudio pronto.');
        };
        st.rec.start();
        st.seconds = 0; $timer.text('00:00'); $status.text('Gravando…');
        $btn.addClass('recording');
        st.timer = setInterval(function(){ st.seconds++; $timer.text(fmtTime(st.seconds)); }, 1000);
      }catch(e){ $erro.text('Não foi possível acessar o microfone.').removeClass('d-none'); }
    }
  });

  $('#formAvaliacao').on('click','[data-action=redo]', function(){
    var $rec = $(this).closest('[data-role=recorder]'), qid = $rec.data('qid');
    var st = audioStates[qid]; if(!st) return;
    if(st.rec && st.rec.state === 'recording'){ st.rec.stop(); st.rec.stream.getTracks().forEach(t=>t.stop()); }
    if(st.url){ URL.revokeObjectURL(st.url); }
    st.blob = null; st.url = null; st.chunks = [];
    clearInterval(st.timer); st.seconds = 0;
    $rec.find('[data-role=audio]').addClass('d-none').removeAttr('src');
    $(this).addClass('d-none');
    $rec.find('[data-role=rec-status]').text('Aguardando…');
    $rec.find('[data-role=rec-timer]').text('00:00');
    $rec.find('[data-action=rec]').removeClass('recording');
  });

  /* ====== Limpar ====== */
  $('#limpar').on('click', function(){
    // notas
    $('#formAvaliacao input[type=radio]').prop('checked',false);
    $('.progress-bar').css('width','0%');
    $('[data-role=badge]').text('–');
    $('.face-item label').css('background','#fff');
    $('[data-role=erro-nota]').addClass('d-none');

    // comentário
    $('textarea').val('');

    // arquivos
    filesQueues = {};
    $('[data-role=file-list]').empty();
    $('[data-role=erro-arquivos]').addClass('d-none').text('');

    // áudio
    Object.keys(audioStates).forEach(function(qid){
      var st = audioStates[qid];
      if(st.rec && st.rec.state==='recording'){ st.rec.stop(); st.rec.stream.getTracks().forEach(t=>t.stop()); }
      if(st.url){ URL.revokeObjectURL(st.url); }
    });
    audioStates = {};
    $('[data-role=audio]').addClass('d-none').removeAttr('src');
    $('[data-action=redo]').addClass('d-none');
    $('[data-role=rec-status]').text('Aguardando…');
    $('[data-role=rec-timer]').text('00:00');
    $('[data-action=rec]').removeClass('recording');

    // fecha todos os extras
    $('.collapse').collapse('hide');

    $('#resultado').addClass('d-none');
  });

  /* ====== Submit ====== */
  $('#formAvaliacao').on('submit', function(e){
    e.preventDefault();
    var valido = true;

    // valida: toda pergunta deve ter nota
    $('.question').each(function(){
      var $q = $(this);
      var hasNota = $q.find('input[type=radio]:checked').length>0;
      $q.find('[data-role=erro-nota]').toggleClass('d-none', hasNota);
      if(!hasNota) valido = false;
    });
    if(!valido){ $('html,body').animate({scrollTop: $('.question').filter(function(){return $(this).find('[data-role=erro-nota]:not(.d-none)').length;}).first().offset().top-80 }, 300); return; }

    // monta FormData
    var fd = new FormData();
    fd.append('anonimo','true');
    fd.append('origem','avaliacao-blocos-rostos');
    fd.append('timestamp', new Date().toISOString());

    // notas + comentários
    $('.question').each(function(){
      var $q = $(this), qid = $q.data('qid');
      var nota = $q.find('input[type=radio]:checked').val();
      var comentario = ($q.find('textarea').val()||'').trim().slice(0,500);
      fd.append('perguntas['+qid+'][nota]', parseInt(nota,10));
      fd.append('perguntas['+qid+'][comentario]', comentario);

      // arquivos
      (filesQueues[qid]||[]).forEach(function(file){
        fd.append('perguntas['+qid+'][anexos][]', file, file.name);
      });

      // áudio
      var st = audioStates[qid];
      if(st && st.blob){
        fd.append('perguntas['+qid+'][audio]', st.blob, 'audio-'+qid+'.webm');
      }
    });

    // AJAX real (descomente e configure sua rota):
    /*
    $.ajax({
      url: '/api/avaliacoes/blocos', // TODO: sua rota
      method: 'POST',
      processData: false,
      contentType: false,
      data: fd
    }).done(function(res){
      $('#resultado').removeClass('d-none');
    }).fail(function(){
      alert('Falha ao enviar sua avaliação. Tente novamente.');
    });
    */

    // Mock de sucesso
    $('#resultado').removeClass('d-none');
  });

  /* ====== Setas globais só para a pergunta em foco ====== */
  $(document).on('keydown', function(e){
    if(/^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName)) return;
    if(e.which!==37 && e.which!==39) return;
    var $focusedQ = $('.question').has(':focus').first();
    if($focusedQ.length===0) return;
    var qid = $focusedQ.data('qid');
    var $sel = $focusedQ.find('input[type=radio]:checked');
    var val = parseInt($sel.val()||0,10);
    if(!val) return;
    e.preventDefault();
    val = e.which===39 ? Math.min(10, val+1) : Math.max(1, val-1);
    $('#'+qid+'-nota'+val).prop('checked',true).trigger('change').next('label').focus();
  });
})();
</script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
