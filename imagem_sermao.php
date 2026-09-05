<?php
// imagem_sermao.php - Gera uma imagem (cartão) do sermão para compartilhar
// Igreja Gileade Tabapuá
require_once 'config.php';

$pdo = conectar_banco();

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM sermoes WHERE id = ?");
    $stmt->execute([$_GET['id']]);
} else {
    $stmt = $pdo->query("SELECT * FROM sermoes ORDER BY data_sermao DESC, id DESC LIMIT 1");
}
$sermao = $stmt->fetch();

if (!$sermao) {
    http_response_code(404);
    die("Sermão não encontrado.");
}

// Fontes (precisam estar na pasta fontes/ do servidor)
$fonteBold   = __DIR__ . '/fontes/Poppins-Bold.ttf';
$fonteReg    = __DIR__ . '/fontes/Poppins-Regular.ttf';
$fonteItalic = __DIR__ . '/fontes/Poppins-Italic.ttf';

if (!file_exists($fonteBold) || !file_exists($fonteReg) || !file_exists($fonteItalic)) {
    die("Fontes não encontradas. Envie a pasta fontes/ (Poppins-Bold.ttf, Poppins-Regular.ttf, Poppins-Italic.ttf) para o servidor.");
}

$W = 1080;
$H = 1350;

$img = imagecreatetruecolor($W, $H);

// Gradiente de fundo (de cima pra baixo)
$corTopo = [31, 43, 62];
$corBase = [44, 62, 80];
for ($y = 0; $y < $H; $y++) {
    $t = $y / $H;
    $r = (int)($corTopo[0] + ($corBase[0] - $corTopo[0]) * $t);
    $g = (int)($corTopo[1] + ($corBase[1] - $corTopo[1]) * $t);
    $b = (int)($corTopo[2] + ($corBase[2] - $corTopo[2]) * $t);
    $cor = imagecolorallocate($img, $r, $g, $b);
    imageline($img, 0, $y, $W, $y, $cor);
}

$branco     = imagecolorallocate($img, 255, 255, 255);
$cinzaClaro = imagecolorallocate($img, 200, 210, 225);
$cinza      = imagecolorallocate($img, 180, 190, 205);

// Função para quebrar texto em linhas que cabem numa largura máxima
function quebrarTexto($texto, $fonte, $tamanho, $larguraMax) {
    $palavras = explode(' ', $texto);
    $linhas = [];
    $linhaAtual = '';
    foreach ($palavras as $palavra) {
        $teste = trim($linhaAtual . ' ' . $palavra);
        $bbox = imagettfbbox($tamanho, 0, $fonte, $teste);
        $largura = $bbox[2] - $bbox[0];
        if ($largura <= $larguraMax || $linhaAtual === '') {
            $linhaAtual = $teste;
        } else {
            $linhas[] = $linhaAtual;
            $linhaAtual = $palavra;
        }
    }
    if ($linhaAtual !== '') $linhas[] = $linhaAtual;
    return $linhas;
}

// Função para centralizar texto horizontalmente
function textoCentralizado($img, $tamanho, $y, $fonte, $texto, $cor, $larguraTela) {
    $bbox = imagettfbbox($tamanho, 0, $fonte, $texto);
    $largura = $bbox[2] - $bbox[0];
    $x = ($larguraTela - $largura) / 2;
    imagettftext($img, $tamanho, 0, (int)$x, $y, $cor, $fonte, $texto);
}

// Nome da igreja (topo)
textoCentralizado($img, 18, 110, $fonteReg, "IGREJA CRISTÃ GILEADE TABAPUÁ", $cinza, $W);

// Título (quebrado em várias linhas, centralizado, no meio da imagem)
$linhasTitulo = quebrarTexto($sermao['titulo'], $fonteBold, 40, $W - 160);
$totalLinhas = count($linhasTitulo);
$alturaLinha = 78;
$yInicial = ($H / 2) - (($totalLinhas * $alturaLinha) / 2) - 40;

$y = $yInicial;
foreach ($linhasTitulo as $linha) {
    textoCentralizado($img, 40, $y, $fonteBold, $linha, $branco, $W);
    $y += $alturaLinha;
}

// Texto base (logo abaixo do título)
if (!empty($sermao['texto_base'])) {
    $y += 20;
    textoCentralizado($img, 22, $y, $fonteItalic, $sermao['texto_base'], $cinzaClaro, $W);
}

// Preletor (rodapé)
textoCentralizado($img, 20, $H - 140, $fonteReg, $sermao['preletor'], $branco, $W);

// Data (rodapé, menor)
textoCentralizado($img, 16, $H - 100, $fonteReg, formatar_data($sermao['data_sermao']), $cinza, $W);

// Linha decorativa fina no rodapé
$corLinha = imagecolorallocate($img, 80, 120, 160);
imagefilledrectangle($img, ($W/2) - 60, $H - 170, ($W/2) + 60, $H - 167, $corLinha);

// Chamada para leitura completa (o link em si vai na legenda da publicação)
$linkTexto = "Leia a mensagem completa acessando o link.";
textoCentralizado($img, 17, $H - 50, $fonteReg, $linkTexto, $cinzaClaro, $W);

header('Content-Type: image/png');
header('Content-Disposition: inline; filename="sermao_' . $sermao['id'] . '.png"');
imagepng($img);
imagedestroy($img);
