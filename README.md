# temaDAW
Tema sem 1 DAW

Aplicație web pentru gestionarea unui hotel (camere, rezervări, contact), cu roluri:
- admin
- recepționer
- client

## Funcționalități
- Login/Register + sesiuni
- Roluri și acces pe pagini
- CRUD camere (admin)
- CRUD rezervări (admin/recepționer; client doar rezervările lui)
- Filtrare + căutare rezervări
- Dashboard admin cu statistici (COUNT/SUM/GROUP BY)
- Contact form + mesaje în admin
- Export CSV rezervări

## Tehnologii
- PHP
- MySQL
- XAMPP (local)
- Hosting: InfinityFree / AwardSpace

## Instalare (local)
1. Importă baza de date (SQL)
2. Copiază `includes/db.example.php` → `includes/db.php` și completează credențialele
3. Deschide în browser: `http://localhost/hotel/`

## Securitate
- Prepared statements (protecție SQL injection)
- Parole cu `password_hash()` / `password_verify()`
- Control acces prin sesiuni + roluri
- `htmlspecialchars()` la afișare (protecție XSS)

## Live Demo
- [(pune aici linkul de hosting)](https://hotel-meriot.infinityfreeapp.com/)
