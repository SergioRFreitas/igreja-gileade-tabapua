<?php
session_start();

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';
$pdo = conectar_banco();

// Buscar todos os membros
$stmt = $pdo->query("SELECT 
    nome,
    email,
    telefone,
    data_nascimento,
    sexo,
    naturalidade,
    endereco,
    estado_civil,
    nome_conjuge,
    telefone_conjuge,
    batismo_aguas,
    ministrio,
    funcao,
    data_filiacao,
    situacao,
    data_saida,
    observacao,
    data_cadastro
    FROM membros ORDER BY nome");

$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Definir nome do arquivo
$nomeArquivo = 'membros_igrejagileade_' . date('d-m-Y') . '.csv';

// Cabeçalhos para download
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM para Excel reconhecer UTF-8 com acentos
echo "\xEF\xBB\xBF";

// Abrir saída
$output = fopen('php://output', 'w');

// Cabeçalho da planilha
fputcsv($output, [
    'Nome',
    'E-mail',
    'Telefone',
    'Data de Nascimento',
    'Sexo',
    'Naturalidade',
    'Endereço',
    'Estado Civil',
    'Nome do Cônjuge',
    'Telefone do Cônjuge',
    'Batismo nas Águas',
    'Ministério',
    'Função',
    'Data de Filiação',
    'Situação',
    'Data de Saída',
    'Observação',
    'Data de Cadastro'
], ';');

// Dados dos membros
foreach ($membros as $m) {
    fputcsv($output, [
        $m['nome'],
        $m['email'],
        $m['telefone'],
        $m['data_nascimento'] ? date('d/m/Y', strtotime($m['data_nascimento'])) : '',
        $m['sexo'],
        $m['naturalidade'],
        $m['endereco'],
        $m['estado_civil'],
        $m['nome_conjuge'],
        $m['telefone_conjuge'],
        $m['batismo_aguas'] ? date('d/m/Y', strtotime($m['batismo_aguas'])) : '',
        $m['ministrio'],
        $m['funcao'],
        $m['data_filiacao'] ? date('d/m/Y', strtotime($m['data_filiacao'])) : '',
        $m['situacao'],
        $m['data_saida'] ? date('d/m/Y', strtotime($m['data_saida'])) : '',
        $m['observacao'],
        $m['data_cadastro'] ? date('d/m/Y', strtotime($m['data_cadastro'])) : '',
    ], ';');
}

fclose($output);
exit();
?>
