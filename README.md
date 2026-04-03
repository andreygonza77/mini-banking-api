#  Mini Banking API

Esercitazione backend REST che simula un conto bancario semplificato.

Il fine di questo progetto è quello di esporre endpoint HTTP che restituiscono JSON senza realizzare un front-end.

## Schema utillizato (bank) 

### Tabella `accounts`
| Campo | Tipo | Descrizione |
| :--- | :--- | :--- |
| `id` | INT (PK) | Identificativo univoco del conto |
| `owner_name` | VARCHAR | Nome del titolare |
| `currency` | VARCHAR | Valuta base (default: EUR) |
| `created_at` | TIMESTAMP | Data di apertura |

### Tabella `transactions`
| Campo | Tipo | Descrizione |
| :--- | :--- | :--- |
| `id` | INT (PK) | Identificativo del movimento |
| `account_id` | INT (FK) | Riferimento al conto |
| `type` | ENUM | Tipo di operazione (`deposit` o `withdrawal`) |
| `amount` | DECIMAL | Importo dell'operazione |
| `description` | TEXT | Descrizione del movimento |
| `created_at` | TIMESTAMP | Data dell'operazione |

---

## API Endpoints

### Gestione Movimenti
| Metodo | Endpoint | Descrizione |
| :--- | :--- | :--- |
| `GET` | `/accounts/{id}/transactions` | Lista di tutti i movimenti di un conto |
| `GET` | `/accounts/{idA}/transactions/{idT}` | Dettaglio di un singolo movimento |
| `POST` | `/accounts/{id}/deposits` | Effettua un deposito (importo > 0) |
| `POST` | `/accounts/{id}/withdrawals` | Effettua un prelievo (solo se saldo disp.) |
| `PUT` | `/accounts/{idA}/transactions/{idT}` | Modifica la descrizione di un movimento |
| `DELETE` | `/accounts/{idA}/transactions/{idT}` | Elimina l'ultimo movimento inserito |

### Conversioni 
| Metodo | Endpoint | Parametri | Descrizione |
| :--- | :--- | :--- | :--- |
| `GET` | `/.../convert/fiat` | `?to=USD` | Converte saldo in valuta Fiat |
| `GET` | `/.../convert/crypto`| `?to=BTC` | Converte saldo in Cryptocurrency |

--- 

## Esempi di Chiamata 

### Ottenere una specifica transazione
**GET** `http://localhost:8085/accounts/1/transactions/1`

Comando:
``` bash
curl http://localhost:8085/accounts/1/transactions/1
```

### Registrare un Deposito
**POST** `http://localhost:8085/accounts/1/deposits`

Cosa aggiungere:
```json
{
    "amount": 150.50,
    "description": "Ricarica mensile"
}
```
Comando:
``` bash
curl -X POST http://localhost:8085/accounts/1/deposits -H "Content-Type: application/json" -d "{\"amount\":150.50, \"description\":\"Ricarica mensile\"}"
```

### Eliminare una transazione

**DELETE** `http://localhost:8085/accounts/1/transactions/4`

Cosa rimuove: ultima transazione 
Comando:
``` bash
curl -X DELETE http://localhost:8085/accounts/1/transactions/4 -H "Content-Type: application/json"
```
## Come avviare:

### Su Linux
`MY_UID=$(id -u) MY_GID=$(id -g) docker-compose up`

### Su Windows
`docker-compose up`

## Realizzato da:

### Gonzales Andrey, Stoppioni Diego, Calamai Neri, classe 5AIA

