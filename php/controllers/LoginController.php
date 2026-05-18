<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoginController
{
    function getUsers(Request $request, Response $response, array $args){
        $db = new mysqli("my_mariadb", "root", "ciccio", "bank");
        $query = "SELECT id, created_at
        FROM accounts;";
    $result = mysqli_query($db, $query);
    $accounts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $response->getBody()->write(json_encode($accounts));
    return $response->withHeader('Content-Type', 'application/json');
    }
}
