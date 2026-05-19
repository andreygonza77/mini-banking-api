<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoginController
{
    private function getConnection(){
        return mysqli_connect('my_mariadb', 'root', 'ciccio', 'bank');
    }
    public function getAccount(Request $request, Response $response, $args) {
    $db = $this->getConnection();
    $id = (int)$args['id'];

    $query = "SELECT id, created_at FROM transactions WHERE account_id = $id LIMIT 1;"; 
    
    $result = mysqli_query($db, $query);
    $account = mysqli_fetch_assoc($result);

    if (!$account) {
        $response->getBody()->write(json_encode(["error" => "Account not found"]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    $account['id'] = $id;

    $response->getBody()->write(json_encode($account));
    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
}
}
