<?php
// index.php
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Gerador de Currículo - Formulário</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    body { background:#f8f9fa; }
    .card { max-width:900px; margin:auto; }
    .campo-experiencia, .campo-formacao { margin-bottom:10px; }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="card shadow p-4">
      <h3 class="text-center mb-4">Gerador de Currículo (APO)</h3>

      <form action="gerar.php" method="POST" enctype="multipart/form-data" id="form-curriculo">
        <div class="row">
          <div class="col-md-8">
            <div class="mb-3">
              <label class="form-label">Nome Completo</label>
              <input type="text" name="nome" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Data de Nascimento</label>
              <input type="date" name="data_nascimento" id="data_nascimento" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Idade (calculada)</label>
              <input type="text" id="idade" class="form-control" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">E-mail</label>
              <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Telefone</label>
              <input type="text" name="telefone" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Endereço</label>
              <input type="text" name="endereco" class="form-control">
            </div>

            <!-- Formação -->
            <div class="mb-3">
              <label class="form-label">Formações</label>
              <div id="formacoes-list">
                <div class="campo-formacao input-group mb-2">
                  <input type="text" name="formacao[]" class="form-control" placeholder="Ex: Bacharel em Sistemas - Universidade X">
                  <button type="button" class="btn btn-danger btn-remover-formacao">Remover</button>
                </div>
              </div>
              <button type="button" id="add-formacao" class="btn btn-sm btn-outline-primary mt-2">+ Adicionar Formação</button>
            </div>

            <!-- Experiências -->
            <div class="mb-3">
              <label class="form-label">Experiências Profissionais</label>
              <div id="experiencias-list">
                <div class="campo-experiencia input-group mb-2">
                  <input type="text" name="experiencia[]" class="form-control" placeholder="Ex: Estagiária - Empresa Y (2022-2023)">
                  <button type="button" class="btn btn-danger btn-remover-experiencia">Remover</button>
                </div>
              </div>
              <button type="button" id="add-experiencia" class="btn btn-sm btn-outline-primary mt-2">+ Adicionar Experiência</button>
            </div>

            <!-- Habilidades -->
            <div class="mb-3">
              <label class="form-label">Habilidades (separe por vírgula)</label>
              <input type="text" name="habilidades" class="form-control" placeholder="Ex: HTML, CSS, JavaScript">
            </div>

            <!-- Referências -->
            <div class="mb-3">
              <label class="form-label">Referências Pessoais</label>
              <div id="referencias-list">
                <div class="input-group mb-2">
                  <input type="text" name="referencia[]" class="form-control" placeholder="Nome - Contato">
                  <button type="button" class="btn btn-danger btn-remover-referencia">Remover</button>
                </div>
              </div>
              <button type="button" id="add-referencia" class="btn btn-sm btn-outline-primary mt-2">+ Adicionar Referência</button>
            </div>

          </div>

          <div class="col-md-4">
            <div class="mb-3 text-center">
              <label class="form-label d-block">Foto (upload)</label>
              <input type="file" name="foto" accept="image/*" class="form-control mb-2" required>
              <small class="text-muted">JPEG/PNG — até 2MB recomendado</small>
              <div class="mt-3">
                <img id="preview" src="" alt="Preview" class="img-fluid rounded" style="max-height:220px; display:none;">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Observações</label>
              <textarea name="observacoes" class="form-control" rows="4"></textarea>
            </div>

          </div>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-success btn-lg">Gerar e Baixar PDF</button>
        </div>
      </form>

      <p class="text-center mt-3 text-muted small">Projeto para a APO — Enzo</p>
    </div>
  </div>

  <script>
    // calcula idade quando muda a data
    function calcularIdade(dataStr) {
      if (!dataStr) return '';
      const hoje = new Date();
      const nasc = new Date(dataStr);
      let idade = hoje.getFullYear() - nasc.getFullYear();
      const m = hoje.getMonth() - nasc.getMonth();
      if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;
      return idade;
    }

    $('#data_nascimento').on('change', function(){
      $('#idade').val(calcularIdade($(this).val()));
    });

    // preview da imagem antes do envio
    $('input[name="foto"]').on('change', function(e){
      const file = e.target.files[0];
      if (!file) { $('#preview').hide(); return; }
      const reader = new FileReader();
      reader.onload = function(ev) {
        $('#preview').attr('src', ev.target.result).show();
      };
      reader.readAsDataURL(file);
    });

    // adicionar formações
    $('#add-formacao').on('click', function(){
      $('#formacoes-list').append(
        '<div class="campo-formacao input-group mb-2">' +
          '<input type="text" name="formacao[]" class="form-control" placeholder="Formação">' +
          '<button type="button" class="btn btn-danger btn-remover-formacao">Remover</button>' +
        '</div>'
      );
    });
    $(document).on('click', '.btn-remover-formacao', function(){ $(this).parent().remove(); });

    // adicionar experiencias
    $('#add-experiencia').on('click', function(){
      $('#experiencias-list').append(
        '<div class="campo-experiencia input-group mb-2">' +
          '<input type="text" name="experiencia[]" class="form-control" placeholder="Experiência">' +
          '<button type="button" class="btn btn-danger btn-remover-experiencia">Remover</button>' +
        '</div>'
      );
    });
    $(document).on('click', '.btn-remover-experiencia', function(){ $(this).parent().remove(); });

    // adicionar referencias
    $('#add-referencia').on('click', function(){
      $('#referencias-list').append(
        '<div class="input-group mb-2">' +
          '<input type="text" name="referencia[]" class="form-control" placeholder="Referência">' +
          '<button type="button" class="btn btn-danger btn-remover-referencia">Remover</button>' +
        '</div>'
      );
    });
    $(document).on('click', '.btn-remover-referencia', function(){ $(this).parent().remove(); });

    // ao submeter, deixa o botão mostrar "gerando..."
    $('#form-curriculo').on('submit', function(){
      $('button[type="submit"]').prop('disabled', true).text('Gerando PDF...');
    });
  </script>

</body>
</html>
