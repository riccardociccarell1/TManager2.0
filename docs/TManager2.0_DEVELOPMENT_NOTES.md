# 🗂️ TManager2.0 — Development Notes

> Documento di lavoro aggiornabile che descrive passo passo la costruzione del progetto **TManager2.0**.

---

# 1. 🎯 Obiettivo del progetto
TManager2.0 è un **Task Manager REST API** sviluppato con Laravel.

L'applicazione verrà testata tramite **Postman**, utilizzando richieste HTTP e dati in formato JSON.

Non utilizzeremo View grafiche.

L'architettura scelta è:

```text
Postman
   ↓
Request
   ↓
Controller
   ↓
Service
   ↓
Model
   ↓
Database

```
Responsabilità principali:

| Livello | Responsabilità |
| --- | --- |
| Request | Validazione dei dati ricevuti |
| Controller | Ricezione richiesta HTTP e risposta JSON |
| Service | Logica applicativa |
| Model | Rappresentazione dei dati e relazioni |
| Database | Persistenza dei dati |

L'obiettivo è mantenere i **Controller semplici** e spostare la logica nei **Service**.

---

# 2. 🏗️ Creazione del progetto Laravel
Il progetto è stato creato tramite Composer.

Comando utilizzato:

```powershell
composer create-project laravel/laravel TManager2.0

```
Dopo la creazione:

```powershell
cd TManager2.0

```
Per verificare che Laravel funzioni:

```powershell
php artisan --version

```
Per avviare il server:

```powershell
php artisan serve

```
Il server locale viene normalmente eseguito su:

```text
http://127.0.0.1:8000

```

---

# 3. ⚙️ Configurazione `.env`
Il progetto utilizza SQLite.

Nel file `.env`:

```env
DB_CONNECTION=sqlite

```
L'URL dell'applicazione è:

```env
APP_URL=http://localhost:8000

```
Durante la configurazione era stato scritto per errore:

```env
APP_URL=http://localhost:8000:8000

```
Questo generava l'errore:

```text
Invalid URI: Host is malformed.

```
Correggendo `APP_URL` il problema è stato risolto.

> ⚠️ Il file `.env` non deve essere pubblicato su GitHub.
Il file `.env.example`, invece, può essere versionato.

---

# 4. 🗃️ Database SQLite
Laravel è configurato per utilizzare:

```text
database/database.sqlite

```
Per controllare lo stato delle migration:

```powershell
php artisan migrate:status

```
Per eseguire le migration non ancora applicate:

```powershell
php artisan migrate

```
Durante le prime fasi del progetto, se il database è ancora vuoto e vogliamo ricrearlo completamente:

```powershell
php artisan migrate:fresh

```
`migrate:fresh`:

```text
elimina tutte le tabelle
        ↓
riesegue tutte le migration
        ↓
ricostruisce il database da zero

```
Va utilizzato con attenzione quando il database contiene dati importanti.

---

# 5. 👤 User
Laravel crea automaticamente il Model:

```text
app/Models/User.php

```
e la migration iniziale:

```text
database/migrations/0001_01_01_000000_create_users_table.php

```
Abbiamo deciso che uno User deve avere:

```text
id
name
surname
email
password
role
created_at
updated_at

```

---

# 6. 🧱 Aggiornamento migration `users`
Alla migration iniziale è stato aggiunto:

```php
$table->string('surname');

```
Il cognome **non deve essere unique**.

Quindi:

```php
$table->string('surname')->unique();

```
❌ sarebbe sbagliato, perché impedirebbe a due utenti di avere lo stesso cognome.

La versione corretta è:

```php
$table->string('surname');

```
L'email rimane invece univoca:

```php
$table->string('email')->unique();

```

---

# 7. 👑 Aggiunta del ruolo User / Owner
Il ruolo è stato aggiunto tramite una migration separata.

Comando:

```powershell
php artisan make:migration add_role_user --table=users

```
Migration:

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('role')->default('user');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}

```
Il valore predefinito è:

```text
user

```
Quindi un normale utente registrato diventa automaticamente:

```text
role = user

```
L'utente non deve poter scegliere autonomamente:

```text
role = owner

```

---

# 8. 🐛 Errore migration `user` / `users`
Durante l'esecuzione della migration era stato usato:

```php
Schema::table('user', ...)

```
Laravel però crea la tabella:

```text
users

