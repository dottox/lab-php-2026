# README.md — FASE 2 Services Module

## 🎯 Objetivo

La FASE 2 introduce el módulo de servicios reservables dentro de ProConnect.

Permitiendo que un profesional:

* publique servicios
* configure duración
* defina modalidad
* establezca precios
* configure buffers
* limite reservas
* prepare disponibilidad futura

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
Policies + Gate Authorization
```

---

# Estructura del módulo

```text
app/
├── Actions/
│   └── Service/
│       ├── DeleteServiceAction.php
│       ├── ListMyServicesAction.php
│       ├── ShowServiceAction.php
│       ├── StoreServiceAction.php
│       └── UpdateServiceAction.php
│
├── Http/
│   ├── Controllers/
│   │   └── Service/
│   │       └── ServiceController.php
│   │
│   ├── Requests/
│   │   └── Service/
│   │       ├── StoreServiceRequest.php
│   │       └── UpdateServiceRequest.php
│   │
│   └── Resources/
│       └── Service/
│           └── ServiceResource.php
│
├── Models/
│   └── Service/
│       └── Service.php
│
└── Policies/
    └── ServicePolicy.php
```

---

# Modelo de dominio

## Service

Representa un servicio reservable publicado por un profesional.

Ejemplos:

* Consultoría
* Coaching
* Mentoría
* Entrenamiento
* Psicoterapia
* Asesoría técnica

---

# Relaciones

```text
ProfessionalProfile
→ hasMany Services
```

```text
Service
→ belongsTo ProfessionalProfile
```

---

# Base de datos

## Tabla

```text
services
```

---

# Campos implementados

```text
id
professional_id
company_id
name
description
price
duration_minutes
modality
address
link
latitude
longitude
max_bookings_per_client
min_reschedule_minutes
buffer_minutes
starts_at
ends_at
is_active
created_at
updated_at
deleted_at
```

---

# Diseño importante

## UUID

Todos los servicios utilizan UUID.

Ventajas:

* seguridad
* URLs públicas
* APIs desacopladas
* mobile-first
* realtime
* escalabilidad

---

## Soft Deletes

```php
use SoftDeletes;
```

Permite preservar:

* bookings históricos
* analytics
* pagos
* estadísticas

---

# Modalidades

El sistema soporta:

```text
presencial
remota
hibrida
```

---

# Diseño de modalidades

## presencial

Utiliza:

```text
address
latitude
longitude
```

---

## remota

Utiliza:

```text
link
```

---

## hibrida

Utiliza ambos:

```text
address
+
link
```

---

# Buffers

## buffer_minutes

Configuración crítica para agenda.

Permite:

* descansos
* preparación
* traslados
* evitar reservas pegadas

---

# Reagendamiento

## min_reschedule_minutes

Controla cuánto tiempo antes puede reagendarse una reserva.

---

# Endpoints implementados

Todos requieren:

```text
auth:user_jwt
```

---

# Crear servicio

```http
POST /api/v1/services
```

### Body

```json
{
  "name": "Consultoría inicial",
  "description": "Primera sesión de diagnóstico.",
  "price": 1500,
  "duration_minutes": 60,
  "modality": "remota",
  "link": "https://meet.example.com/test",
  "buffer_minutes": 15,
  "min_reschedule_minutes": 10,
  "is_active": true
}
```

---

# Listar mis servicios

```http
GET /api/v1/services/my
```

---

# Ver servicio

```http
GET /api/v1/services/{service}
```

---

# Actualizar servicio

```http
PUT /api/v1/services/{service}
```

---

# Eliminar servicio

```http
DELETE /api/v1/services/{service}
```

---

# Arquitectura utilizada

## Actions Pattern

Separación:

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
* escalabilidad
* mantenibilidad
* testabilidad

---

# Authorization

## Policies + Gate

Se utiliza:

```php
Gate::authorize(...)
```

junto con:

```php
ServicePolicy
```

---

# Ownership

Cada profesional solo puede:

* ver sus servicios
* editar sus servicios
* eliminar sus servicios

---

# Ejemplo

## Controller

```php
Gate::authorize('update', $service);
```

---

## Policy

```php
public function update(User $user, Service $service): bool
{
    return $user->professionalProfile?->id
        === $service->professional_id;
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
* desacoplar frontend/backend

---

# Validaciones importantes

## duration_minutes

Valores permitidos:

```text
15
30
45
60
90
120
```

---

## price

```text
>= 0
```

---

## coordinates

```text
latitude  -90 → 90
longitude -180 → 180
```

---

## modality

```text
presencial
remota
hibrida
```

---

# Estado actual

## ✅ Completado

```text
Service model
Migration
Relationships
Requests
Actions
Resources
Controller
Routes
Policies
Gate authorization
CRUD privado
Ownership validation
```

---

# Resultado final

La plataforma ahora soporta:

```text
usuarios profesionales
→ servicios reservables
→ pricing
→ modalidades
→ buffers
→ ownership
→ base para availability
```

---

# Próxima fase

```text
FASE 3 — Availability Engine
```

El motor de disponibilidad que calculará:

* slots
* horarios
* buffers
* excepciones
* disponibilidad real
