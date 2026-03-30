<?php
use Slim\Factory\AppFactory;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/controllers/TransactionsController.php';

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
$app->run();
