<?php
// gerar.php
// ------------------------------
// 1) tratamento do upload da foto (segurança básica)
// ------------------------------
$uploads_dir = __DIR__ . '/uploads';
if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

$destino = 'uploads/sem-foto.png'; // padrão relativo
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $tmp = $_FILES['foto']['tmp_name'];
    $nomeOrig = basename($_FILES['foto']['name']);
    // gerar nome único simples
    $novoNome = time() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $nomeOrig);
    $caminho = $uploads_dir . '/' . $novoNome;

    // validação simples de tipo e tamanho (até 4MB)
    $tiposPermitidos = ['image/jpeg','image/png','image/jpg','image/webp'];
    if ($_FILES['foto']['size'] <= 4 * 1024 * 1024 && in_array($_FILES['foto']['type'], $tiposPermitidos)) {
        move_uploaded_file($tmp, $caminho);
        $destino = 'uploads/' . $novoNome; // caminho relativo usado no HTML
    } else {
        // keep default sem-foto.png se inválida
        $erro_upload = "Arquivo de imagem inválido (use JPG/PNG até 4MB).";
    }
} else {
    // sem upload — mantém sem-foto.png (se você quiser coloque uma imagem default em uploads/sem-foto.png)
    $destino = 'uploads/sem-foto.png';
}

// ------------------------------
// 2) pegar dados e calcular idade
// ------------------------------
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$nome = $_POST['nome'] ?? '';
$data_nasc = $_POST['data_nascimento'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$endereco = $_POST['endereco'] ?? '';
$habilidades = $_POST['habilidades'] ?? '';
$observacoes = $_POST['observacoes'] ?? '';

$formacoes = $_POST['formacao'] ?? [];
$experiencias = $_POST['experiencia'] ?? [];
$referencias = $_POST['referencia'] ?? [];

$idade = '';
if ($data_nasc) {
    try {
        $d = new DateTime($data_nasc);
        $hoje = new DateTime();
        $idade = $hoje->diff($d)->y;
    } catch (Exception $e) {
        $idade = '';
    }
}

// título do arquivo PDF desejado
$pdfNome = preg_replace('/[^A-Za-z0-9\-\_]/', '_', substr($nome,0,40)) ?: 'curriculo';
$pdfNome .= '_' . date('Ymd_His') . '.pdf';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Currículo - <?= h($nome) ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Bootstrap (apenas para estilizar a página) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body { background:#fff; color:#111; }
    .cv { max-width:800px; margin:20px auto; padding:20px; border:1px solid #eaeaea; }
    .foto { max-width:180px; max-height:180px; object-fit:cover; border-radius:8px; }
    .campo-title { font-weight:700; color:#333; margin-top:10px; }
    .small-muted { color:#666; font-size:0.9rem; }
    /* garantir boa aparência no PDF */
    @media print {
      body { margin:0; }
      .no-print { display:none; }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="cv" id="curriculo-for-pdf">
    <div class="d-flex align-items-center">
      <div style="flex:1">
        <h2 style="margin-bottom:0"><?= h($nome) ?></h2>
        <p class="small-muted" style="margin-bottom:0"><?= h($endereco) ?></p>
        <p class="small-muted mb-1"><?= h($email) ?> — <?= h($telefone) ?></p>
        <p class="small-muted mb-0"><strong>Idade:</strong> <?= h($idade) ?></p>
      </div>
      <div class="ms-3 text-end">
        <img src="<?= h($destino) ?>" alt="Foto" class="foto">
      </div>
    </div>

    <hr>

    <?php if (!empty($experiencias)): ?>
      <div>
        <div class="campo-title">Experiências</div>
        <ul>
          <?php foreach ($experiencias as $exp): if(trim($exp)!==''): ?>
            <li><?= h($exp) ?></li>
          <?php endif; endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (!empty($formacoes)): ?>
      <div>
        <div class="campo-title">Formação</div>
        <ul>
          <?php foreach ($formacoes as $f): if(trim($f)!==''): ?>
            <li><?= h($f) ?></li>
          <?php endif; endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (!empty($habilidades)): ?>
      <div>
        <div class="campo-title">Habilidades</div>
        <p><?= h($habilidades) ?></p>
      </div>
    <?php endif; ?>

    <?php if (!empty($referencias)): ?>
      <div>
        <div class="campo-title">Referências</div>
        <ul>
          <?php foreach ($referencias as $r): if(trim($r)!==''): ?>
            <li><?= h($r) ?></li>
          <?php endif; endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (!empty($observacoes)): ?>
      <div>
        <div class="campo-title">Observações</div>
        <p><?= nl2br(h($observacoes)) ?></p>
      </div>
    <?php endif; ?>

    <?php if (!empty($erro_upload)): ?>
      <div class="alert alert-warning mt-3"><?= h($erro_upload) ?></div>
    <?php endif; ?>
  </div>

  <div class="text-center mt-3 no-print">
    <button id="baixarPDF" class="btn btn-primary">Baixar PDF</button>
    <a href="index.php" class="btn btn-secondary">Voltar</a>
  </div>
</div>

<!-- html2pdf (usa html2canvas + jsPDF) via CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
  // nome do arquivo PDF gerado (pega do PHP)
  const nomePdf = <?= json_encode($pdfNome) ?>;

  function gerarPdf() {
    const element = document.getElementById('curriculo-for-pdf');

    // opções básicas: margem pequena, formato A4, alta qualidade
    const opt = {
      margin:       0.4,
      filename:     nomePdf,
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2 },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    // gera e baixa o pdf
    html2pdf().set(opt).from(element).save();
  }

  // botão manual
  document.getElementById('baixarPDF').addEventListener('click', function(){
    this.disabled = true;
    this.innerText = 'Gerando...';
    gerarPdf();
    setTimeout(()=>{ this.disabled = false; this.innerText = 'Baixar PDF'; }, 3000);
  });

  // tentar iniciar o download automaticamente ao carregar a página
  window.addEventListener('load', function(){
    // pequena pausa para garantir que imagens carreguem
    setTimeout(function(){
      gerarPdf();
    }, 1200);
  });
</script>

</body>
</html>
