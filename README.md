# GameMate — Vue 3 + Laravel 12 (working full-stack)

Pilna stack lietotne, kur spēlētāji izveido profilus, swipojas, matchojas un čatojas.

## Ātrais starts

### 1) Backend (Laravel)
```bash
cd Backend/example-app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve            # http://127.0.0.1:8000
```

### 2) Frontend (Vue 3 + Vuetify)
```bash
cd Frontend/vue-project
npm install
npm run dev                  # http://localhost:5173
```

Atver `http://localhost:5173`.

## Demo konti (no seedera)
- `alex@demo.com` / `password`
- `mia@demo.com` / `password`
- `jonas@demo.com` / `password`
- `eva@demo.com` / `password`
- `leo@demo.com` / `password`

Pieslēdzies kā divi dažādi lietotāji (piem. divos pārlūkos / incognito), pa-swipo viens otram → veidosies match → vari čatot.

## API galapunkti (`/api`)
| Metode | URL | Auth | Apraksts |
|---|---|---|---|
| POST | `/register` | – | `{name,email,password,gamertag}` |
| POST | `/login` | – | `{email,password}` → `{token,user}` |
| GET | `/me` | Bearer | Pašreizējais lietotājs ar profilu |
| POST | `/logout` | Bearer | Atsauc tokenu |
| PUT | `/profile` | Bearer | Atjaunina profilu |
| GET | `/profiles/{id}` | Bearer | Profila skats |
| GET | `/discover` | Bearer | Profili, kurus vēl neesi swipojis |
| POST | `/swipe` | Bearer | `{target_id, liked}` → `{matched, match_id}` |
| GET | `/matches` | Bearer | Tavi matchi |
| GET | `/matches/{id}/messages` | Bearer | Match čata vēsture |
| POST | `/matches/{id}/messages` | Bearer | `{body}` |

## Tech stack
- Laravel 12 + SQLite + custom bearer-token auth (`api_token` uz users)
- Vue 3 + Vue Router + Vuetify 3 (dark) + axios
- Vite proxy `/api → 127.0.0.1:8000` (CORS jau iestatīts)

## Stuktūra
```
Backend/example-app/
  app/Models/{User,Profile,Swipe,MatchRecord,Message}.php
  app/Http/Controllers/{AuthController,ProfileController,MatchController}.php
  app/Http/Middleware/ApiTokenAuth.php
  routes/api.php
  database/migrations/*
  database/seeders/DatabaseSeeder.php

Frontend/vue-project/src/
  App.vue, main.js, router.js, api.js, auth.js
  pages/{Home,Login,Register,Profile,Discover,Matches,Chat}.vue
```
