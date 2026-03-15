# Cómo iniciar el sistema de vacaciones

Sigue estos pasos en orden para ejecutar el proyecto en tu máquina.

---

## Requisitos

- **PHP 8.2+**
- **Composer**
- **MySQL** (o **SQLite** para desarrollo rápido)
- **Node.js y npm** (opcional; solo si usas compilación de assets con Vite)

---

## 1. Clonar / abrir el proyecto

Abre una terminal en la carpeta del proyecto:

```bash
cd "C:\Users\Luis Morales\Documents\examen"
```

---

## 2. Instalar dependencias PHP

```bash
composer install
```

---

## 3. Configurar entorno

Copia el archivo de ejemplo y genera la clave de aplicación:

```bash
copy .env.example .env
php artisan key:generate
```

Edita **`.env`** y configura la base de datos:

**Con MySQL:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

**Con SQLite (rápido para pruebas):**

```env
DB_CONNECTION=sqlite
```

Luego crea el archivo de SQLite si no existe:

```bash
# En Windows (PowerShell), si no existe la base:
if (!(Test-Path database\database.sqlite)) { New-Item -ItemType File -Path database\database.sqlite }
```

---

## 4. Ejecutar migraciones

Crea todas las tablas (users, sessions, roles, areas, employees, vacation_requests, notifications, etc.):

```bash
php artisan migrate
```

Si preguntara “¿crear la base de datos?”, responde **yes**.

---

## 5. Ejecutar seeders (datos iniciales)

Crea los **roles** (ADMIN, HR_MANAGER, AREA_MANAGER, EMPLOYEE) y las **áreas** (Desarrollo, QA, etc.):

```bash
php artisan db:seed
```

Si solo quieres roles y áreas:

```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AreaSeeder
```

*(AreaSeeder existe si se creó en el proyecto; si no, las áreas se pueden insertar después.)*

---

## 6. Levantar el servidor

```bash
php artisan serve
```

El sistema quedará en: **http://127.0.0.1:8000**

---

## 7. Entrar al sistema

1. Abre el navegador en **http://127.0.0.1:8000**.
2. **Regístrate** (Register) o **inicia sesión** (Login) si ya tienes usuario.
3. Si usaste `DatabaseSeeder` por defecto, puede existir un usuario de prueba (revisa `database/seeders/DatabaseSeeder.php`).
4. Para poder **aprobar solicitudes** o usar todas las pantallas, asigna un rol al usuario. Por ejemplo, en **Tinker**:

```bash
php artisan tinker
```

Dentro de Tinker:

```php
$user = \App\Models\User::first();
$user->assignRole('ADMIN');
exit
```

---

## 8. (Opcional) Cola para correos

Si quieres que los **emails** de notificación se envíen en segundo plano:

1. En `.env` deja `QUEUE_CONNECTION=database`.
2. En otra terminal, ejecuta:

```bash
php artisan queue:work
```

Si no ejecutas la cola, los correos se pueden enviar en modo **sync** (cambiando `QUEUE_CONNECTION=sync` en `.env`) o quedar en el log según tu configuración de **MAIL_MAILER**.

---

## Resumen rápido (una sola vez)

```bash
cd "C:\Users\Luis Morales\Documents\examen"
composer install
copy .env.example .env
php artisan key:generate
# Configurar DB en .env (MySQL o SQLite)
php artisan migrate
php artisan db:seed
php artisan serve
```

Luego abre **http://127.0.0.1:8000**, regístrate o inicia sesión y asigna un rol (por ejemplo ADMIN) al usuario con Tinker si es necesario.

---

## Si faltan tablas (áreas, empleados, solicitudes)

Si al entrar a **Empleados**, **Áreas** o **Solicitudes** ves errores de tablas inexistentes, el proyecto puede no incluir aún las migraciones de `areas`, `employees` y `vacation_requests`. En ese caso hay que añadir esas migraciones (según el diseño en `docs/DOCUMENTACION_TECNICA.md`) y ejecutar de nuevo:

```bash
php artisan migrate
```
