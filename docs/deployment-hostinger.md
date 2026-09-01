# Despliegue TREBBIA en Hostinger + GitHub

Dominio de produccion: `trebbia.app`

## Estado local

- Proyecto: Laravel 12
- Rama: `main`
- Build frontend: `npm.cmd run build`
- Base de datos recomendada en Hostinger: MySQL
- Carpeta publica Laravel: `public`

## Comandos CMD locales

Ejecutar desde:

```bat
cd C:\xampp\htdocs\TREBBIA
```

Preparar y validar:

```bat
composer install
npm.cmd install
npm.cmd run build
php artisan test
vendor\bin\pint --test
```

Primer commit:

```bat
git add .
git commit -m "Initial TREBBIA Laravel SaaS foundation"
```

Conectar GitHub cuando tengas la URL del repositorio:

```bat
git remote add origin https://github.com/TU_USUARIO/trebbia.git
git branch -M main
git push -u origin main
```

Actualizar despues de cada cambio:

```bat
git status
git add .
git commit -m "Describe el cambio"
git push
```

## Hostinger hPanel

Segun la documentacion actual de Hostinger, la integracion Git se configura desde hPanel en:

`Websites` -> sitio `trebbia.app` -> `Dashboard` -> `Advanced` -> `Git`

Luego:

1. Conectar GitHub con OAuth.
2. Seleccionar el repositorio `trebbia`.
3. Seleccionar rama `main`.
4. Usar `public_html` como root directory si el sitio apunta ahi.
5. Activar auto-deploy si quieres que cada `git push` despliegue automaticamente.

Hostinger indica que el directorio destino debe estar vacio para el primer despliegue. Si hay archivos previos en `public_html`, conviene respaldarlos y limpiarlos desde File Manager antes de conectar Git.

## Flujo de trabajo en vivo

Para trabajar con cambios visibles casi en tiempo real:

1. Hostinger debe quedar conectado al repositorio `https://github.com/ypernia/TREBBIA.git`.
2. La rama de despliegue debe ser `main`.
3. Auto-deployment debe estar activo en hPanel.
4. Cada cambio local se valida, se confirma con commit y se sube a GitHub.
5. Hostinger despliega automaticamente el nuevo commit.

Comandos CMD para cada tanda de cambios:

```bat
cd C:\xampp\htdocs\TREBBIA
php artisan test
vendor\bin\pint --test
npm.cmd run build
git status
git add .
git commit -m "Describe el cambio"
git push
```

Despues del `git push`, revisar en Hostinger:

- `Advanced` -> `Git`
- Estado del deployment
- Build output si algo falla

Para produccion conviene trabajar en cambios pequenos. Si un cambio incluye migraciones de base de datos, despues del deploy hay que ejecutar en SSH:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si el cambio solo modifica Blade, CSS o controladores sin migraciones, normalmente basta con el auto-deploy de Hostinger.

## Laravel en Hostinger

Este repo incluye un `.htaccess` en la raiz que redirige las solicitudes hacia `public/`, siguiendo el patron recomendado por Hostinger para Laravel cuando el proyecto se despliega dentro de `public_html`.

Despues del primer deploy:

1. Crear base de datos MySQL en hPanel.
2. Crear `.env` en el servidor usando `.env.production.example` como base.
3. Definir `APP_KEY`.
4. Ejecutar por SSH dentro de `public_html`:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si Hostinger ejecuta Composer automaticamente al desplegar un repo con `composer.json`, revisar el log de Git deployment en hPanel y ejecutar solo los comandos pendientes.

## DNS

En Hostinger, asigna el dominio `trebbia.app` al hosting donde quedara la app. Si el dominio no usa nameservers de Hostinger, apunta los DNS hacia el hosting indicado por hPanel.

## Secretos

No subir al repo:

- `.env`
- claves SMTP
- credenciales MySQL
- tokens de GitHub
- llaves privadas SSH

Usar `.env.production.example` solo como plantilla.
