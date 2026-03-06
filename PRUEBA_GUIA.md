# 🧪 GUÍA DE PRUEBA - Sistema CRIL

## ⚡ Inicio Rápido

### Opción 1: Script automático (Windows)
```bash
# Doble click en:
run-prueba-seeder.bat
```

### Opción 2: Comando artisan (Windows/Mac/Linux)
```bash
php artisan seed:prueba
```

### Opción 3: Manual
```bash
php artisan migrate:fresh
php artisan db:seed --class=PruebaSeeder
```

---

## 📊 Datos que se crean

### Centro
- **Nombre:** CRIL TEST
- **Dirección:** Carrer Test 123

### Usuarios

#### 👨‍💼 Admin
- **Email:** `admin@test.com`
- **Contraseña:** `admin123`
- **Acceso a:** Gestión de encuestas, pacientes, fisioterapeutas, reportes

#### 👨‍⚕️ Fisioterapeutas (2 usuarios)
- **Email:** `fisio1@test.com` / `fisio2@test.com`
- **Contraseña:** `fisio123`
- **Acceso a:** Solo dashboard con estadísticas NPS y lista de pacientes

#### 👥 Pacientes (5 usuarios)
- **CIP:** `CATA0000000001` - `CATA0000000005`
- **DNI:** `00000001A` - `00000005A`
- **Accceso a:** Portal de encuestas
- **Nota:** Usa CIP + DNI para login

### Encuestas
- **Nombre:** Enquesta NPS Test
- **tipo:** NPS (0-10)
- **Estado:** ACTIVA
- **Preguntas:** 2 (Pregunta NPS + Comentarios)
- **Asignados:** Todos los 5 pacientes
- **Estado:** PENDIENTE (ninguno ha respondido)

---

## 🔍 Casos de prueba

### 1️⃣ Crear/Editar Encuesta (SIN perder datos)

**Problema anterior:** Al editar una encuesta, se borraban las preguntas.

**Lo que se arregló:**
- ✅ El backend ahora maneja las preguntas en UPDATE
- ✅ Las preguntas existentes se reemplazan (no se pierden)
- ✅ Los cambios se guardan correctamente

**Cómo probar:**
1. Login como admin@test.com
2. Ve a Encuestas
3. Edita "Enquesta NPS Test"
4. Cambia el título
5. Modifica una pregunta
6. Guarda
7. ✅ Los datos deben estar intactos (no se pierden)

### 2️⃣ Ver Encuestas como Paciente

**Cómo probar:**
1. Login con CIP: `CATA0000000001` / DNI: `00000001A`
2. ✅ Debes ver "Enquesta NPS Test" en PENDIENTES
3. Haz click en "Respondre Ara"
4. Completa la encuesta
5. Verifica que aparezca en COMPLETADAS

### 3️⃣ Dashboard Fisioterapeuta

**Cómo probar:**
1. Login como fisio1@test.com / fisio123
2. ✅ Solo ves el Dashboard (sin otras opciones de menú)
3. Verás:
   - Estadísticas NPS
   - Lista de 5 pacientes
   - Número de encuestas (completadas/pendientes) por paciente

---

## 🛠️ Soluciones aplicadas

### Problema: Datos que se pierden al editar encuesta
**Causa:** El método `update()` del controlador no procesaba las preguntas

**Solución:**
```php
// Antes: Las preguntas se ignoraban
// Después: Se procesan y se actualizan correctamente

if ($preguntes !== null) {
    // 1. Borrar preguntas existentes
    $enquesta->preguntes()->delete();
    
    // 2. Crear nuevas preguntas
    foreach ($preguntes as $index => $preguntaData) {
        $enquesta->preguntes()->create([...]);
    }
}
```

### Problema: Composer install no funciona
**Causa:** Composer no está en PATH

**Soluciones:**
1. Usar scripts `.bat` / `.sh` que usan `php artisan`
2. Usar comandos artisan en lugar de CLI directo
3. Si necesitas reinstalar: `php composer.phar install`

---

## 📱 URLs de acceso

| rol | URL | Usuario |
|-----|-----|---------|
| Admin | http://localhost/admin/login | admin@test.com |
| Fisio | http://localhost/fisio/login | fisio1@test.com |
| Pacient | http://localhost/pacient/login | CIP + DNI |

---

## 🐛 Si algo no funciona

### Las encuestas no aparecen
```bash
# Verifica que las participaciones existan
php artisan tinker
>>> App\Models\Participacio::count()  # Debe mostrar 5+
>>> App\Models\Enquesta::count()      # Debe mostrar 1+
```

### Los datos se siguen perdiendo
- Verifica que hayas actualizado el archivo `EnquestaController.php`
- Devuelve cambios y ejecuta: `php artisan seed:prueba`

### Error de validación al editar
- Asegúrate de que las preguntas sean válidas:
  - `text_pregunta` no puede estar vacío
  - `tipus` debe ser uno de los valores válidos

---

## ✅ Checklist de validación

- [ ] Script `run-prueba-seeder.bat` ejecuta sin errores
- [ ] Se crean 5 pacientes
- [ ] Se crea 1 encuesta NPS
- [ ] Los datos de la encuesta no se pierden al editar
- [ ] Un paciente puede responder la encuesta
- [ ] El fisioterapeuta ve el dashboard
- [ ] El admin ve la encuesta en el listado

---

## 📝 Notas

- **Encuestas siempre en PENDIENTE:** El seeder crea todas las participaciones con `estat = 'pendent'`
- **Sin datos históricos:** No hay NPS resultados previos, todo empieza vacío
- **Centro único:** CRIL TEST es el único centro, todos los fisios/pacientes están ahí
- **Roles simples:** 1 admin, 2 fisios, 5 pacientes (sin mezcla de roles)

---

## 🚀 Próximos pasos

1. **Testing manual** de todas las funcionalidades
2. **Pruebas de carga** con más pacientes si es necesario
3. **Integración** en base de datos real con datos reales
4. **Refinar UI/UX** basado en feedback de usuarios reales
