# Tcocina - Preparacion para Produccion

Guia corta para dejar el sistema listo en hosting productivo con Google OAuth, sistema de soles y correo de bienvenida.

## 1) Variables de entorno recomendadas

Configurar en el servidor un `.env` de produccion (no reutilizar el local):

```env
APP_NAME=Tcocina
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tcocina_prod
DB_USERNAME=tcocina_user
DB_PASSWORD=tu_password_segura

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.com
MAIL_PORT=587
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
MAIL_SCHEME=null
MAIL_FROM_ADDRESS=no-reply@tu-dominio.com
MAIL_FROM_NAME="Tcocina"

GOOGLE_CLIENT_ID=tu_client_id_prod
GOOGLE_CLIENT_SECRET=tu_client_secret_prod
GOOGLE_REDIRECT_URI=https://tu-dominio.com/auth/google/callback

LOYALTY_WELCOME_EMAIL_ENABLED=true
LOYALTY_WELCOME_EMAIL_QUEUE=false
```

Notas:

- Si usas colas para mail, cambiar `LOYALTY_WELCOME_EMAIL_QUEUE=true` y mantener activo el worker.
- Mantener `APP_DEBUG=false` siempre en produccion.

## 2) Google Cloud Console (produccion)

En el cliente OAuth de produccion:

- Authorized JavaScript origins:
  - `https://tu-dominio.com`
- Authorized redirect URIs:
  - `https://tu-dominio.com/auth/google/callback`

Debe coincidir exactamente con `GOOGLE_REDIRECT_URI`.

## 3) Comandos de puesta en marcha

Ejecutar en el servidor:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si `QUEUE_CONNECTION=database`:

```bash
php artisan queue:table
php artisan migrate --force
php artisan queue:work --tries=3 --timeout=90
```

## 4) Supervisor (recomendado para colas)

Si decides usar queue para correos, correr el worker con Supervisor o servicio equivalente para que no se caiga.

Comando objetivo:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

## 5) Smoke test post deploy

1. Abrir home y login (`/`, `/login`).
2. Probar `/auth/google` y completar callback.
3. Verificar redireccion a `mi-progreso`.
4. Confirmar pedido desde admin y validar que suma soles.
5. Probar canje y validar sobrante.
6. Confirmar que usuario nuevo Google recibe email de bienvenida.

## 6) Correo de bienvenida (implementado)

- Se envia solo en alta de usuario por primer login Google.
- Si falla el proveedor de correo, el login no se corta.
- Se puede activar/desactivar por env:
  - `LOYALTY_WELCOME_EMAIL_ENABLED=true|false`
- Se puede enviar por cola:
  - `LOYALTY_WELCOME_EMAIL_QUEUE=true|false`
