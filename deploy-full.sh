#!/bin/bash
# Script completo de despliegue para cPanel con SSH

set -e  # Detener si hay error

echo "📦 DESPLIEGUE COMPLETO EN cPANEL"
echo "=================================="
echo ""

# 1. Hacer git pull
echo "========================================================================================"
echo "1️⃣  Actualizando código desde GitHub..."
cd /www/wwwroot/enquestes.crilsl.org
git pull origin main
echo "✅ Git pull completado"
echo ""

# 2. Instalar dependencias
echo "========================================================================================"
echo "2️⃣  Instalando dependencias npm..."
npm install
echo "✅ npm install completado"
echo ""

echo "========================================================================================"
echo "3️⃣  Instalando dependencias composer..."
composer install --no-dev
echo "✅ composer install completado"
echo ""

# 3. Compilar Vue
echo "========================================================================================"
echo "4️⃣  Compilando Vue y Tailwind..."
npm run build
echo "✅ npm run build completado"
echo ""

# 4. Crear .env si no existe
echo "========================================================================================"
echo "5️⃣  Configurando .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env creado desde .env.example"
else
    echo "✅ .env ya existe"
fi
echo ""

# 5. Generar APP_KEY
echo "========================================================================================"
echo "6️⃣  Generando APP_KEY..."
php artisan key:generate
echo "✅ APP_KEY generada"
echo ""

# 6. Optimizar Laravel
echo "========================================================================================"
echo "7️⃣  Optimizando Laravel..."
php artisan config:cache
php artisan view:cache
php artisan route:cache
echo "✅ Cache optimizado"
echo ""

# 7. Permisos
echo "========================================================================================"
echo "8️⃣  Ajustando permisos..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 644 .env
echo "✅ Permisos ajustados"
echo ""

# 8. Storage link
echo "========================================================================================"
echo "9️⃣  Creando storage link..."
php artisan storage:link 2>&1 || true
echo "✅ Storage link creado"
echo ""

# 9. Migrar BD
echo "========================================================================================"
echo "🔟 Migrando base de datos..."
php artisan migrate --force
echo "✅ Migraciones completadas"
echo ""

echo "=================================="
echo "✅ ¡DESPLIEGUE COMPLETADO!"
echo ""
echo "🔗 URL: http://enquestes.crilsl.org/"
echo "=================================="
