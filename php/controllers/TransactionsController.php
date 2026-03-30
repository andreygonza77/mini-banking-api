<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TransactionsController
{
  public function index(Request $request, Response $response, $args){
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'bank');
    $result = $mysqli_connection->query("SELECT * FROM alunni");
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }
  public function getMovements(Request $request, Response $response, $args){
    $connect = mysqli_connect('my_mariadb', 'root', 'ciccio', 'bank');
    $id = $args['id'];
    $query = "SELECT id, type, amount, description, created_at 
              FROM transactions 
              WHERE account_id = $id 
              ORDER BY created_at DESC;";
    $result = mysqli_query($connect, $query);
    $movement = mysqli_fetch_assoc($result);
    $response->getBody()->write(json_encode($movement));
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function getMovementDetail(Request $request, Response $response, $args){
    $connect = mysqli_connect('my_mariadb', 'root', 'ciccio', 'bank');
    $idT = $args["idT"];
    $idA = $args["idA"];
    $query = "SELECT * 
    FROM transactions 
    WHERE id = $idT;";
    $result = mysqli_query($connect, $query);
    $movement = mysqli_fetch_assoc($result);
    if(!isset($movement)){
      return $response->withStatus(404);
    $response->getBody()->write(json_encode($movement));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
   }
  }
  /*
  public function pushDeposit(Request $request, Response $response, $args){

  }

  public function pushWithDrawal(Request $request, Response $response, $args){

  }

  public function setMovement(Request $request, Response $response, $args){

  }
  
  public function deleteMovement(Request $request, Response $response, $args){

  }
  */
}
