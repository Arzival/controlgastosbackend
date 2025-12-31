# Control de Gastos - Backend API

## 📋 Descripción

API REST desarrollada en Laravel 12 para el backend del sistema de control de gastos. Proporciona endpoints seguros para la gestión de usuarios, transacciones financieras, fondos de ahorro y categorías. Utiliza Laravel Sanctum para autenticación basada en tokens.

## 🚀 Tecnologías Utilizadas

- **Laravel 12.0** - Framework PHP
- **PHP 8.2+** - Lenguaje de programación
- **Laravel Sanctum 4.2** - Autenticación API con tokens
- **MySQL/PostgreSQL/SQLite** - Base de datos (configurable)

## 📦 Instalación

1. **Requisitos previos**:
   - PHP 8.2 o superior
   - Composer
   - Base de datos (MySQL, PostgreSQL o SQLite)

2. **Clonar el repositorio** (si aplica) o navegar al directorio:
```bash
cd controlgastos
```

3. **Instalar dependencias**:
```bash
composer install
```

4. **Configurar variables de entorno**:
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurar base de datos en `.env`**:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=controlgastos
DB_USERNAME=root
DB_PASSWORD=
```

6. **Ejecutar migraciones**:
```bash
php artisan migrate
```

7. **Publicar configuración de Sanctum** (si es necesario):
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

8. **Iniciar el servidor**:
```bash
php artisan serve
```

La API estará disponible en `http://localhost:8000`.

## 🏗️ Estructura del Proyecto

```
app/
├── Http/
│   └── Controllers/
│       ├── AuthController.php              # Autenticación (register, login)
│       ├── CategoryController.php          # CRUD de categorías
│       ├── SavingsFundController.php       # CRUD de fondos de ahorro
│       ├── SavingsTransactionController.php # CRUD de transacciones de ahorro
│       └── TransactionController.php       # CRUD de transacciones generales
├── Models/
│   ├── Category.php                        # Modelo de categorías
│   ├── SavingsFund.php                    # Modelo de fondos de ahorro
│   ├── SavingsTransaction.php              # Modelo de transacciones de ahorro
│   ├── Transaction.php                    # Modelo de transacciones
│   └── User.php                           # Modelo de usuarios (con HasApiTokens)
database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── *_create_personal_access_tokens_table.php  # Sanctum
│   ├── *_create_savings_funds_table.php           # Fondos de ahorro
│   ├── *_create_transactions_table.php            # Transacciones generales
│   ├── *_create_savings_transactions_table.php     # Transacciones de ahorro
│   └── *_create_categories_table.php               # Categorías
routes/
└── api.php                                    # Rutas de la API
```

## 🔐 Autenticación

La API utiliza **Laravel Sanctum** para autenticación basada en tokens:

1. **Registro/Login**: El usuario obtiene un token
2. **Token**: Se envía en el header `Authorization: Bearer {token}`
3. **Middleware**: `auth:sanctum` protege las rutas que requieren autenticación
4. **Validación**: El middleware valida el token en cada petición

## 📡 Endpoints de la API

### Base URL
```
http://localhost:8000/api
```

### Endpoints Públicos (Sin autenticación)

#### Health Check
```
GET /api/health
```
Verifica que el backend esté funcionando.

**Respuesta:**
```json
{
  "status": "success",
  "message": "Backend funcionando correctamente"
}
```

#### Registro de Usuario
```
POST /api/register
```

