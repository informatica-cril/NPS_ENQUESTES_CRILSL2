#!/bin/bash
# Script para preparar la app antes de desplegar en cPanel

echo "🚀 Preparando aplicación para despliegue..."
echo ""

# 1. Instalar dependencias
echo "📦 Step 1: Instalando dependencias frontend..."
npm install
if [ $? -ne 0 ]; then
    echo "❌ Error en npm install"
    exit 1
fi

# 2. Compilar Vue
echo ""
echo "🔨 Step 2: Compilando Vue y Tailwind..."
npm run build
if [ $? -ne 0 ]; then
    echo "❌ Error en npm run build"
    exit 1
fi

# 3. Instalar composer
echo ""
echo "📚 Step 3: Instalando composer (sin dev)..."
composer install --no-dev
if [ $? -ne 0 ]; then
    echo "❌ Error en composer install"
    exit 1
fi

# 4. Optimizar Laravel
echo ""
echo "⚡ Step 4: Optimizando Laravel..."
php artisan config:cache

# 5. Ver archivos compilados
echo ""
echo ""
echo "✅ Compilación completada!"
echo ""
echo "Archivos generados en public/build/:"
ls -la public/build/ | head -20
echo ""
echo "📤 Próximo paso: git add . && git commit -m 'Production build' && git push origin main"