```
Per questo compariva:

```text
SQLSTATE[HY000]: General error: 1 no such table: user

```
La correzione è stata:

```php
Schema::table('users', ...)

```

---

# 9. 🔄 Come funzionano migration multiple sulla stessa tabella
Laravel può avere più migration che riguardano `users`.

Esempio:

```text
create_users_table
        ↓
crea la tabella users

add_role_user
        ↓
modifica users aggiungendo role

```
Laravel tiene traccia delle migration già eseguite nella tabella:

```text
migrations

```
Il comando:

```powershell
php artisan migrate:status

```
permette di vedere:

```text
Ran
Pending

```
Una migration `Ran` non viene rieseguita con un normale:

```powershell
php artisan migrate

```

---

# 10. 📦 Model `User`
Abbiamo scelto di mantenere il Model semplice, come nei progetti precedenti.

Struttura prevista:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model that represents a user of the Task Manager.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Attributes that can be assigned.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
    ];

    /**
     * Attributes hidden from JSON responses.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attributes that Laravel converts automatically.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Get all tasks that belong to the user.
     *
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}

```

---

# 11. 🔒 `$hidden`
Nel Model User abbiamo:

```php
protected $hidden = [
    'password',
    'remember_token',
];

```
`$hidden` indica quali attributi non devono comparire quando il Model viene trasformato in JSON o array.

Esempio:

```php
return response()->json($user);

```
La password non verrà mostrata nella risposta.

Questo:

```text
password salvata nel database
        ↓
esiste
        ↓
ma viene nascosta nel JSON

```

---

# 12. 📥 `$fillable`
Nel Model:

```php
protected $fillable = [
    'name',
    'surname',
    'email',
    'password',
];

```
`$fillable` specifica quali campi possono essere assegnati tramite mass assignment.

Il campo:

```text
role

```
è volutamente escluso.

Questo aiuta a evitare che un utente possa registrarsi scegliendo arbitrariamente:

```json
{
    "role": "owner"
}

```

---

# 13. 📅 `casts()`
Nel Model manteniamo:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
    ];
}

```
Questo dice a Laravel di trattare `email_verified_at` come una data.

Non utilizziamo il cast:

```php
'password' => 'hashed'

```
perché abbiamo scelto di gestire esplicitamente l'hash della password nel `AuthService`.

---

# 14. 🔑 Installazione API e Laravel Sanctum
Per aggiungere il supporto alle API è stato eseguito:

```powershell
php artisan install:api

```
Questo ha aggiunto Laravel Sanctum e creato la migration:

```text
create_personal_access_tokens_table

```
La tabella:

```text
personal_access_tokens

```
viene utilizzata per memorizzare i token di autenticazione.

Nel Model `User` abbiamo aggiunto:

```php
use Laravel\Sanctum\HasApiTokens;

```
e:

```php
use HasApiTokens, HasFactory, Notifiable;

```
Grazie a questo possiamo successivamente fare:

```php
$user->createToken('auth_token')->plainTextToken;

```

---

# 15. ✅ RegisterRequest
È stata creata tramite:

```powershell
php artisan make:request RegisterRequest

```
Percorso:

```text
app/Http/Requests/RegisterRequest.php

```
La Request si occupa esclusivamente di:

```text
autorizzazione
+
validazione
+
messaggi di errore

```
Non deve:

```text
creare utenti
trasformare nome/cognome
hashare password
creare token

```
Queste operazioni appartengono al Service.

---

# 16. 🔐 `authorize()` nella RegisterRequest
La registrazione è pubblica, quindi:

```php
public function authorize(): bool
{
    return true;
}

```
Se viene lasciato:

```php
return false;

```
Laravel blocca la richiesta con:

```text
403
This action is unauthorized.

```
Questo errore è stato incontrato durante il primo test con Postman.

---

# 17. 📝 Regole di `RegisterRequest`
La Request valida:

```text
name
surname
email
password
password_confirmation
role

```
Struttura principale:

```php
public function rules(): array
{
    return [
        'name' => [
            'required',
            'string',
            'max:255',
        ],

        'surname' => [
            'required',
            'string',
            'max:255',
        ],

        'email' => [
            'required',
            'email',
            'max:255',
            'unique:users,email',
        ],

        'password' => [
            'required',
            'confirmed',
            Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols(),
        ],

        'role' => [
            'prohibited',
        ],
    ];
}

