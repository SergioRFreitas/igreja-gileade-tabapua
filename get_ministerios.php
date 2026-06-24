<?php
// get_ministerios.php - Retorna lista de ministérios em JSON
header('Content-Type: application/json');

$ministerios = [
    ['nome' => 'Ação Social'],
    ['nome' => 'Casais'],
    ['nome' => 'Eventos'],
    ['nome' => 'Homens'],
    ['nome' => 'Infantil'],
    ['nome' => 'Jovens e Adolescentes'],
    ['nome' => 'Louvor'],
    ['nome' => 'Mídia'],
    ['nome' => 'Mulheres'],
    ['nome' => 'Oração e I  ntercessão'],
    ['nome' => 'Pastoral'],
    ['nome' => 'Tesouraria'],
    ['nome' => 'Evangelismo'],
    ['nome' => 'Patrimonial'],
];

echo json_encode($ministerios);
