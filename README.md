# Mini Banking API 

REST backend exercise simulating a simplified bank account.

The goal of this school project is to expose HTTP endpoints that return JSON.

## Schema used (bank) 

### Table `accounts`
| Field | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK) | Unique account identifier |
| `owner_name` | VARCHAR | Owner name |
| `currency` | VARCHAR | Base currency (default: EUR) |
| `created_at` | TIMESTAMP | Opening date |

### Table `transactions`
| Field | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK) | Transaction identifier |
| `account_id` | INT (FK) | Account reference |
| `type` | ENUM | Operation type (`deposit` or `withdrawal`) |
| `amount` | DECIMAL | Operation amount |
| `description` | TEXT | Transaction description |
| `created_at` | TIMESTAMP | Operation date |

---

## API Endpoints

### Transaction Management
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/accounts/{id}` | Account's information |
| `GET` | `/accounts/{id}/balance` | Current balance of a user |
| `GET` | `/accounts/{id}/transactions` | List of all transactions for an account |
| `GET` | `/accounts/{idA}/transactions/{idT}` | Detail of a single transaction |
| `POST` | `/accounts/{id}/deposits` | Make a deposit (amount > 0) |
| `POST` | `/accounts/{id}/withdrawals` | Make a withdrawal (only if balance available) |
| `PUT` | `/accounts/{idA}/transactions/{idT}` | Edit the description of a transaction |
| `DELETE` | `/accounts/{idA}/transactions/{idT}` | Delete the last inserted transaction |

### Conversions 
| Method | Endpoint | Parameters | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/accounts/{id}/balance/convert/fiat` | `?to=USD` | Converts balance to Fiat currency |
| `GET` | `/accounts/{id}/balance/convert/crypto`| `?to=BTC` | Converts balance to Cryptocurrency |

--- 

## 📝 Call Example (JSON) 

### Get a specific transaction
**GET** `http://localhost:8085/accounts/1/transactions/1`

Command:
``` bash
curl http://localhost:8085/accounts/1/transactions/1
```

### Register a Deposit
**POST** `http://localhost:8085/accounts/1/deposits`

Payload:
```json
{
    "amount": 150.50,
    "description": "Monthly top-up"
}
```
Command:
``` bash
curl -X POST http://localhost:8085/accounts/1/deposits -H "Content-Type: application/json" -d "{\"amount\":150.50, \"description\":\"Monthly top-up\"}"
```

### Delete a transaction

**DELETE** `http://localhost:8085/accounts/1/transactions/4`

What it removes: last transaction 

Command:

``` bash
curl -X DELETE http://localhost:8085/accounts/1/transactions/4 -H "Content-Type: application/json"
```

### Crypto Conversion

**GET** `http://localhost:8085/accounts/1/balance/convert/crypto?to=BTC`

Command:

``` bash
curl http://localhost:8085/accounts/1/balance/convert/crypto?to=BTC
```

## How to start:

### On Linux
`MY_UID=$(id -u) MY_GID=$(id -g) docker-compose up`

### On Windows
`docker-compose up`

## Frontend 

The repository [mini-banking-frontend](https://github.com/andreygonza77/mini-banking-frontend.git) is the frontend for this backend project.

## Created by:

### Gonzales Andrey, Calamai Neri, Stoppioni Diego, classe **5AIA** 🐒🐵
