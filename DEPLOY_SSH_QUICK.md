# 🚀 DESPLIEGUE RÁPIDO CON SSH EN cPANEL

## Ejecuta ESTO en tu terminal SSH:

```bash
cd /www/wwwroot/enquestes.crilsl.org

# 1. Actualizar desde GitHub
git pull origin main

# 2. Instalar dependencias
#    (asegúrate de que el root package.json ya contiene las dependencias de frontend
#     y la versión correcta de tailwind 3.x)
npm install
composer install --no-dev

# 3. Compilar Vue y Tailwind
#     el build usa la configuración de Tailwind ubicada en la raíz (copiada desde frontend)
npm run build

# 4. Crear .env (solo primera vez)
cp .env.example .env

# 5. Generar APP_KEY
php artisan key:generate

# 6. Permisos
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 644 .env

# 7. Optimizar Laravel
php artisan config:cache
php artisan view:cache
php artisan route:cache

# 8. Storage link (si falla, ignora el error)
php artisan storage:link 2>&1 || true

# 9. Migrar BD (primera vez únicamente)
php artisan migrate --force

# ✅ Listo!
```

**O ejecuta el script completo:**
```bash
bash /www/wwwroot/enquestes.crilsl.org/deploy-full.sh
```

---

## ✅ Verifica que funciona:

```
http://enquestes.crilsl.org/
```

Deberías ver la app Vue, no la página de inicio de Laravel.

---

## 📝 PRÓXIMAS VECES (solo updates):

```bash
cd /www/wwwroot/enquestes.crilsl.org
git pull origin main
npm run build
php artisan config:cache
```

Eso es todo. No necesitas volver a migrar ni generar keys.

