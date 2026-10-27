# API de Missões Espaciais

API REST em PHP com Slim Framework 4, criada como exemplo para cadastrar, consultar, atualizar e excluir missões espaciais.

## Requisitos
- PHP 8 ou superior (com extensão `mbstring` habilitada)
- Composer

## Instalação
Na pasta do projeto, execute:

```bash
composer install
```

Se necessário, instale as dependências com:

```bash
composer require slim/slim:^4 slim/psr7
```

## Executar no Windows / VS Code
Na pasta `api_espacial`, execute:

```bash
php -S localhost:8000 -t public
```

Mantenha o terminal aberto. Se a porta 8000 estiver ocupada, use `8001` e ajuste a URL no Postman.

## Endpoints
- `GET /status` — verifica se a API está ativa.
- `GET /missoes` — lista as missões.
- `GET /missoes/{id}` — consulta uma missão.
- `POST /missoes` — cadastra uma missão.
- `PUT /missoes/{id}` — atualiza uma missão.
- `DELETE /missoes/{id}` — exclui uma missão.

Os campos obrigatórios para cadastro/atualização são `nome`, `destino`, `data_lancamento` (formato `AAAA-MM-DD`) e `status`. `descricao` é opcional.

## Testar no Postman
Importe `postman/API_Missoes_Espaciais.postman_collection.json` pelo botão **Import**. Na coleção, clique em **Run** para executar os testes automatizados. A coleção usa `http://localhost:8000` como URL base.

## Decisões técnicas e limitações
- As respostas são JSON e usam códigos HTTP apropriados (200, 201, 400, 404 e 409).
- Há cinco missões de exemplo cadastradas inicialmente.
- Os dados ficam em memória; são reiniciados quando o servidor é reiniciado. Não há banco de dados neste exemplo.
- Este código é uma implementação de exemplo porque o arquivo `public/index.php` recebido no projeto original estava vazio. Ajuste os endpoints/campos se o professor tiver fornecido requisitos diferentes.
