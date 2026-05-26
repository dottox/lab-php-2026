# PROCONNECT — Roadmap SaaS Core

---

# Estado actual

## ✅ FASE 0 — Infraestructura Base

Completado:

* Laravel 13
* Docker Compose
* PostgreSQL
* Redis
* Mailpit
* JWT Authentication
* Refresh Tokens
* Arquitectura Actions Pattern
* Form Requests
* Json Resources
* Base API REST
* User module
* Auth module
* `/auth/register`
* `/auth/login`
* `/auth/refresh`
* `/auth/logout`
* `/me`

---

# Objetivo actual

Entrar en la primera etapa REAL del dominio SaaS:

```text
Profesionales
→ Servicios
→ Disponibilidad
→ Reservas
```

---

# Roadmap — SaaS Core

---

# FASE 1 — Professional Profiles

## Objetivo

Permitir que un usuario se convierta en profesional dentro de la plataforma.

---

## Entidad principal

```text
professional_profiles
```

---

## Campos iniciales

```text
id
user_id
slug
headline
bio
avatar_url
timezone
country
city
address
website
languages
experience_years
is_public
created_at
updated_at
```

---

# Relaciones

```text
User
→ hasOne ProfessionalProfile
```

---

# Endpoints

## Públicos

```txt
GET /professionals
GET /professionals/{slug}
```

## Privados

```txt
POST   /professional-profile
GET    /professional-profile
PUT    /professional-profile
DELETE /professional-profile
```

---

# Reglas importantes

## 1. Slug único

```txt
jose-hernandez
maria-gonzalez
```

---

## 2. Perfil opcional

No todos los usuarios serán profesionales.

---

## 3. Soft delete recomendado

Para preservar bookings históricos.

---

# UX importante

## Mobile first

El profesional debe poder:

* crear perfil desde celular
* subir avatar
* editar bio
* configurar modalidad
* activar/desactivar visibilidad

---

# Resultado esperado

```txt
Profesionales públicos navegables
```

---

# FASE 2 — Services

## Objetivo

Permitir que profesionales publiquen servicios reservables.

---

# Entidad principal

```text
services
```

---

# Campos iniciales

```text
id
professional_profile_id
name
slug
description
duration_minutes
price
currency
modality
buffer_before_minutes
buffer_after_minutes
max_daily_bookings
is_active
is_public
created_at
updated_at
```

---

# Modalidades

```txt
REMOTE
PRESENTIAL
HYBRID
```

---

# Relaciones

```text
ProfessionalProfile
→ hasMany Services
```

---

# Endpoints

## Públicos

```txt
GET /services
GET /services/{slug}
```

## Privados

```txt
POST   /services
GET    /services/my
PUT    /services/{service}
DELETE /services/{service}
```

---

# Reglas importantes

## 1. Duración configurable

```txt
15
30
45
60
90
120
```

---

## 2. Buffers

Evitar reservas pegadas.

```txt
buffer_before
buffer_after
```

---

## 3. Visibilidad

Servicio puede estar:

```txt
draft
active
hidden
```

---

# UX importante

El profesional debe poder:

* crear servicio en menos de 1 minuto
* duplicar servicios
* activar/desactivar
* editar precio rápido

---

# Resultado esperado

```txt
Marketplace inicial de servicios
```

---

# FASE 3 — Availability Engine

## Objetivo

Construir el motor de disponibilidad.

---

# IMPORTANTE

Esta es probablemente la fase MÁS importante del proyecto.

Porque availability define:

```txt
qué slots existen
```

Y bookings depende completamente de esto.

---

# Entidades principales

```text
availability_rules
availability_exceptions
blocked_slots
```

---

# availability_rules

## Campos

```text
id
professional_profile_id
weekday
start_time
end_time
is_active
```

---

# availability_exceptions

## Campos

```text
id
professional_profile_id
date
start_time
end_time
reason
```

---

# blocked_slots

Bloqueos manuales.

---

# Reglas importantes

## 1. Timezone-aware

CRÍTICO.

Toda disponibilidad debe calcularse usando timezone del profesional.

---

## 2. Buffers reales

Debe considerar:

```txt
service duration
+
buffer_before
+
buffer_after
```

---

## 3. Excepciones

Soportar:

* vacaciones
* feriados
* pausas
* enfermedad
* eventos especiales

---

## 4. Slots dinámicos

NO guardar slots físicos.

Los slots deben calcularse dinámicamente.

---

# Endpoint crítico

```txt
GET /services/{service}/availability
```

---

# Response esperada

```json
{
  "date": "2026-05-20",
  "slots": [
    "09:00",
    "10:30",
    "14:00"
  ]
}
```

---

# UX importante

La agenda debe sentirse:

```txt
instantánea
fluida
moderna
tipo Calendly
```

---

# Resultado esperado

```txt
Motor real de agenda
```

---

# FASE 4 — Bookings

## Objetivo

Permitir reservas reales.

---

# Entidad principal

```text
bookings
```

---

# Campos iniciales

```text
id
service_id
professional_profile_id
client_user_id
starts_at
ends_at
status
notes
cancel_reason
confirmed_at
cancelled_at
completed_at
```

---

# Estados

```txt
PENDING
CONFIRMED
PAID
COMPLETED
CANCELLED
NO_SHOW
```

---

# IMPORTANTE

Esta fase introduce:

```txt
CONCURRENCIA
```

---

# Problema crítico

Evitar:

```txt
doble reserva
```

---

# Reglas obligatorias

## 1. Transactions

```php
DB::transaction(...)
```

---

## 2. lockForUpdate()

```php
->lockForUpdate()
```

---

## 3. Validación atómica

Nunca:

```php
if ($available) {
   create booking
}
```

---

# Endpoint principal

```txt
POST /bookings
```

---

# Flujo esperado

```text
cliente
→ selecciona servicio
→ consulta disponibilidad
→ elige slot
→ crea booking
→ profesional confirma
→ booking queda reservado
```

---

# Endpoints

```txt
POST   /bookings
GET    /bookings/my
GET    /bookings/{booking}
PATCH  /bookings/{booking}/confirm
PATCH  /bookings/{booking}/cancel
PATCH  /bookings/{booking}/complete
```

---

# UX importante

La reserva debe sentirse:

```txt
rápida
simple
sin fricción
mobile-first
```

---

# Resultado esperado

```txt
MVP SaaS funcional tipo Calendly
```

---

# Después de estas 4 fases

Vendría:

```text
Payments
Packages
Reviews
Notifications
Realtime
Video Calls
Admin Panel
Analytics
```

---

# Prioridad real

```text
1. Professional Profiles
2. Services
3. Availability
4. Bookings
```

NO al revés.

Porque bookings sin availability bien diseñado:

```text
rompe todo el sistema
```
