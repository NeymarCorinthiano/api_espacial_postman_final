
<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class MissoesTest extends TestCase
{
    public function testPostCriaMissao(): void
    {
        // Carrega a aplicação sem iniciar o servidor.
        $app = require __DIR__ . '/../src/app.php';

        $dados = [
            'nome' => 'Missão Marte',
            'destino' => 'Marte',
            'status' => 'Planejada'
        ];

        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/missoes')
            ->withHeader('Content-Type', 'application/json')
            ->withParsedBody($dados);

        $response = $app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());

        $corpo = json_decode(
            (string) $response->getBody(),
            true
        );

        $this->assertEquals('Missão Marte', $corpo['nome']);
        $this->assertEquals('Marte', $corpo['destino']);
        $this->assertEquals('Planejada', $corpo['status']);
    }
}
