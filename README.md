# Decision Data · Panorama

Prueba técnica final — proceso de selección de desarrolladores 2026 (Decision Data / Telconet).

> **El buró deja de solo informarte y empieza a gestionar por ti.**
> Cuando aparece en tu huella de consulta una consulta que no reconoces, la mayoría de personas
> no sabe qué hacer con eso. Panorama abre un caso, arranca el reloj legal, pide tu autorización
> y gestiona la oposición LOPDP por ti — con un agente que solo puede actuar dentro de límites
> explícitos y nunca sin tu consentimiento firmado.

Ver el brief completo de producto y arquitectura en [`docs/BRIEF-decision-data.md`](docs/BRIEF-decision-data.md)
(documento de trabajo interno, no forma parte del enunciado original de Decision Data).

## Recorrido implementado punta a punta

**Consulta no reconocida → oposición LOPDP**, con una única regla que sostiene todo:

> El humano autoriza, el agente ejecuta. Ninguna transición hacia `en_gestion` es posible sin un
> consentimiento firmado y vigente para ese caso y esa entidad específica (probado con tests).

```
detectado ──► notificado ──► autorizado ──► en_gestion ──┬──► resuelto
                                                          └──► escalado ──► resuelto
```

## Arquitectura

```
CANALES                    BACKEND LARAVEL                      DATOS Y EXTERNOS
─────────                  ───────────────                      ────────────────
Web (React SPA)  ────┐     ┌─────────────────────┐        ┌──► MySQL
                     ├────►│ Capa de API         │        │    (casos, eventos,
WhatsApp             │     │ auth · validación   │        │     consentimientos)
(Meta Cloud API) ────┘     │ rate limit · errores│        │
                           └──────────┬──────────┘        ├──► Redis
Voz (fase 2,                          │                   │    (colas, scheduler)
 no implementado)          ┌──────────▼──────────┐        │
                           │ MOTOR DE CASOS      │────────┤
                           │ máquina de estados  │        ├──► Claude API
                           │ reloj de plazos     │        │    (redacción del Gestor,
                           │ consentimiento      │        │     con stub si no hay key)
                           │ bitácora inmutable  │        │
                           └──────────┬──────────┘        └──► Meta Cloud API
                                      │                        (plantilla + webhook)
                           ┌──────────▼──────────┐
                           │ AGENTES             │
                           │ Centinela · Gestor  │
                           │ (Vocero → fase 2)   │
                           └─────────────────────┘
```

**Tres reglas de integración no negociables** (con tests que las prueban):

1. Laravel es el único dueño del estado. Ningún otro servicio escribe en MySQL directamente.
2. Las 6 herramientas del agente (`app/Http/Controllers/Agente/AgenteHerramientasController.php`)
   son endpoints de Laravel protegidos por un token de alcance limitado a un caso y expirable
   (`App\Models\AgenteToken` + `App\Http\Middleware\VerificarTokenAgente`).
3. La bitácora (`eventos`) y los documentos generados son append-only: el modelo rechaza
   `update`/`delete` a nivel de aplicación (`App\Domain\Casos\Exceptions\BitacoraInmutableException`).

### Dominio (`backend/app/Domain/Casos`)

| Pieza | Responsabilidad |
|---|---|
| `CasoEstado` | Enum + grafo de transiciones válidas |
| `CaseStateMachine` | Único punto autorizado a cambiar `casos.estado`; exige consentimiento vigente para `en_gestion` |
| `DeadlineClock` | Días hábiles (feriados de Ecuador configurables), cálculo y detección de vencimiento |
| `EscaladorDePlazos` | Escala a `escalado` todo caso `en_gestion` vencido (comando `casos:escalar-vencidos`) |
| `Agentes\Centinela` | Detecta consultas no reconocidas sin caso, abre el caso, notifica (comando `centinela:ejecutar`) |
| `Agentes\Gestor` | Redacta la oposición (vía `AgenteRedactorInterface`), la persiste, avanza el caso |

### Stack

| Capa | Elección |
|---|---|
| Frontend | React 19 + Vite, sin librería de UI: tokens de marca propios en CSS |
| Backend | Laravel 13, Sanctum para tokens de sesión |
| Base | MySQL 8 |
| Colas | Redis (`predis`, sin extensión nativa) — job `GestionarCasoJob` corre en el `worker` |
| Scheduler | `php artisan schedule:work` en un contenedor `scheduler` dedicado |
| Razonamiento | Claude API (Messages API) para la redacción del Gestor — con stub si no hay `ANTHROPIC_API_KEY` |
| Mensajería | Meta Cloud API, conmutable a `log` por variable de entorno |
| Empaque | Docker Compose: `mysql · redis · app · worker · scheduler · frontend` |

## Cómo correrlo

### Opción recomendada: Docker Compose

```bash
git clone <url-del-repo>
cd decision-data-b2c

cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

docker compose up --build
```

- Backend: http://localhost:8000 (healthcheck en `/up`)
- Frontend: http://localhost:5173
- El contenedor `app` genera `APP_KEY`, corre las migraciones y siembra los datos sintéticos
  automáticamente en su primer arranque (`backend/docker/entrypoint.sh`). Es idempotente: si
  reinicias los contenedores no vuelve a duplicar datos.

**Login de demo:** cédula sintética `1710034065` (Ana María Torres).

### Opción manual (sin Docker)

Requiere PHP 8.3+, Composer, Node 20+, y MySQL o SQLite.

