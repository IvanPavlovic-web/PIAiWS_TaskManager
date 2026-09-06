# PIAiWS Task Manager

Ovo je SPA aplikacija za upravljanje zadacima razvijena u čistom PHP-u, sa SQLite bazom i JavaScript frontendom bez biblioteka. Aplikacija podržava registraciju, prijavu, sesije, CRUD operacije za kategorije i zadatke, filtere, pretragu i responzivan dizajn u minimalističkom crno-bijelom stilu.

## Tehnologije

- Backend: PHP 8.x bez frameworka
- Frontend: HTML5, CSS3, JavaScript (plain JS)
- Baza podataka: SQLite
- Sesije: native PHP session
- API: REST preko Fetch API-ja

## Šta je instalirano i korišteno

Za lokalni rad na Windows mašini koristili smo sljedeće komponente:

- XAMPP 8.2: https://www.apachefriends.org/index.html
- PHP 8.2: https://www.php.net/
- Apache HTTP server: https://httpd.apache.org/
- SQLite: https://www.sqlite.org/
- PDO SQLite ekstenzija (dio PHP instalacije)

Preporučena Windows opcija je XAMPP, jer u sebi sadrži Apache, PHP i SQLite podršku, i najjednostavniji je za početak.

## Karakteristike aplikacije

- Registracija korisnika
- Prijava i odjava
- Sesije za zaštitu API ruta
- Dodavanje, izmjena i brisanje kategorija
- Dodavanje, izmjena i brisanje zadataka
- Filtriranje po kategoriji i statusu
- Pretraga po naslovu
- Automatsko kreiranje SQLite baze pri prvom pokretanju
- Lozinke se čuvaju hash-irane koristeći `password_hash()`

## Struktura projekta

```text
PIAiWS_TaskManager/
├── index.html
├── style.css
├── app.js
├── router.php
├── .gitignore
├── database.sqlite        (automatski se kreira pri prvom pokretanju)
├── api/
│   ├── index.php
│   ├── db.php
│   ├── auth.php
│   ├── routes.php
│   ├── .htaccess
│   └── controllers/
│       ├── UserController.php
│       ├── CategoryController.php
│       └── TaskController.php
└── README.md
```

## Preduslovi

### Windows

Najjednostavnije je instalirati XAMPP:

1. Skinite XAMPP sa zvaničnog sajta:
   https://www.apachefriends.org/index.html
2. Instalirajte ga sa standardnim podešavanjima.
3. Nakon instalacije, proverite da postoje:
   - `C:\xampp\apache\bin\httpd.exe`
   - `C:\xampp\php\php.exe`

### Linux / macOS

Ako ne koristite XAMPP, instalirajte:

- PHP 8.x: https://www.php.net/downloads.php
- Apache: https://httpd.apache.org/
- SQLite: https://www.sqlite.org/download.html

Obavezno uključite `pdo_sqlite` ekstenziju u PHP konfiguraciji.

## 1) Instalacija na Windows-u (preporučeno)

### Korak 1: Instalirajte XAMPP

- Preuzmite XAMPP sa:
  https://www.apachefriends.org/index.html
- Instalirajte ga i pokrenite XAMPP Control Panel.

### Korak 2: Uvezite projekat u htdocs

Ako koristite XAMPP, kopirajte projekat u folder:

```text
C:\xampp\htdocs\PIAiWS_TaskManager
```

Na primjer, ako ste projekat otvorili u folderu `C:\Users\YourName\Desktop\task_manager_spa`, kopirajte ga u:

```text
C:\xampp\htdocs\PIAiWS_TaskManager
```

### Korak 3: Pokrenite Apache

U XAMPP Control Panel-u:

- kliknite na `Start` pored `Apache`
- ako je sve u redu, Apache će biti aktivan

### Korak 4: Otvorite aplikaciju

U browser-u idite na:

```text
http://localhost/PIAiWS_TaskManager/
```

Ako se aplikacija učita, sve je konfiguracija ispravno postavljena.

## 2) Pokretanje preko PHP development servera

Ovaj projekat ima i `router.php` fajl, pa ga možete pokrenuti direktno koristeći PHP CLI.

