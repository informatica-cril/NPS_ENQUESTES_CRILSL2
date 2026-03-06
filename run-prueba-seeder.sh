#!/bin/bash
cd "$(dirname "$0")"
echo "🌱 Ejecutando seeder de prueba..."
php artisan migrate:fresh
php artisan db:seed --class=PruebaSeeder
echo ""
echo "✅ Seeder completado!"
echo ""
echo "📝 Próximos pasos:"
echo "1. Abre la aplicación"
echo "2. Login como admin: admin@test.com / admin123"
echo "3. Crea/Edita una encuesta sin perder datos"
echo "4. Los 5 pacientes verán la encuesta en su portal"
