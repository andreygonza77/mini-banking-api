<?php
use Slim\Factory\AppFactory;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/controllers/TransactionsController.php';

$mysqli = new mysqli("my_mariadb", "root", "ciccio", "bank");
$app = AppFactory::create();

$app->get('/test', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Test page");
    return $response;
});

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$app->get('/accounts/{id}/transactions', "TransactionsController:getMovements");
$app->get('/accounts/{idA}/transactions/{idT}', "TransactionsController:getMovementDetail");
$app->post('/accounts/{id}/deposits', "TransactionsController:pushDeposit");
$app->post('/accounts/{id}/withdrawals', "TransactionsController:pushWithDrawal");
$app->put('/accounts/{idA}/transactions/{idT}', "TransactionsController:setMovement");
$app->delete('/accounts/{idA}/transactions/{idT}', "TransactionsController:deleteMovement");
$app->get('/accounts/{id}/balance/convert/fiat', function (Request $request, Response $response, array $args) use ($mysqli) {
    $accountId = (int)$args['id'];
    $params = $request->getQueryParams();
    $to = strtoupper($params['to'] ?? '');

    if (!$to) {
        $response->getBody()->write(json_encode([
            'error' => 'Missing target currency'
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    $stmt = $mysqli->prepare('SELECT id, currency FROM accounts WHERE id = ?');
    $stmt->bind_param('i', $accountId);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result->fetch_assoc();

    if (!$account) {
        $response->getBody()->write(json_encode([
            'error' => 'Account not found'
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $from = strtoupper($account['currency']);

    $stmt = $mysqli->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) AS balance
        FROM transactions
        WHERE account_id = ?
    ");
    $stmt->bind_param('i', $accountId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $balance = (float)($row['balance'] ?? 0);

    $url = "https://api.frankfurter.dev/v1/latest?base={$from}&symbols={$to}";
    $json = @file_get_contents($url);

    if ($json === false) {
        $response->getBody()->write(json_encode([
            'error' => 'External exchange API unavailable'
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(502);
    }

    $data = json_decode($json, true);

    if (!isset($data['rates'][$to])) {
        $response->getBody()->write(json_encode([
            'error' => 'Target currency not supported'
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    $rate = (float)$data['rates'][$to];
    $converted = round($balance * $rate, 2);

    $response->getBody()->write(json_encode([
        'account_id' => $accountId,
        'provider' => 'Frankfurter',
        'conversion_type' => 'fiat',
        'from_currency' => $from,
        'to_currency' => $to,
        'original_balance' => $balance,
        'converted_balance' => $converted,
        'rate' => $rate,
        'date' => $data['date'] ?? null
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});
$app->get('/accounts/{id}/balance/convert/crypto', function (Request $request, Response $response, array $args) use ($mysqli) {
    $accountId = (int)$args['id'];
    $params = $request->getQueryParams();
    $toCrypto = strtoupper($params['to'] ?? '');
    if (!$toCrypto) {
        $response->getBody()->write(json_encode(['error' => 'Missing target crypto (e.g. BTC, ETH)']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
    $stmt = $mysqli->prepare('SELECT currency FROM accounts WHERE id = ?');
    $stmt->bind_param('i', $accountId);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc();
    if (!$account) {
        $response->getBody()->write(json_encode(['error' => 'Account not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $fromCurrency = strtoupper($account['currency']); // es. EUR
    $stmt = $mysqli->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) - 
            COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) AS balance 
        FROM transactions WHERE account_id = ?
    ");
    $stmt->bind_param('i', $accountId);
    $stmt->execute();
    $balance = (float)($stmt->get_result()->fetch_assoc()['balance'] ?? 0);
    $marketSymbol = $toCrypto . $fromCurrency;
    $url = "https://api.binance.com/api/v3/ticker/price?symbol={$marketSymbol}";
    $json = @file_get_contents($url);
    if ($json === false) {
        $response->getBody()->write(json_encode([
            'error' => "Market pair {$marketSymbol} not found or Binance API unavailable"
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $data = json_decode($json, true);
    $price = (float)$data['price'];
    $convertedAmount = round($balance / $price, 8);
    $response->getBody()->write(json_encode([
        'account_id' => $accountId,
        'provider' => 'Binance',
        'conversion_type' => 'crypto',
        'from_currency' => $fromCurrency,
        'to_crypto' => $toCrypto,
        'market_symbol' => $marketSymbol,
        'original_balance' => $balance,
        'price' => $price,
        'converted_amount' => $convertedAmount
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();