### Windows

U PowerShell-u izvršite:

```powershell
cd "C:\Users\Admin\Desktop\task_manager_spa"
& "C:\xampp\php\php.exe" -S localhost:8000 router.php
```

Zatim otvorite:

```text
http://localhost:8000/
```

### Linux / macOS

```bash
cd /path/to/PIAiWS_TaskManager
php -S localhost:8000 router.php
```

Zatim u browser-u:

```text
http://localhost:8000/
```

## 3) Pokretanje preko Apache-a

Ako je projekat kopiran u `htdocs`, Apache će automatski posluživati aplikaciju preko URL-a:

```text
http://localhost/PIAiWS_TaskManager/
```

Napomena:

- `api/.htaccess` služi za prepisivanje ruta ka `api/index.php`
- `router.php` se koristi uglavnom za lokalni PHP dev server, a ne za produkcioni Apache setup

## 4) Automatsko kreiranje baze

Kada aplikacija prvi put pristupi SQLite bazi, ona će se automatski kreirati ako ne postoji.

Baza će biti kreirana u root folderu projekta kao:

```text
database.sqlite
```

U bazi će se automatski napraviti tabele:

- `users`
- `categories`
- `tasks`

## 5) API rute

Aplikacija koristi REST API kroz PHP backend.

### Autentifikacija

- `POST /api/register`
- `POST /api/login`
- `GET /api/logout`

### Kategorije

- `GET /api/categories`
- `POST /api/categories`
- `PUT /api/categories/{id}`
- `DELETE /api/categories/{id}`

### Zadaci

- `GET /api/tasks`
- `POST /api/tasks`
- `PUT /api/tasks/{id}`
- `DELETE /api/tasks/{id}`

### Filteri i pretraga za zadatke

API podržava opcione query parametre:

- `?category=1`
- `?status=pending`
- `?search=ime zadatka`

Primjer:

```text
/api/tasks?category=1&status=pending
/api/tasks?search=Domaci
```

## 6) Sigurnost

Ova aplikacija implementira osnovne sigurnosne mehanizme:

- lozinke se spremaju hash-irane (`password_hash`)
- session-based autentifikacija
- protected API rute za sve osim `register` i `login`
- PDO prepared statements za SQLite upite
- korisnik može pristupiti samo svojim podacima

## 7) Potrebni linkovi

- PHP: https://www.php.net/
- Apache: https://httpd.apache.org/
- XAMPP: https://www.apachefriends.org/index.html
- SQLite: https://www.sqlite.org/
- PDO SQLite dokumentacija: https://www.php.net/manual/en/ref.pdo-sqlite.php

## 8) Uputstvo za pokretanje od nule

Ako želite da odmah pokrenete projekat na Windows mašini, uradite ovo:

1. Instalirajte XAMPP sa:
   https://www.apachefriends.org/index.html
2. Kopirajte projekat u:
   `C:\xampp\htdocs\PIAiWS_TaskManager`
3. Startujte Apache u XAMPP Control Panel-u
4. Otvorite u browser-u:
   `http://localhost/PIAiWS_TaskManager/`

Ako želite da pokrenete projekat direktno bez Apache-a:

```powershell
cd "C:\Users\Admin\Desktop\task_manager_spa"
& "C:\xampp\php\php.exe" -S localhost:8000 router.php
```

Nakon toga otvorite:

```text
http://localhost:8000/
```

## 9) Napomena o GitHub repozitorijumu

Ovaj projekat je pripremljen za GitHub i sadržava sve potrebne fajlove za rad, dok se SQLite baza i privremeni fajlovi automatski kreiraju prilikom pokretanja aplikacije. To znači da korisnici ne moraju ručno da naprave bazu — aplikacija to radi sama.

## 10) Licenca

Ovaj projekat je namenjen za obuku, razvoj i demonstraciju jednostavnog PHP + SQLite Task Manager SPA sistema.

---

Ako želite, mogu odmah da vam pripremim i završni GitHub commit i push baš na repozitorijum:
https://github.com/IvanPavlovic-web/PIAiWS_TaskManager.git
