-- Script SQL para actualizar la base de datos con username y admin credentials
-- Ejecutar en la base de datos sql_enquestes_crilsl_org

-- Agregar columna username a la tabla users
ALTER TABLE users ADD COLUMN username VARCHAR(255) NULL UNIQUE AFTER name;

-- Actualizar el admin con username 'admin' y contraseña 'CRIL2025' (MD5)
UPDATE users SET
    username = 'admin',
    password = MD5('CRIL2025')
WHERE email = 'admin@cril.es' OR role = 'admin';

-- Para usuarios existentes que no tienen username, puedes asignar uno basado en email
-- UPDATE users SET username = SUBSTRING_INDEX(email, '@', 1) WHERE username IS NULL AND email IS NOT NULL;

-- Verificar que el admin esté configurado correctamente
SELECT id, name, username, email, role, password FROM users WHERE role = 'admin';