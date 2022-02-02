# Applicazione mvp per autenticazione con token jwt in laravel

## Strumenti utilizzati
- libreria per token jwt https://github.com/tymondesigns/jwt-auth
- guida alla libreria https://jwt-auth.readthedocs.io/en/develop/
- ambiente di sviluppo devilbox 1.9.3 https://github.com/cytopia/devilbox
  - php ver. 8.0
  - apache ver. 2.4
  - configurato con accesso diretto a tutte le porte dei servizi
- database sqlite

## Comandi per attivare il progetto
- cp .env.example .env
- composer install
- npm install
- php artisan migrate
- php artisan jwt:secret

## Route create dentro al middleware 
### (escluse dal middleware dentro il controller le route register e login)
- POST /api/auth/register - per registrare un utente
- POST /api/auth/login - per eseguire il login ed ottenere il bearer token
- POST /api/auth/logout - per eseguire il logout tramite il token
- POST /api/auth/refresh - per rinnovare il token restituendone uno diverso
- GET /api/auth/me - per ottenere i dati utente tramite il token

## PHP UNIT
- per eseguire la php unit lanciare il comando TODO
- la unit verifica:
  - creazione utente
  - ricreazione stesso utente
  - login
  - ottenere i dati utente con il token
  - collegarmi con un token errato
  - fare il logout

## Istruzioni per generare un nuovo progetto da zero
TODO

# TODO correggere che il logout con token errato non dia errori