**Body:**
```json
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Respuesta:**
```json
{
  "status": "success",
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com"
    },
    "token": "1|xxxxxxxxxxxxx"
  }
}
```

#### Inicio de Sesión
```
POST /api/login
```

**Body:**
```json
{
  "email": "juan@example.com",
  "password": "password123"
}
```

**Respuesta:**
```json
{
  "status": "success",
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com"
    },
    "token": "1|xxxxxxxxxxxxx"
  }
}
```

### Endpoints Protegidos (Requieren autenticación)

Todas las siguientes rutas requieren el header:
```
Authorization: Bearer {token}
```

#### Fondos de Ahorro

**Obtener todos los fondos:**
```
GET /api/savings-funds
```

**Crear fondo:**
```
POST /api/savings-funds
```
**Body:**
```json
{
  "name": "Vacaciones",
  "description": "Ahorro para vacaciones",
  "color": "#3b82f6"
}
```

**Actualizar fondo:**
```
POST /api/savings-funds/update
```
**Body:**
```json
{
  "id": 1,
  "name": "Vacaciones 2025",
  "description": "Nueva descripción",
  "color": "#10b981"
}
```

**Eliminar fondo:**
```
POST /api/savings-funds/delete
```
**Body:**
```json
{
  "id": 1
}
```
**Nota**: Solo se puede eliminar si el balance es 0.

#### Transacciones Generales

**Obtener todas las transacciones:**
```
GET /api/transactions
```

**Crear transacción:**
```
POST /api/transactions
```
**Body:**
```json
{
  "type": "expense",
  "amount": 150.50,
  "category": "Comida",
  "description": "Almuerzo",
  "date": "2025-01-15",
  "savings_fund_id": null
}
```

**Actualizar transacción:**
```
POST /api/transactions/update
```
**Body:**
```json
{
  "id": 1,
  "amount": 200.00,
  "category": "Transporte"
}
```

**Eliminar transacción:**
```
POST /api/transactions/delete
```
**Body:**
```json
{
  "id": 1
}
```

#### Transacciones de Ahorro

**Obtener todas las transacciones de ahorro:**
```
GET /api/savings-transactions
```

**Crear transacción de ahorro:**
```
POST /api/savings-transactions
```
**Body:**
```json
{
  "savings_fund_id": 1,
  "type": "deposit",
  "amount": 500.00,
  "description": "Depósito inicial",
  "date": "2025-01-15"
}
```
**Tipos**: `deposit` (depósito) o `withdrawal` (retiro)

**Nota**: Al crear una transacción de ahorro, el balance del fondo se actualiza automáticamente.

**Eliminar transacción de ahorro:**
```
POST /api/savings-transactions/delete
```
**Body:**
```json
{
  "id": 1
}
```
**Nota**: Al eliminar, el balance del fondo se revierte automáticamente.

#### Categorías

**Obtener todas las categorías:**
```
GET /api/categories
```

**Crear categoría:**
```
POST /api/categories
```
**Body:**
```json
{
  "name": "Entretenimiento",
  "color": "#f59e0b"
}
```

**Actualizar categoría:**
```
POST /api/categories/update
```
**Body:**
```json
{
  "id": 1,
  "name": "Ocio",
  "color": "#ec4899"
}
```

**Eliminar categoría:**
```
POST /api/categories/delete
```
**Body:**
```json
{
  "id": 1
}
```
**Nota**: No se puede eliminar si está en uso en alguna transacción.

## 🗄️ Estructura de Base de Datos

### Tabla: `users`
- `id` - ID único
- `name` - Nombre del usuario
- `email` - Email (único)
- `password` - Contraseña hasheada
- `created_at`, `updated_at` - Timestamps

### Tabla: `savings_funds`
- `id` - ID único
- `user_id` - Foreign key a users
- `name` - Nombre del fondo
- `description` - Descripción (nullable)
- `color` - Color en formato hexadecimal
- `balance` - Balance actual (decimal, default 0)
- `created_at`, `updated_at` - Timestamps

### Tabla: `transactions`
- `id` - ID único
- `user_id` - Foreign key a users
- `type` - Enum: 'expense' o 'income'
- `amount` - Monto (decimal)
- `category` - Nombre de la categoría
- `description` - Descripción (nullable)
- `date` - Fecha de la transacción
- `savings_fund_id` - Foreign key a savings_funds (nullable)
- `created_at`, `updated_at` - Timestamps

### Tabla: `savings_transactions`
- `id` - ID único
- `savings_fund_id` - Foreign key a savings_funds
- `user_id` - Foreign key a users
- `type` - Enum: 'deposit' o 'withdrawal'
- `amount` - Monto (decimal)
- `description` - Descripción (nullable)
- `date` - Fecha de la transacción
- `created_at`, `updated_at` - Timestamps

### Tabla: `categories`
- `id` - ID único
- `user_id` - Foreign key a users
- `name` - Nombre de la categoría
- `color` - Color en formato hexadecimal
- `created_at`, `updated_at` - Timestamps
- **Constraint único**: `(user_id, name)` - No puede haber categorías duplicadas por usuario

### Tabla: `personal_access_tokens` (Sanctum)
- Gestionada automáticamente por Laravel Sanctum para almacenar tokens de autenticación

## 🔒 Seguridad y Validaciones

### Validaciones Implementadas

1. **Autenticación**:
   - Todas las rutas protegidas verifican el token
   - El usuario solo puede acceder a sus propios datos

2. **Fondos de Ahorro**:
   - No se puede eliminar un fondo con balance > 0
   - El fondo debe pertenecer al usuario autenticado

3. **Transacciones de Ahorro**:
   - No se puede retirar más dinero del disponible en el fondo
   - El fondo debe pertenecer al usuario
   - Actualización automática del balance en transacciones de base de datos

4. **Categorías**:
   - No se puede crear una categoría con nombre duplicado (por usuario)
   - No se puede eliminar una categoría si está en uso

5. **Transacciones**:
   - Validación de tipos (expense/income)
   - Validación de montos (debe ser > 0)
   - Validación de fechas

## 🔄 Relaciones de Modelos

### User
- `hasMany` SavingsFund
- `hasMany` Transaction
- `hasMany` SavingsTransaction
- `hasMany` Category
- `HasApiTokens` (trait de Sanctum)

### SavingsFund
- `belongsTo` User
- `hasMany` SavingsTransaction

### Transaction
- `belongsTo` User
- `belongsTo` SavingsFund (nullable)

### SavingsTransaction
- `belongsTo` SavingsFund
- `belongsTo` User

### Category
- `belongsTo` User

## 🛠️ Comandos Útiles

```bash
# Ejecutar migraciones
php artisan migrate

# Crear nueva migración
php artisan make:migration create_table_name

# Crear nuevo controlador
php artisan make:controller ControllerName

# Crear nuevo modelo
php artisan make:model ModelName

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rutas registradas
php artisan route:list
```

## 📝 Respuestas de Error

Todas las respuestas de error siguen este formato:

```json
{
  "status": "error",
  "message": "Mensaje de error descriptivo",
  "errors": {
    "campo": ["Error de validación específico"]
  }
}
```

**Códigos de estado HTTP:**
- `200` - Éxito
- `201` - Creado exitosamente
- `404` - No encontrado
- `422` - Error de validación
- `500` - Error del servidor

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test
```

## 📄 Licencia

Este proyecto es privado y de uso personal.

## 👨‍💻 Autor

Desarrollado para control y gestión de finanzas personales.

## 🔗 Integración con Frontend

Este backend está diseñado para trabajar con el frontend React ubicado en:
```
../React/controlgastos
```

El frontend debe configurar la variable `VITE_API_URL` apuntando a esta API.
