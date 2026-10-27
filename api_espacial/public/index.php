<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Dados iniciais em memória: serão reiniciados quando o servidor for reiniciado.
$missoes = [
    1 => ['id' => 1, 'nome' => 'Artemis II', 'destino' => 'Lua', 'data_lancamento' => '2026-11-01', 'status' => 'planejada', 'descricao' => 'Missão tripulada de teste ao redor da Lua.'],
    2 => ['id' => 2, 'nome' => 'Mars Explorer', 'destino' => 'Marte', 'data_lancamento' => '2027-05-15', 'status' => 'em preparação', 'descricao' => 'Missão de exploração de Marte.'],
    3 => ['id' => 3, 'nome' => 'Europa Clipper', 'destino' => 'Europa', 'data_lancamento' => '2027-09-10', 'status' => 'em andamento', 'descricao' => 'Estudo da lua Europa, de Júpiter.'],
    4 => ['id' => 4, 'nome' => 'Solar Probe', 'destino' => 'Sol', 'data_lancamento' => '2025-01-20', 'status' => 'concluída', 'descricao' => 'Coleta de dados sobre a coroa solar.'],
    5 => ['id' => 5, 'nome' => 'Titan Surveyor', 'destino' => 'Titã', 'data_lancamento' => '2028-03-18', 'status' => 'planejada', 'descricao' => 'Estudo da lua Titã, de Saturno.'],
];
$proximoId = 6;

function jsonResponse(Response $response, array $data, int $status = 200): Response
{
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus($status);
}

function validarMissao(array $dados, bool $completa = true): ?string
{
    $campos = ['nome', 'destino', 'data_lancamento', 'status'];
    foreach ($campos as $campo) {
        if ($completa && (!isset($dados[$campo]) || !is_string($dados[$campo]) || trim($dados[$campo]) === '')) {
            return "O campo '$campo' é obrigatório e deve ser um texto não vazio.";
        }
        if (isset($dados[$campo]) && (!is_string($dados[$campo]) || trim($dados[$campo]) === '')) {
            return "O campo '$campo' deve ser um texto não vazio.";
        }
    }
    if (isset($dados['data_lancamento'])) {
        $data = DateTime::createFromFormat('Y-m-d', $dados['data_lancamento']);
        if (!$data || $data->format('Y-m-d') !== $dados['data_lancamento']) {
            return "O campo 'data_lancamento' deve usar o formato AAAA-MM-DD.";
        }
    }
    return null;
}

$app->get('/status', function (Request $request, Response $response) {
    return jsonResponse($response, ['status' => 'online', 'mensagem' => 'API de missões espaciais funcionando!']);
});

$app->get('/missoes', function (Request $request, Response $response) use (&$missoes) {
    return jsonResponse($response, array_values($missoes));
});

$app->get('/missoes/{id}', function (Request $request, Response $response, array $args) use (&$missoes) {
    $id = filter_var($args['id'], FILTER_VALIDATE_INT);
    if (!$id || !isset($missoes[$id])) {
        return jsonResponse($response, ['erro' => 'Missão não encontrada.'], 404);
    }
    return jsonResponse($response, $missoes[$id]);
});

$app->post('/missoes', function (Request $request, Response $response) use (&$missoes, &$proximoId) {
    $dados = (array)($request->getParsedBody() ?? []);
    $erro = validarMissao($dados, true);
    if ($erro !== null) return jsonResponse($response, ['erro' => $erro], 400);

    foreach ($missoes as $missao) {
        if (strtolower($missao['nome']) === strtolower(trim($dados['nome']))) {
            return jsonResponse($response, ['erro' => 'Já existe uma missão com esse nome.'], 409);
        }
    }
    $nova = [
        'id' => $proximoId++,
        'nome' => trim($dados['nome']),
        'destino' => trim($dados['destino']),
        'data_lancamento' => $dados['data_lancamento'],
        'status' => trim($dados['status']),
        'descricao' => isset($dados['descricao']) && is_string($dados['descricao']) ? trim($dados['descricao']) : ''
    ];
    $missoes[$nova['id']] = $nova;
    return jsonResponse($response, $nova, 201);
});

$app->put('/missoes/{id}', function (Request $request, Response $response, array $args) use (&$missoes) {
    $id = filter_var($args['id'], FILTER_VALIDATE_INT);
    if (!$id || !isset($missoes[$id])) return jsonResponse($response, ['erro' => 'Missão não encontrada.'], 404);

    $dados = (array)($request->getParsedBody() ?? []);
    $erro = validarMissao($dados, true);
    if ($erro !== null) return jsonResponse($response, ['erro' => $erro], 400);
    foreach ($missoes as $outroId => $missao) {
        if ($outroId !== $id && strtolower($missao['nome']) === strtolower(trim($dados['nome']))) {
            return jsonResponse($response, ['erro' => 'Já existe uma missão com esse nome.'], 409);
        }
    }
    $missoes[$id] = [
        'id' => $id,
        'nome' => trim($dados['nome']),
        'destino' => trim($dados['destino']),
        'data_lancamento' => $dados['data_lancamento'],
        'status' => trim($dados['status']),
        'descricao' => isset($dados['descricao']) && is_string($dados['descricao']) ? trim($dados['descricao']) : ''
    ];
    return jsonResponse($response, $missoes[$id]);
});

$app->delete('/missoes/{id}', function (Request $request, Response $response, array $args) use (&$missoes) {
    $id = filter_var($args['id'], FILTER_VALIDATE_INT);
    if (!$id || !isset($missoes[$id])) return jsonResponse($response, ['erro' => 'Missão não encontrada.'], 404);
    unset($missoes[$id]);
    return jsonResponse($response, ['mensagem' => 'Missão excluída com sucesso.']);
});

$app->run();