```
Import necessario:

```php
use Illuminate\Validation\Rules\Password;

```

---

# 18. 🔑 Regole password
Abbiamo scelto di utilizzare le regole native Laravel.

```php
Password::min(8)
    ->mixedCase()
    ->numbers()
    ->symbols()

```
Significa:

```text
min(8)
→ almeno 8 caratteri

mixedCase()
→ maiuscole e minuscole

numbers()
→ almeno un numero

symbols()
→ almeno un carattere speciale

```
Esempio valido:

```text
Password1!

```

---

# 19. ✔️ `confirmed`
La regola:

```php
'confirmed'

```
fa sì che Laravel richieda automaticamente:

```text
password_confirmation

```
Quindi Postman deve inviare:

```json
{
    "password": "Password1!",
    "password_confirmation": "Password1!"
}

```
Se i valori sono diversi, la validazione fallisce.

---

# 20. 🚫 `role` prohibited
Nella registrazione:

```php
'role' => [
    'prohibited',
],

```
impedisce al client di inviare il ruolo.

Una richiesta del tipo:

```json
{
    "name": "Riccardo",
    "surname": "Ciccarelli",
    "email": "riccardo@example.com",
    "password": "Password1!",
    "password_confirmation": "Password1!",
    "role": "owner"
}

```
deve essere rifiutata.

Il ruolo viene gestito dal server.

---

# 21. 💬 Messaggi di validazione
Dentro `RegisterRequest` utilizziamo messaggi personalizzati per i casi principali.

Esempio:

```php
public function messages(): array
{
    return [
        'name.required' => 'The name is required.',
        'name.string' => 'The name must be a string.',
        'name.max' => 'The name cannot be longer than 255 characters.',

        'surname.required' => 'The surname is required.',
        'surname.string' => 'The surname must be a string.',
        'surname.max' => 'The surname cannot be longer than 255 characters.',

        'email.required' => 'The email is required.',
        'email.email' => 'The email format is not valid.',
        'email.max' => 'The email cannot be longer than 255 characters.',
        'email.unique' => 'This email is already registered.',

        'password.required' => 'The password is required.',
        'password.confirmed' => 'The password confirmation does not match.',

        'role.prohibited' => 'The role cannot be selected during registration.',
    ];
}

```
Per i requisiti avanzati della password utilizziamo i messaggi nativi di Laravel.

---

# 22. 🔓 LoginRequest
È stata creata tramite:

```powershell
php artisan make:request LoginRequest

```
Il login richiede solamente:

```text
email
password

```
La Request controlla che i dati siano formalmente validi.

Non deve controllare:

```text
se l'utente esiste
se la password è corretta

```
Questa è logica applicativa e appartiene al `AuthService`.

Esempio:

```php
public function rules(): array
{
    return [
        'email' => [
            'required',
            'email',
        ],

        'password' => [
            'required',
            'string',
        ],
    ];
}

```
Anche qui:

```php
public function authorize(): bool
{
    return true;
}

```

---

# 23. 🧠 AuthService
Laravel non crea automaticamente una cartella `Services`.

È stata quindi creata manualmente:

```text
app/Services

```
e successivamente:

```text
app/Services/AuthService.php

```
Il namespace corretto è:

```php
namespace App\Services;

```
La classe deve chiamarsi:

```php
class AuthService

```
Il nome deve corrispondere al file:

```text
AuthService.php

```

---

# 24. 🐛 Errori incontrati con AuthService
Nel primo tentativo erano presenti:

```php
use app\Models\User;

```
e:

```php
class AuthServices

```
Sono stati corretti in:

```php
use App\Models\User;

```
e:

```php
class AuthService

```
La corrispondenza corretta è:

```text
File:
app/Services/AuthService.php

Namespace:
App\Services

Class:
AuthService

```
In caso di problemi con l'autoload:

```powershell
composer dump-autoload

```
e:

```powershell
php artisan optimize:clear

```

---

# 25. 👤 `registerUser()` nel AuthService
La logica della registrazione viene gestita nel Service.

```php
/**
 * Register a new user.
 *
 * The name and surname are converted to uppercase
 * before saving the user into the database.
 *
 * @param array $data
 * @return User
 */
public function registerUser(array $data): User
{
    return User::create([
        'name' => strtoupper($data['name']),
        'surname' => strtoupper($data['surname']),
        'email' => strtolower($data['email']),
        'password' => bcrypt($data['password']),
    ]);
}

