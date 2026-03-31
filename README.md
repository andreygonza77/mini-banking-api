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

## 📝 Esempi di Chiamata (JSON)

### Registrare un Deposito
**POST** `http://localhost:8085/accounts/1/deposits`
```json
{
    "amount": 150.50,
    "description": "Ricarica mensile"
}
```
## Come avviare:

### Su Linux
`MY_UID=$(id -u) MY_GID=$(id -g) docker-compose up`

### Su Windows
`docker-compose up`

## Realizzato da:
### Gonzales 
### Stoppioni
### Calamai
