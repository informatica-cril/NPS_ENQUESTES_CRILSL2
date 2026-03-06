@echo off
REM Script para ejecutar el seeder de prueba
echo 🌱 Ejecutando seeder de prueba...
echo.

REM Cambiar al directorio del proyecto
cd /d "%~dp0"

REM Ejecutar migraciones
echo [1/3] Ejecutando migraciones...
php artisan migrate:fresh
if errorlevel 1 (
    echo ❌ Error en migraciones
    pause
    exit /b 1
)

REM Ejecutar seeder
echo.
echo [2/3] Ejecutando seeder de prueba...
php artisan db:seed --class=PruebaSeeder
if errorlevel 1 (
    echo ❌ Error en seeder
    pause
    exit /b 1
)

REM Mensaje de éxito
echo.
echo.
echo ✅ ¡Seeder completado correctamente!
echo.
echo 📊 Datos creados:
echo   • 1 Centro: CRIL TEST
echo   • 2 Fisioterapeutas
echo   • 5 Pacientes
echo   • 1 Encuesta NPS asignada a todos (estado: PENDIENTE)
echo.
echo 🔐 Credenciales de prueba:
echo   Admin:   admin@test.com / admin123
echo   Fisio:   fisio1@test.com / fisio123
echo   Pacient: CATA0000000001 / 00000001A (CIP + DNI)
echo.
echo 💡 Próximos pasos:
echo   1. Abre la aplicación en el navegador
echo   2. Login como admin
echo   3. Ve a Encuestas y edita la que se creó
echo   4. Verifica que NO se pierdan los datos
echo   5. Los 5 pacientes verán la encuesta en su portal
echo.
pause
