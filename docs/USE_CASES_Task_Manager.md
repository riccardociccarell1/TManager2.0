# ✅ TASK MANAGER API
**Obiettivo:** realizzare un'applicazione REST API per la gestione delle task, organizzata secondo una struttura MVC con **Model**, **Controller** e **Service**.

L'applicazione non utilizzerà View grafiche: tutte le funzionalità verranno testate tramite **Postman**, attraverso richieste HTTP con dati in formato JSON.

**Architettura:**

`Client / Postman → Controller → Service → Model → Database`

---

## 👥 1. ATTORI DEL SISTEMA
### 👤 User
Un normale utente può:

- registrarsi;

- effettuare il login;

- effettuare il logout;

- visualizzare il proprio profilo;

- creare una task;

- visualizzare le proprie task;

- visualizzare una singola task;

- modificare una propria task;

- eliminare una propria task;

- cambiare lo stato di una propria task.

> ⚠️ **Regola:** un User non può vedere, modificare o eliminare le task appartenenti ad altri utenti.
### 👑 Owner
L'Owner è un utente con privilegi maggiori.

Può:

- effettuare il login;

- effettuare il logout;

- visualizzare il proprio profilo;

- creare e gestire le proprie task;

- visualizzare tutte le task presenti nel sistema;

- sapere a quale utente appartiene ogni task.

> ℹ️ Nella prima versione l'Owner può vedere le task degli altri utenti, ma non può modificarle o eliminarle.

---

## 🗃️ 2. ENTITÀ PRINCIPALI
### 👤 User
| Campo | Descrizione |
| --- | --- |
| `id` | Identificativo univoco |
| `name` | Nome dell'utente |
| `email` | Email |
| `password` | Password criptata |
| `role` | Ruolo: `user` oppure `owner` |
| `created_at` | Data creazione |
| `updated_at` | Data modifica |

### Ruoli disponibili
```text
user
owner

```
Il ruolo predefinito alla registrazione sarà:

```text
user

```

> 🔐 Un utente non può scegliere autonomamente il ruolo `owner` durante la registrazione.
### ✅ Task
| Campo | Descrizione |
| --- | --- |
| `id` | Identificativo della task |
| `user_id` | Proprietario della task |
| `title` | Titolo |
| `description` | Descrizione |
| `status` | Stato |
| `priority` | Priorità |
| `due_date` | Data di scadenza |
| `created_at` | Data creazione |
| `updated_at` | Data modifica |

### 🔗 Relazione
```text
User 1 ───────────── N Task

```
Un User può possedere più Task.

Una Task appartiene ad un solo User.

In Laravel la relazione sarà concettualmente:

```php
User hasMany Task
Task belongsTo User

```

---

## 🚦 3. STATO DELLE TASK
Una Task può avere uno dei seguenti stati:

| Stato | Significato |
| --- | --- |
| `pending` | Da iniziare |
| `in_progress` | In corso |
| `completed` | Completata |

Una nuova Task viene creata automaticamente con:

```text
status = pending

```

---

## 🔥 4. PRIORITÀ DELLE TASK
Le priorità disponibili sono:

```text
low
medium
high

```
La priorità predefinita sarà:

```text
medium

```

---

# 📋 CASI D'USO

---

## UC01 — Registrazione utente
### 👤 Attore
`User`

### 🎯 Obiettivo
Permettere a un nuovo utente di creare un account.

### 📥 Input
```json
{
    "name": "Riccardo",
    "email": "riccardo@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}

```
### ⚙️ Flusso principale
- L'utente invia i propri dati.

- Il sistema valida i dati.

- Il sistema verifica che l'email non sia già utilizzata.

- La password viene criptata.

- Viene creato il nuovo utente.

- Il sistema assegna automaticamente il ruolo `user`.

### ✅ Risultato
L'utente viene registrato correttamente.

### ❌ Possibili errori
- email già utilizzata;

- email non valida;

- password mancante;

- password di conferma differente;

- campi obbligatori mancanti.

### 🔐 Regola di sicurezza
Questo input:

```json
{
    "name": "Riccardo",
    "email": "riccardo@example.com",
    "password": "password123",
    "role": "owner"
}

```
non deve permettere all'utente di diventare Owner.

**Il ruolo viene deciso dal server.**

---

## UC02 — Login
### 👤 Attore
`User / Owner`

### 🎯 Obiettivo
Autenticare un utente registrato.

### 📥 Input
```json
{
    "email": "riccardo@example.com",
    "password": "password123"
}

```
### ⚙️ Flusso principale
- L'utente invia email e password.

- Il sistema cerca l'utente tramite email.

- Il sistema verifica la password.

- Se le credenziali sono corrette viene generato un token.

- Il token viene restituito al client.

