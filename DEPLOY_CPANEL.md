# 📦 GUÍA DE DESPLIEGUE EN cPANEL

## ⭐ TIENES SSH? (RECOMENDADO)

**Lee esto:** [DEPLOY_SSH_QUICK.md](DEPLOY_SSH_QUICK.md)

Es **MUCHO MÁS FÁCIL**. Solo ejecuta los comandos y funciona todo.

---

## Sin SSH (usando File Manager)

## Información del Hosting
- **Dominio**: http://enquestes.crilsl.org/
- **cPanel**: https://192.168.1.184:8888/
- **Base de Datos**: `sql_enquestes_crilsl_org`
- **Usuario BD**: `sql_enquestes_crilsl_org`

---

## 📋 ANTES DE DESPLEGAR (En tu PC)

### 1. Compila la app Vue
```bash
cd VUE_NPM_new
npm install
npm run build
```
Esto généra los archivos compilados en `public/build/`

### 2. Verifica que todo esté listo
```bash
composer install --no-dev
php artisan config:cache
```

### 3. Sube todo a GitHub
```bash
git add .
git commit -m "Production build"
git push origin main
```

**⚠️ IMPORTANTE**: Subes `public/build/` pero NO subes `node_modules/` ni `frontend/`

---

## 🚀 EN CPANEL (Sin SSH)

### Paso 1: Crea la carpeta en cPanel
1. Entra en File Manager de cPanel
2. Navega a `/home/[usuario]/public_html/`
3. Crea una carpeta llamada `enquestes` (si no existe)

### Paso 2: Clona desde GitHub
En File Manager, dentro de `/public_html/enquestes/`:

**Opción A - Si cPanel soporta Git:**
1. Haz clic derecho → "Create File" → `deploy.php`
2. Pega esto:

```php
<?php
exec('cd ' . __DIR__ . ' && git pull origin main 2>&1', $output);
echo '<pre>' . implode("\n", $output) . '</pre>';
?>
```

3. Asegúrate de que `.git` existe en `/public_html/enquestes/`
4. Accede a `http://enquestes.crilsl.org/deploy.php` para hacer pull
5. **Elimina el archivo `deploy.php` después de usarlo** por seguridad

**Opción B - Descarga manual:**
1. Descarga el proyecto desde GitHub como ZIP
2. Extrae en `/public_html/enquestes/`

### Paso 3: Configura el .env
1. En File Manager, entra a `/public_html/enquestes/`
2. Copia `.env.example` → `.env`
3. Edita `.env` con estos valores:

```env
APP_NAME="NPS Enquestes CRIL"
APP_ENV=production
APP_KEY=base64:AQUI_VA_TU_APP_KEY
APP_DEBUG=false
APP_URL=http://enquestes.crilsl.org

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sql_enquestes_crilsl_org
DB_USERNAME=sql_enquestes_crilsl_org
DB_PASSWORD=13e6df30f8ff98

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### Paso 4: Permisos (En cPanel Terminal si tienes)
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 644 .env
```

Si no tienes terminal en cPanel:
- Dirígete a File Manager
- Haz clic derecho en `storage/` → Change Permissions → 755
- Haz lo mismo con `bootstrap/cache/`

### Paso 5: Genera la APP_KEY
1. En File Manager, crea un archivo llamado `key_generator.php` en `/public_html/enquestes/`
2. Pega esto:

```php
<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
echo shell_exec('php artisan key:generate --show');
?>
```

3. Accede a `http://enquestes.crilsl.org/key_generator.php`
4. Copia el valor `base64:...` que sale
5. Pega en tu `.env` en la línea `APP_KEY=`
6. **Elimina `key_generator.php`** por seguridad

### Paso 6: Optimiza Laravel
Crea un archivo `optimize.php`:

```php
<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
echo '<pre>';
echo shell_exec('php artisan config:cache') . "\n";
echo shell_exec('php artisan view:cache') . "\n";
echo shell_exec('php artisan storage:link 2>&1 || true') . "\n";
echo '</pre>';
?>
```

Accede a `http://enquestes.crilsl.org/optimize.php` y luego **elimina el fichero**.

### Paso 7: Migra la BD (Una sola vez)
Crea un archivo `migrate.php`:

```php
<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
echo '<pre>';
echo shell_exec('php artisan migrate --force 2>&1') . "\n";
echo '</pre>';
?>
```

Accede a `http://enquestes.crilsl.org/migrate.php` y luego **elimina el fichero**.

---

## ✅ Verifica que funciona

```
http://enquestes.crilsl.org/
```

Deberías ver tu app Vue, no la página de inicio de Laravel.

---

## 📝 PRÓXIMAS VECES (Actualizar código)

Para futuras actualizaciones sin SSH:

1. En tu PC: `npm run build` + Git push
2. En cPanel: Usa `deploy.php` para hacer git pull (o descarga ZIP y extrae)
3. Listo, sin necesidad de compilar nada en cPanel

---

## ⚠️ SEGURIDAD

Después de cada paso, **ELIMINA los archivos PHP** que creaste:
- `deploy.php`
- `key_generator.php`
- `optimize.php`
- `migrate.php`

Estos archivos son solo para setup inicial.

---

## 🆘 Si algo falla

1. **Ver errores**: Revisa el archivo `storage/logs/laravel.log`
2. **Cache corrupto**: Elimina contenido de `/storage/framework/cache/`
3. **Permisos**: Asegúrate que `storage/` y `bootstrap/cache/` tienen 755

¡Listo! 🎉