```
Abbiamo deciso:

```text
name
→ uppercase

surname
→ uppercase

email
→ lowercase

password
→ bcrypt

```

---

# 26. 🔐 Hash password nel Service
Abbiamo scelto di gestire esplicitamente l'hash nel `AuthService`.

Attualmente:

```php
'password' => bcrypt($data['password']),

```
Questo significa che il Model non deve anche applicare:

```php
'password' => 'hashed'

```
Le due responsabilità non devono essere duplicate.

---

# 27. 🔑 `loginUser()` nel AuthService
Il login viene gestito nel Service.

```php
/**
 * Login a user and create an authentication token.
 *
 * @param array $data
 * @return array
 * @throws ValidationException
 */
public function loginUser(array $data): array
{
    // Search the user by email.
    $user = User::where(
        'email',
        strtolower($data['email'])
    )->first();

    // Check if the user exists and the password is correct.
    if (!
```
`data['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Invalid credentials.'],
        ]);
    }

    // Create a new authentication token for the user.
    user->createToken('auth_token')->plainTextToken;

    // Return the authenticated user and token.
    return [
        'user' => $user,
        'token' => $token,
    ];
}
`

Import:

```php
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

```

---

# 28. 🔍 Controllo password durante login
Usiamo:

```php
Hash::check(
```
`user->password)
`

Il flusso è:

```text
password ricevuta
        ↓
Hash::check()
        ↓
confronto con hash nel database
        ↓
true / false

```

---

# 29. ❌ Credenziali errate
Se l'utente non viene trovato oppure la password è sbagliata:

```php
if (!
```
`data['password'], $user->password)) {
    throw ValidationException::withMessages([
        'email' => ['Invalid credentials.'],
    ]);
}
`

Usiamo un unico messaggio:

```text
Invalid credentials.

```

---

# 30. 🎟️ Creazione token Sanctum
Quando le credenziali sono corrette:

```php

```
`user
    ->createToken('auth_token')
    ->plainTextToken;
`

Il token verrà utilizzato in Postman come:

```text
Authorization
Bearer Token

```

---

# 31. 🎮 AuthController
È stato creato tramite:

```powershell
php artisan make:controller AuthController

```
Percorso:

```text
app/Http/Controllers/AuthController.php

```
Il Controller non contiene logica applicativa.

---

# 32. 💉 Dependency Injection di AuthService
Nel Controller:

```php
private AuthService $authService;

```
e:

```php
public function __construct(AuthService $authService)
{
    
```
`authService;
}
`

Import:

```php
use App\Services\AuthService;

```

---

# 33. 📝 Metodo `register()` del Controller
```php
/**
 * Register a new user.
 *
 * @param RegisterRequest $request
 * @return JsonResponse
 */
public function register(RegisterRequest $request): JsonResponse
{
    // Send validated data to the AuthService.
    
```
`this->authService->registerUser(
        $request->validated()
    );

    // Return the created user.
    return response()->json([
        'message' => 'User registered successfully.',
        'user' => $user,
    ], 201);
}
`

---

# 34. 🔓 Metodo `login()` del Controller
```php
/**
 * Login a user.
 *
 * @param LoginRequest $request
 * @return JsonResponse
 */
public function login(LoginRequest $request): JsonResponse
{
    // Send validated data to the AuthService.
    
```
`this->authService->loginUser(
        $request->validated()
    );

    // Return the authenticated user and token.
    return response()->json([
        'message' => 'Login successful.',
        'user' => $result['user'],
        'token' => $result['token'],
    ], 200);
}
`

---

# 35. ✅ `$request->validated()`
Quando utilizziamo:

```php
$request->validated()

```
prendiamo i dati che hanno superato le regole definite nella Form Request.

---

# 36. 🌐 `routes/api.php`
Dopo:

```powershell
php artisan install:api

```
Laravel aveva già creato:

```php
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

```
Abbiamo aggiunto:

```php
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

```
Struttura:

```text
PUBBLICHE
├── POST /api/register
└── POST /api/login

PROTETTE
└── GET /api/user

```

---

# 37. 🛡️ Middleware `auth:sanctum`
Una route con:

```php
->middleware('auth:sanctum')

```
può essere utilizzata solamente da un utente autenticato tramite Sanctum.

Le future route delle Task saranno protette.

---

# 38. 🧪 Primo test registrazione con Postman
Endpoint:

```text
POST http://127.0.0.1:8000/api/register

