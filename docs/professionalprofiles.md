# README.md — FASE 1 Professional Profiles

## 🎯 Objetivo

La FASE 1 introduce el primer módulo real del dominio SaaS:

```text
Professional Profiles
```

Permitiendo que un usuario autenticado:

* se convierta en profesional
* tenga perfil profesional propio
* pueda publicar servicios posteriormente
* participe del marketplace
* tenga identidad pública dentro de la plataforma

---

# Arquitectura implementada

## Stack

```text
Laravel 13
PHP 8.4+
PostgreSQL
Docker Compose
JWT Authentication
Redis
```

---

# Estructura del módulo

```text
app/
├── Actions/
│   └── ProfessionalProfile/
│       ├── ShowProfessionalProfileAction.php
│       ├── StoreProfessionalProfileAction.php
│       └── UpdateProfessionalProfileAction.php
│
├── Http/
│   ├── Controllers/
│   │   └── ProfessionalProfile/
│   │       └── ProfessionalProfileController.php
│   │
│   ├── Requests/
│   │   └── ProfessionalProfile/
│   │       ├── StoreProfessionalProfileRequest.php
│   │       └── UpdateProfessionalProfileRequest.php
│   │
│   └── Resources/
│       └── ProfessionalProfile/
│           └── ProfessionalProfileResource.php
│
├── Models/
│   └── User/
│       └── ProfessionalProfile.php
│
└── Policies/
    └── ProfessionalProfilePolicy.php
```

---

# Modelo de dominio

## ProfessionalProfile

Representa la identidad profesional pública de un usuario.

---

# Relación principal

```text
User
→ hasOne ProfessionalProfile
```

```text
ProfessionalProfile
→ belongsTo User
```

---

# Base de datos

## Tabla

```text
professional_profiles
```

---

# Campos implementados

```text
id
user_id
bio
avg_rating
reviews_count
is_verified
created_at
updated_at
deleted_at
```

---

# Diseño importante

## UUID

Se utilizan UUIDs desde el inicio para:

* APIs públicas
* seguridad
* frontend desacoplado
* escalabilidad
* realtime
* mobile

---

## Soft Deletes

```php
use SoftDeletes;
```

Evita romper:

* bookings históricos
* reviews
* analytics
* pagos

---

# Endpoints implementados

## Privados

Todos requieren:

```text
auth:user_jwt
```

---

## Crear perfil profesional

```http
POST /api/v1/professional-profile
```

### Body

```json
{
  "bio": "Coach profesional especializado en liderazgo."
}
```

---

## Obtener perfil profesional

```http
GET /api/v1/professional-profile
```

---

## Actualizar perfil profesional

```http
PUT /api/v1/professional-profile
```

### Body

```json
{
  "bio": "Nuevo texto profesional."
}
```

---

# Arquitectura utilizada

## Actions Pattern

Se utiliza separación:

```text
Controller
→ Action
→ Model
```

---

# Objetivo

Mantener:

* controllers delgados
* lógica encapsulada
* testabilidad
* escalabilidad

---

# Ejemplo

## Controller

```php
public function update(
    UpdateProfessionalProfileRequest $request,
    UpdateProfessionalProfileAction $action
): JsonResponse {
    $profile = $action($request);

    return response()->json([
        'professional_profile' => new ProfessionalProfileResource($profile),
    ]);
}
```

---

## Action

```php
public function __invoke(
    UpdateProfessionalProfileRequest $request
): ProfessionalProfile {

    $profile = ProfessionalProfile::query()
        ->where('user_id', Auth::id())
        ->firstOrFail();

    $profile->update(
        $request->validated()
    );

    return $profile->refresh();
}
```

---

# Requests

Se utilizan Form Requests para:

* validación
* sanitización
* separación de responsabilidades

---

# Resources

Se utilizan API Resources para:

* respuestas consistentes
* transformación de datos
* ocultar implementación interna

---

# Seguridad

## Ownership

Cada perfil profesional pertenece exclusivamente a un usuario.

---

# Middleware

```php
auth:user_jwt
```

Protege todos los endpoints privados.

---

# Próximas fases relacionadas

Después de Professional Profiles:

```text
FASE 2 — Services
FASE 3 — Availability Engine
FASE 4 — Bookings
```

---

# Estado actual

## ✅ Completado

```text
ProfessionalProfile model
Migration
Relationships
Requests
Actions
Resources
Controller
Routes
JWT protection
CRUD básico
```

---

# Resultado final

La plataforma ahora soporta:

```text
usuarios autenticados
→ perfiles profesionales
→ identidad SaaS pública
→ base para servicios y reservas
```

---

# Próximo objetivo

```text
Services Module
```

Permitir que profesionales publiquen servicios reservables.
