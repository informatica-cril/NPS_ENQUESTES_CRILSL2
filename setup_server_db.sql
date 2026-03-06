-- Script para verificar y actualizar la BD del servidor
-- Ejecutar en phpMyAdmin de sql_enquestes_crilsl_org

-- 1. PRIMERO: Ver qué usuarios existen actualmente
SELECT id, name, email, role, password, LENGTH(password) as pass_length
FROM users
ORDER BY role, id;

-- 2. Agregar columna username si no existe
ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(255) NULL UNIQUE AFTER name;

-- 3. Configurar el admin con username 'admin' y contraseña 'CRIL2025'
UPDATE users SET
    username = 'admin',
    password = MD5('CRIL2025'),
    is_active = 1
WHERE role = 'admin' OR email = 'admin@cril.es';

-- 4. Verificar que el admin esté configurado correctamente
SELECT id, name, username, email, role, password, is_active
FROM users
WHERE role = 'admin' OR username = 'admin';

-- 5. OPCIONAL: Para otros usuarios, crear usernames basados en email
-- UPDATE users SET username = SUBSTRING_INDEX(email, '@', 1)
-- WHERE username IS NULL AND email IS NOT NULL AND email != '';

-- 6. Verificar que todos los usuarios tengan username o email
SELECT id, name, username, email, role
FROM users
WHERE (username IS NULL OR username = '') AND (email IS NULL OR email = '');