```
Headers:

```text
Accept: application/json
Content-Type: application/json

```
Body:

```json
{
    "name": "Riccardo",
    "surname": "Ciccarelli",
    "email": "riccardo@example.com",
    "password": "Password1!",
    "password_confirmation": "Password1!"
}

```
Risultato atteso:

```json
{
    "message": "User registered successfully.",
    "user": {
        "id": 1,
        "name": "RICCARDO",
        "surname": "CICCARELLI",
        "email": "riccardo@example.com",
        "role": "user"
    }
}

```
HTTP Status:

```text
201 Created

```

---

# 39. 🐛 Errore `This action is unauthorized`
Durante il primo test Postman è comparso:

```json
{
    "message": "This action is unauthorized."
}

```
La causa era:

```php
public function authorize(): bool
{
    return false;
}

```
La correzione:

```php
public function authorize(): bool
{
    return true;
}

```

---

# 40. 📋 Task
Il Model `Task` è stato solamente creato.

Attualmente:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
}

```
La migration `tasks` è stata solamente creata e non ancora configurata:

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->timestamps();
});

```
Quindi:

```text
Task Model      → DA COMPLETARE
Task migration  → DA COMPLETARE
TaskService     → NON ANCORA CREATO
TaskController  → NON ANCORA CREATO
TaskRequest     → NON ANCORA CREATA

```

---

# 41. 🔗 Relazione User → Task
Nel Model `User` abbiamo già previsto:

```php
public function tasks(): HasMany
{
    return $this->hasMany(Task::class);
}

```
La relazione inversa verrà aggiunta quando configureremo `Task`.

---

# 42. 💻 Git e GitHub
Il progetto è stato aggiunto a GitHub Desktop e pubblicato sulla repository:

```text
TManager2.0

```
Branch:

```text
main

```
Flusso:

```text
VS Code
   ↓
modifiche
   ↓
GitHub Desktop
   ↓
commit
   ↓
Push origin

```

---

# 43. 📚 Stile del codice scelto
Per tutto il progetto manteniamo:

- codice semplice;

- commenti chiari;

- PHPDoc in inglese semplice;

- Model leggeri;

- logica nei Service;

- Controller leggeri;

- Request dedicate alla validazione;

- risposte JSON;

- test tramite Postman.

---

# 44. 🧭 Stato attuale del progetto
```text
PROJECT SETUP
├── Laravel                    ✅
├── SQLite                     ✅
├── Git / GitHub               ✅
└── API setup                  ✅

USER
├── User Model                 ✅
├── surname                    ✅
├── role                       ✅
├── HasApiTokens               ✅
└── tasks() relation           ✅

AUTH REQUESTS
├── RegisterRequest            ✅
└── LoginRequest               ✅

AUTH SERVICE
├── registerUser()             ✅
└── loginUser()                ✅

AUTH CONTROLLER
├── register()                 ✅
└── login()                    ✅

AUTH ROUTES
├── POST /api/register         ✅
├── POST /api/login            ✅
└── GET /api/user              ✅

TASK
├── Model                      ⏳
├── Migration                  ⏳
├── Requests                   ❌
├── Service                    ❌
├── Controller                 ❌
└── Routes                     ❌

```

---

# 45. 🚀 Prossimi step
```text
1. Test completo registrazione
2. Test login
3. Test token Sanctum
4. Test GET /api/user
5. Logout
6. Configurazione Task migration
7. Configurazione Task Model
8. Task Request
9. TaskService
10. TaskController
11. Task routes
12. Test CRUD Task con Postman
13. Gestione autorizzazioni
14. Gestione Owner

```

---

# 46. 🧠 Regola architetturale di riferimento
```text
REQUEST
│
├── validation
└── error messages

        ↓

CONTROLLER
│
├── receives HTTP request
├── calls Service
└── returns JSON

        ↓

SERVICE
│
├── business logic
├── transformations
├── authentication logic
└── database operations through Models

        ↓

MODEL
│
├── attributes
├── hidden fields
└── relationships

        ↓

DATABASE

```
Questa è la struttura di riferimento per tutto il progetto **TManager2.0**.

​

# 47. 🧠 Regola architetturale di riferimento
Integrazione della funzione logout: (la funzione logout eliminerà il current token dell'utente).

​

​