### ✅ Risultato
L'utente è autenticato.

### ❌ Possibili errori
- email inesistente;

- password errata;

- campi mancanti.

---

## UC03 — Logout
### 👤 Attore
`User / Owner autenticato`

### 🎯 Obiettivo
Terminare l'autenticazione corrente.

### ⚙️ Flusso principale
- L'utente invia una richiesta di logout.

- Il sistema identifica l'utente tramite il token.

- Il token corrente viene eliminato.

### ✅ Risultato
L'utente non può più utilizzare quel token.

### ❌ Possibili errori
- token assente;

- token non valido;

- utente non autenticato.

---

## UC04 — Visualizzazione profilo
### 👤 Attore
`User / Owner autenticato`

### 🎯 Obiettivo
Visualizzare i dati del proprio account.

### 📤 Output
```json
{
    "id": 1,
    "name": "Riccardo",
    "email": "riccardo@example.com",
    "role": "user"
}

```
### 🔐 Regola
Ogni utente può visualizzare solamente il proprio profilo.

---

## UC05 — Creazione Task
### 👤 Attore
`User / Owner autenticato`

### 🎯 Obiettivo
Creare una nuova task.

### 📥 Input
```json
{
    "title": "Studiare Laravel",
    "description": "Ripassare Model, Service e Controller",
    "priority": "high",
    "due_date": "2026-08-25"
}

```
### ⚙️ Flusso principale
- L'utente invia i dati della task.

- Il sistema valida i dati.

- Il sistema identifica l'utente autenticato.

- Il sistema assegna automaticamente `user_id`.

- Il sistema assegna automaticamente `status = pending`.

- La task viene salvata.

### ✅ Risultato
Viene creata una nuova Task appartenente all'utente autenticato.

### 🔐 Regola fondamentale
Il client non deve inviare:

```json
{
    "user_id": 15
}

```
Il proprietario viene recuperato dal token dell'utente autenticato.

Concettualmente:

```php
$userId = auth()->id();

```

---

## UC06 — Visualizzazione delle proprie Task
### 👤 Attore
`User autenticato`

### 🎯 Obiettivo
Visualizzare tutte le proprie task.

### ⚙️ Flusso principale
- L'utente invia la richiesta.

- Il sistema identifica l'utente.

- Il sistema recupera solamente le task appartenenti a quell'utente.

- Le task vengono restituite in JSON.

### 🔐 Regola
```text
task.user_id = authenticated_user.id

```
Un User non deve ricevere task appartenenti ad altri utenti.

---

## UC07 — Visualizzazione singola Task
### 👤 Attore
`User autenticato`

### 🎯 Obiettivo
Visualizzare una specifica task tramite ID.

### 🌐 Esempio richiesta
```text
GET /api/tasks/12

```
### ⚙️ Flusso principale
- Il sistema cerca la Task con ID `12`.

- Verifica che la Task appartenga all'utente autenticato.

- Se appartiene all'utente, la restituisce.

### ❌ Caso non autorizzato
Se la Task appartiene ad un altro User:

```text
403 Forbidden

```
oppure:

```text
404 Not Found

```
La scelta definitiva verrà fatta durante l'implementazione.

---

## UC08 — Modifica Task
### 👤 Attore
`User autenticato`

### 🎯 Obiettivo
Modificare una propria task.

### 📥 Input
```json
{
    "title": "Studiare Laravel API",
    "description": "Ripassare Model, Controller e Service",
    "priority": "high",
    "due_date": "2026-08-26"
}

```
### ⚙️ Flusso principale
- Il sistema recupera la Task.

- Verifica che appartenga all'utente autenticato.

- Valida i nuovi dati.

- Aggiorna la Task.

### 🔐 Regole
- `user_id` non può essere modificato;

- una Task non può essere trasferita ad un altro User;

- un User non può modificare Task altrui.

---

## UC09 — Cambio stato Task
### 👤 Attore
`User autenticato`

### 🎯 Obiettivo
Cambiare lo stato di una propria Task.

### 📥 Input
```json
{
    "status": "in_progress"
}

```
oppure:

```json
{
    "status": "completed"
}

```
### ✅ Valori ammessi
```text
pending
in_progress
completed

```
### 🔐 Regola
L'utente può cambiare lo stato solamente delle proprie Task.

---

## UC10 — Eliminazione Task
### 👤 Attore
`User autenticato`

### 🎯 Obiettivo
Eliminare una propria Task.

### ⚙️ Flusso principale
- Il sistema recupera la Task.

- Verifica il proprietario.

- Se appartiene all'utente autenticato, la elimina.

### ❌ Caso non autorizzato
Un User non può eliminare Task appartenenti ad altri utenti.

---

