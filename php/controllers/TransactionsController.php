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
    $result = mysqli_query($db, $query);
    $movement = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $response->getBody()->write(json_encode($movements));
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function getMovementDetail(Request $request, Response $response, $args){
    $db = $this->getConnection();
    $idA = $args["idA"];
    $query = "SELECT * 
    FROM transactions 
    WHERE id = $idT;";
    $result = mysqli_query($db, $query);
    $movement = mysqli_fetch_assoc($result);
    if(!$movement){
        $response->getBody()->write(json_encode(["error" => "Not found"]));
        return $response->withStatus(404)->withHeader("Content-type", "application/json");
    }
    $response->getBody()->write(json_encode($movement));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }
  public function pushDeposit(Request $request, Response $response, $args){
    $db = $this->getConnection();
    
    // Se la connessione fallisce, lo scriviamo nel JSON
    if (!$db) {
        $response->getBody()->write(json_encode(["error" => "Connessione fallita: " . mysqli_connect_error()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $idA = $args['id'];
    $data = json_decode($request->getBody(), true);
    $amount = $data['amount'] ?? 0;
    $description = $data['description'] ?? '';

    // Query per il saldo
    $qSaldo = "SELECT (SELECT IFNULL(SUM(amount),0) FROM transactions WHERE account_id = $idA AND type = 'deposit') - 
                      (SELECT IFNULL(SUM(amount),0) FROM transactions WHERE account_id = $idA AND type = 'withdrawal') as saldo";
    
    $resSaldo = mysqli_query($db, $qSaldo);
    
    // Se la query del saldo fallisce
    if (!$resSaldo) {
        $response->getBody()->write(json_encode(["error" => "Errore SQL Saldo: " . mysqli_error($db)]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $row = mysqli_fetch_assoc($resSaldo);
    $currentBalance = $row['saldo'] ?? 0;
    $newBalance = $currentBalance + $amount;

    // Query di inserimento
    $query = "INSERT INTO transactions (account_id, type, amount, description, balance_after) 
              VALUES ($idA, 'deposit', $amount, '$description', $newBalance)";
    
    if (!mysqli_query($db, $query)) {
        // Se l'inserimento fallisce, scriviamo PERCHÉ
        $response->getBody()->write(json_encode(["error" => "Errore SQL Insert: " . mysqli_error($db)]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $response->getBody()->write(json_encode(["message" => "Successo!", "balance" => $newBalance]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
}
 /*
  public function pushDeposit(Request $request, Response $response, $args){
    $db = $this->getConnection();
    $idA = $args['id'];
    $data = json_decode($request->getBody(), true);
    $amount = $data['amount'] ?? 0;
    $description = $data['description'] ?? '';
    if ($amount <= 0) {
      $response->getBody()->write(json_encode(["error" => "Import must be higher than 0"]));
      return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    $qSaldo = "SELECT (SELECT IFNULL(SUM(amount),0) FROM transactions WHERE account_id = $idA AND type = 'deposit') - 
                      (SELECT IFNULL(SUM(amount),0) FROM transactions WHERE account_id = $idA AND type = 'withdrawal') as saldo";
    $currentBalance = mysqli_fetch_assoc(mysqli_query($db, $qSaldo))['saldo'];
    $newBalance = $currentBalance + $amount;
    $query = "INSERT INTO transactions (account_id, type, amount, description, balance_after) 
              VALUES ($idA, 'deposit', $amount, '$description', $newBalance)";
    mysqli_query($db, $query);
    $response->getBody()->write(json_encode(["message" => "Deposit successful", "balance" => $newBalance]));
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
  }
*/
  public function pushWithDrawal(Request $request, Response $response, $args){
    $db = $this->getConnection();
    $idA = $args['id'];
    $data = json_decode($request->getBody(), true);
    $amount = $data['amount'] ?? 0;
    $description = $data['description'] ?? '';
    if ($amount <= 0) {
        $response->getBody()->write(json_encode(["error" => "Importo non valido"]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    $query2 = "SELECT (SELECT IFNULL(SUM(amount),0) FROM transactions WHERE account_id = $idA AND type = 'deposit') - 
                      (SELECT IFNULL(SUM(amount),0) FROM transactions WHERE account_id = $idA AND type = 'withdrawal') as saldo";
    $res = mysqli_query($db, $query2);
    $currentBalance = mysqli_fetch_assoc($res)['saldo'];
    if ($amount > $currentBalance) {
        $response->getBody()->write(json_encode(["error" => "Insufficient balance"]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    $newBalance = $currentBalance - $amount;
    $query = "INSERT INTO transactions (account_id, type, amount, description, balance_after) 
              VALUES ($idA, 'withdrawal', $amount, '$description', $newBalance)";
    mysqli_query($db, $query);
    $response->getBody()->write(json_encode(["message" => "Withdrawal made", "balance" => $newBalance]));
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
  }

  public function deleteMovement(Request $request, Response $response, $args){
    $db = $this->getConnection();
    $idT = $args['idT'];
    $idA = $args['idA']; 
    $checkQuery = "SELECT id FROM transactions WHERE account_id = $idA ORDER BY created_at DESC LIMIT 1";
    $res = mysqli_query($db, $checkQuery);
    $row = mysqli_fetch_assoc($res);
    $lastId = $row['id'] ?? null;
    if ($idT != $lastId) {
        $response->getBody()->write(json_encode(["error" => "You can just delete the last transaction"]));
        return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
    }
    $query = "DELETE FROM transactions WHERE id = $idT";
    mysqli_query($db, $query);
    $response->getBody()->write(json_encode(["message" => "Movement deleted"]));
    return $response->withHeader('Content-Type', 'application/json');
  }

}
