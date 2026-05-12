<?php
use Slim\Factory\AppFactory;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/controllers/TransactionsController.php';

$mysqli = new mysqli("my_mariadb", "root", "ciccio", "bank");
$app = AppFactory::create();

$app->get('/accounts/{id}/balance', "TransctionsController:getBalance");
$app->get('/accounts/{id}/transactions', "TransactionsController:getMovements");
$app->get('/accounts/{idA}/transactions/{idT}', "TransactionsController:getMovementDetail");
$app->post('/accounts/{id}/deposits', "TransactionsController:pushDeposit");
$app->post('/accounts/{id}/withdrawals', "TransactionsController:pushWithDrawal");
$app->put('/accounts/{idA}/transactions/{idT}', "TransactionsController:setMovement");
$app->delete('/accounts/{idA}/transactions/{idT}', "TransactionsController:deleteMovement");
$app->get('/accounts/{id}/balance/convert/fiat', "BalanceController:convertToFiat");
$app->get('/accounts/{id}/balance/convert/crypto', "BalanceController:convertToCrypto");

$app->run();