## UC11 — Owner visualizza tutte le Task
### 👤 Attore
`Owner`

### 🎯 Obiettivo
Permettere all'Owner di visualizzare tutte le Task presenti nel sistema.

### ⚙️ Flusso principale
- L'Owner invia la richiesta.

- Il sistema verifica che `role = owner`.

- Recupera tutte le Task.

- Per ogni Task recupera anche il proprietario.

- Restituisce i dati in JSON.

### 📤 Output di esempio
```json
[
    {
        "id": 1,
        "title": "Studiare Laravel",
        "status": "pending",
        "priority": "high",
        "user": {
            "id": 3,
            "name": "Riccardo",
            "email": "riccardo@example.com"
        }
    }
]

```
### ❌ Caso User normale
Se un normale User prova ad accedere:

```text
403 Forbidden

```

---

# 🔐 MATRICE DEI PERMESSI
| Operazione | 👤 User | 👑 Owner |
| --- | --- | --- |
| Registrazione | ✅ | — |
| Login | ✅ | ✅ |
| Logout | ✅ | ✅ |
| Visualizzare proprio profilo | ✅ | ✅ |
| Creare propria Task | ✅ | ✅ |
| Visualizzare proprie Task | ✅ | ✅ |
| Modificare propria Task | ✅ | ✅ |
| Eliminare propria Task | ✅ | ✅ |
| Cambiare stato propria Task | ✅ | ✅ |
| Visualizzare Task altrui | ❌ | ✅ |
| Visualizzare tutte le Task | ❌ | ✅ |
| Modificare Task altrui | ❌ | ❌ |
| Eliminare Task altrui | ❌ | ❌ |

---

# 🌐 ENDPOINT PREVISTI
### Autenticazione
```text
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/me

```
### Task
```text
GET    /api/tasks
POST   /api/tasks
GET    /api/tasks/{id}
PUT    /api/tasks/{id}
PATCH  /api/tasks/{id}/status
DELETE /api/tasks/{id}

```
### Owner
```text
GET    /api/owner/tasks

```

---

# 🏗️ ARCHITETTURA PREVISTA
### Models
```text
User
Task

```
Responsabilità:

- rappresentare i dati;

- definire le relazioni;

- comunicare con il database tramite Eloquent.

### Controllers
```text
AuthController
TaskController
OwnerTaskController

```
### AuthController
```text
register
login
logout
me

```
### TaskController
```text
index
store
show
update
updateStatus
destroy

```
### OwnerTaskController
```text
index

```
### Services
```text
AuthService
TaskService

```
Il Controller riceve la richiesta HTTP.

Il Service contiene la logica applicativa.

Il Model comunica con il database.

Esempio:

```text
POST /api/tasks
      ↓
TaskController::store()
      ↓
TaskService::createTask()
      ↓
Task::create()
      ↓
Database

```

---

# 🧪 FORM REQUEST PREVISTEq
```text
RegisterRequest
LoginRequest
StoreTaskRequest
UpdateTaskRequest
UpdateTaskStatusRequest

```
Serviranno per mantenere i Controller puliti e separare la validazione.

---

# 🛡️ REGOLE DI SICUREZZA
### 🔴 Regola 1 — Non fidarsi del `user_id`
Il proprietario della Task deve essere sempre ricavato dall'utente autenticato.

```php
auth()->id();

```
### 🔴 Regola 2 — Verificare sempre il proprietario
```text
Task richiesta
      ↓
appartiene all'utente autenticato?
      ↓
 SI          NO
 ↓            ↓
OK        Accesso negato

```
### 🔴 Regola 3 — Proteggere le funzioni Owner
```text
role == owner

```
Solo un Owner può accedere alle API riservate.

---

# 🚀 ORDINE DI SVILUPPO
```text
1. Creazione progetto Laravel
2. Configurazione database
3. Model User
4. Model Task
5. Migration Task
6. Relazione User ↔ Task
7. Autenticazione
8. AuthService
9. AuthController
10. Test Auth con Postman
11. TaskService
12. TaskController
13. CRUD Task
14. Test CRUD con Postman
15. Autorizzazioni
16. Ruolo Owner
17. OwnerTaskController
18. Endpoint Owner
19. Test finali con Postman

```

---

# 🎯 TEST FINALE DEL PROGETTO
Il progetto sarà considerato funzionante quando:

```text
USER A crea una Task
        ↓
USER A vede la Task

USER B effettua il login
        ↓
USER B non vede la Task di USER A

USER B prova a visualizzare,
modificare o eliminare la Task di USER A
        ↓
ACCESSO NEGATO

OWNER effettua il login
        ↓
OWNER vede le Task di USER A e USER B

```
Tutte le operazioni verranno testate tramite:

**POSTMAN + HTTP + JSON**