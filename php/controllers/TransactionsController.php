<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TransactionsController
{
  private function getConnection(){
    return mysqli_connect('my_mariadb', 'root', 'ciccio', 'bank');
  }

  public function getMovements(Request $request, Response $response, $args){
    $db = $this->getConnection();
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
    $db = $this->getConnection();
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
 
  public function pushDeposit(Request $request, Response $response, $args){

  }
 /*
  public function pushWithDrawal(Request $request, Response $response, $args){

  }

  public function setMovement(Request $request, Response $response, $args){

  }
  */
  public function deleteMovement(Request $request, Response $response, $args){
    $db = $this->getConnection();
    $idT = $args['idT'];
    $idA = $args['idA'];

    // Controllo se è l'ultimo ID per quel conto
    $checkQuery = "SELECT id FROM transactions WHERE account_id = $idA ORDER BY created_at DESC LIMIT 1";
    $lastId = mysqli_fetch_assoc(mysqli_query($db, $checkQuery))['id'];

    if ($idT != $lastId) {
        $response->getBody()->write(json_encode(["error" => "Puoi eliminare solo l'ultimo movimento per coerenza"]));
        return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
    }
    $query = "DELETE FROM transactions WHERE id = $idT";
    mysqli_query($db, $query);
    $response->getBody()->write(json_encode(["message" => "Movimento eliminato"]));
    return $response->withHeader('Content-Type', 'application/json');
  }
  
}