```bash
cd backend
cp .env.example .env
# Para desarrollo rápido sin levantar MySQL/Redis, en .env usa:
#   DB_CONNECTION=sqlite, DB_DATABASE=(ruta absoluta a database/database.sqlite)
#   QUEUE_CONNECTION=sync, CACHE_STORE=file, SESSION_DRIVER=file
composer install
php artisan key:generate
touch database/database.sqlite   # si usas sqlite
php artisan migrate --seed

php artisan serve                 # backend en :8000
php artisan queue:work            # en otra terminal, si QUEUE_CONNECTION no es sync
php artisan schedule:work          # en otra terminal, para Centinela/Escalador automáticos
```

```bash
cd frontend
cp .env.example .env
npm install
npm run dev                       # :5173
```

## Pruebas

```bash
cd backend
php artisan test
```

37 tests, todos sobre el motor de casos y su superficie HTTP: transiciones inválidas, agente sin
consentimiento vigente, cálculo de días hábiles y vencimiento, escalamiento automático, bitácora
inmutable, validación de cédula ecuatoriana (dígito verificador módulo 10, no solo formato), y el
contrato completo de las 6 herramientas del agente con tokens de alcance y expiración.

```bash
cd frontend
npm run build                     # falla si hay errores de compilación/import
```

## Recorrido de demostración (con los datos sembrados)

1. **Login** con la cédula `1710034065`.
2. **Panorama**: verás 3 casos (Cooperativa JEP en `notificado`, Produbanco en `en_gestion` con
   plazo ya vencido, Banco Guayaquil `resuelto`) y una consulta de Banco del Austro sin caso
   todavía — a propósito, para la detección en vivo.
3. **Detección en vivo** (desde una terminal, dentro del contenedor `app` o local):
   ```bash
   php artisan centinela:ejecutar
   ```
   Refresca Panorama: aparece un cuarto caso para Banco del Austro en estado `notificado`.
4. **Autorizar**: entra al caso de Cooperativa JEP y firma. El estado pasa a `autorizado` de
   inmediato; unos segundos después (cuando el `worker` procesa `GestionarCasoJob`) pasa solo a
   `en_gestion` y aparece el documento de oposición generado.
5. **Escalamiento en vivo**: el caso de Produbanco ya tiene `vence_en` en el pasado.
   ```bash
   php artisan casos:escalar-vencidos
   ```
   Refresca ese caso: pasa a `escalado`.
6. **Revocar**: en cualquier caso con autorización vigente, el botón "Revocar" pide confirmación
   antes de ejecutar.
7. **Bitácora**: cada caso muestra su historial completo, append-only.

## Seguridad y privacidad

- La cédula nunca se guarda en claro: `personas.cedula_hash` es un hash (`Persona::hashCedula`).
- Login sin contraseña real: es una verificación de identidad **simulada** sobre datos sintéticos
  (documentado, no se presenta como un factor de autenticación real).
- Autorización por propietario en cada endpoint de caso/consentimiento (403 si no es el titular).
- Las 6 herramientas del agente exigen un `AgenteToken` con alcance a un caso específico, una
  habilidad concreta, y expiración — nunca acceso a la base ni a la API completa.
- `eventos` y `documentos` son append-only a nivel de modelo (Eloquent lanza excepción ante
  `update`/`delete`).
- CORS, rate limiting (`throttle:60,1`) en las rutas de agente, y validación de entradas en todos
  los formularios (incluida la cédula con dígito verificador real, no solo longitud).

## Limitaciones conocidas y próximos pasos

- Solo se implementa un tipo de caso: consulta no reconocida → oposición LOPDP. Los demás quedan
  definidos en el modelo (`casos.tipo`) sin implementar.
- El agente **Vocero** (llamadas telefónicas a la entidad) es **fase 2, no implementado**. Existe
  solo como contrato de integración en el diagrama de arquitectura.
- El envío a la entidad reportante y a la Superintendencia se registra como documento generado;
  no hay integración real con sus canales porque no son públicos.
- La verificación de identidad es simulada sobre datos sintéticos (sin OTP ni biometría real).
- El **Gestor** y el **Centinela** actuales orquestan las 6 herramientas del agente **en proceso**
  (llamadas directas a los mismos servicios de dominio), no todavía a través de un loop agéntico
  real de Claude con tool use por HTTP contra `/api/agente/*`. El contrato HTTP con token de
  alcance limitado sí está completo, probado de forma independiente
  (`tests/Feature/AgenteHerramientasTest.php`), y es exactamente lo que un loop de Claude con tool
  use llamaría. Conectar ese loop es el siguiente paso natural: mismo contrato, sin cambiar el
  dominio.
- La redacción de la oposición usa `RedactorStub` cuando no hay `ANTHROPIC_API_KEY` configurada
  (ver `AI_USAGE.md`). Con la key puesta, `ClaudeRedactor` llama a la Claude API de verdad.
- El plazo de respuesta (`CASOS_DIAS_HABILES_RESPUESTA`, default 15 días hábiles) es un valor
  razonable asumido, no un plazo legal confirmado por Decision Data — ajustable por variable de
  entorno.
- El logo (`frontend/src/assets/marca/dd-lockup-white.png`) se descargó directamente de
  `decisiondata.ec` y se usa sin modificar, tal como pide el enunciado.
- Sin tests end-to-end de frontend (Playwright/Cypress) por tiempo; sí hay 37 tests de backend
  sobre el motor, que es donde está el riesgo real.
- Para producción: mover `NOTIFICATION_DRIVER` a `whatsapp` con plantilla aprobada por Meta,
  agregar un usuario de base de datos sin permisos de `UPDATE`/`DELETE` sobre `eventos` y
  `documentos` (hoy la inmutabilidad se aplica solo a nivel de aplicación), y mover el cálculo de
  feriados de Ecuador a una fuente mantenida en vez de la lista fija en `config/casos.php`.
