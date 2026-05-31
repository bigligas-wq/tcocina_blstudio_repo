# Configuracion de Entornos (.env)

Este archivo resume como configurar variables de entorno para:

- Desarrollo local
- Produccion

Importante:

- Nunca subas secretos reales al repositorio.
- `.env` de local y `.env` de produccion deben ser distintos.
- Para Google OAuth, `GOOGLE_REDIRECT_URI` debe coincidir exactamente con el valor autorizado en Google Cloud Console.

## 1) Local (127.0.0.1:8000)

Usa esta base como referencia en tu `.env` local:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=TU_DB_LOCAL
DB_USERNAME=TU_USUARIO_LOCAL
DB_PASSWORD=TU_PASSWORD_LOCAL

GOOGLE_CLIENT_ID=TU_GOOGLE_CLIENT_ID_LOCAL
GOOGLE_CLIENT_SECRET=TU_GOOGLE_CLIENT_SECRET_LOCAL
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

En Google Cloud Console (cliente OAuth de local):

- Authorized JavaScript origins:
  - `http://127.0.0.1:8000`
- Authorized redirect URIs:
  - `http://127.0.0.1:8000/auth/google/callback`

## 2) Produccion (tu dominio real)

Usa esta base como referencia en el `.env` del servidor:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=TU_DB_PROD
DB_USERNAME=TU_USUARIO_PROD
DB_PASSWORD=TU_PASSWORD_PROD

GOOGLE_CLIENT_ID=TU_GOOGLE_CLIENT_ID_PROD
GOOGLE_CLIENT_SECRET=TU_GOOGLE_CLIENT_SECRET_PROD
GOOGLE_REDIRECT_URI=https://tu-dominio.com/auth/google/callback

MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.com
MAIL_PORT=587
MAIL_USERNAME=TU_USUARIO_SMTP
MAIL_PASSWORD=TU_PASSWORD_SMTP
MAIL_FROM_ADDRESS=no-reply@tu-dominio.com
MAIL_FROM_NAME="Tcocina"

LOYALTY_WELCOME_EMAIL_ENABLED=true
LOYALTY_WELCOME_EMAIL_QUEUE=false
```

En Google Cloud Console (cliente OAuth de produccion):

- Authorized JavaScript origins:
  - `https://tu-dominio.com`
- Authorized redirect URIs:
  - `https://tu-dominio.com/auth/google/callback`

## 3) Recomendacion segura

Usar dos clientes OAuth separados:

- Cliente OAuth Local
- Cliente OAuth Produccion

Asi evitas mezclar credenciales y reduces riesgo de errores al desplegar.

## 4) Checklist despues de cambios en .env

Ejecutar:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

Luego probar:

- `/auth/google` (redirige a Google)
- callback de login (vuelve al sitio sin error)
- correo de bienvenida (solo alta Google nueva)
