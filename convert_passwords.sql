-- Script SQL para convertir contraseñas bcrypt a MD5
-- Ejecutar este script en la base de datos del hosting si hay usuarios con contraseñas bcrypt

-- Primero, hacer backup de la tabla users
CREATE TABLE users_backup AS SELECT * FROM users;

-- Función para detectar si es bcrypt (empieza con $2y$ o $2a$ o $2b$)
-- Para contraseñas MD5, asumimos que son strings de 32 caracteres hexadecimales

-- Si tienes contraseñas bcrypt que quieres convertir a MD5,
-- necesitarías saber las contraseñas originales en texto plano.
-- Como no las tienes, este script solo identifica los tipos:

SELECT
    id,
    email,
    CASE
        WHEN password LIKE '$2%' THEN 'bcrypt'
        WHEN LENGTH(password) = 32 AND password REGEXP '^[a-f0-9]+$' THEN 'md5'
        ELSE 'unknown'
    END as password_type,
    password
FROM users;

-- NOTA: No se puede convertir bcrypt a MD5 sin la contraseña original.
-- Si tienes usuarios con bcrypt, tendrán que resetear sus contraseñas.