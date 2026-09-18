# Decision Data · Panorama

Prueba técnica final — proceso de selección de desarrolladores 2026 (Decision Data / Telconet).

> **El buró deja de solo informarte y empieza a gestionar por ti.**
> Cuando aparece en tu huella de consulta una consulta que no reconoces, la mayoría de personas
> no sabe qué hacer con eso. Panorama abre un caso, arranca el reloj legal, pide tu autorización
> y gestiona la oposición LOPDP por ti — con un agente que solo puede actuar dentro de límites
> explícitos y nunca sin tu consentimiento firmado.

Ver el brief completo de producto y arquitectura en [`docs/BRIEF-decision-data.md`](docs/BRIEF-decision-data.md)
(documento de trabajo interno, no forma parte del enunciado original de Decision Data).

**Demo pública:** https://demo.sumpasports.com — el mismo stack de Docker Compose de abajo, expuesto
por un Cloudflare Tunnel (conexión saliente desde el servidor; ningún puerto abierto en el router ni
en el firewall). Cédula de prueba: `1710034065`. Es un servidor personal, no una infraestructura
gestionada — la fuente de verdad y lo que hay que poder correr sin depender de esta URL sigue siendo
`docker compose up` en cualquier máquina, como pide el enunciado.

## Flujo de ramas

| Rama | Para qué |
|---|---|
| `adrian` | Desarrollo día a día. Todo commit nuevo nace aquí. |
| `developer` | Integración: recibe merges de `adrian` para verificar que todo corre junto (tests, build, Docker) antes de tocar producción. |
| `produccion` | **Rama principal.** Solo lo ya verificado en `developer`. Es la que se etiqueta con el tag de entrega y la que se reporta como "rama principal" en el correo final. |

## Recorrido implementado punta a punta

**Consulta no reconocida → oposición LOPDP**, con una única regla que sostiene todo:

> El humano autoriza, el agente ejecuta. Ninguna transición hacia `en_gestion` es posible sin un
> consentimiento firmado y vigente para ese caso y esa entidad específica (probado con tests).

```
detectado ──► notificado ──┬──► autorizado ──► en_gestion ──┬──► resuelto
                            │                                └──► escalado ──► resuelto
                            └──► descartado   ("sí, fui yo" — sin consentimiento, sin Gestor)
```

`descartado` es la rama corta y, en la práctica, la más frecuente: la mayoría de las consultas se
reconocen. El caso de oposición (el resto del diagrama) es la excepción que justifica que el motor
exista, no el camino más transitado. Ver `UC-01`/`UC-03` en el catálogo de casos de uso.

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

### Capas del backend

El backend sigue una separación explícita en cuatro capas, para que cada archivo tenga una sola
responsabilidad y el proyecto se pueda hacer crecer sin que los controladores se vuelvan un cajón
de sastre:

```
Http/Controllers   → delgados: reciben el Request ya validado, llaman UN servicio, devuelven UN Resource
Http/Requests      → toda la validación de entrada y las reglas de autorización (Policies)
Services/<módulo>  → orquestan un caso de uso completo (ej. firmar consentimiento = crear
                      registro + avisar al motor + encolar el job); un archivo por módulo
Domain/Casos       → el motor: reglas de negocio puras (máquina de estados, plazos, agentes),
                      sin saber nada de HTTP ni de Eloquent más allá de los modelos
Http/Resources     → la forma exacta que ve el frontend, desacoplada de las columnas de la tabla
```

Ejemplo concreto — firmar un consentimiento:

```
POST /api/casos/{caso}/consentimiento
  → FirmarConsentimientoRequest   (autoriza vía CasoPolicy::autorizar)
  → ConsentimientoController::firmar()   (5 líneas: llama al servicio, traduce excepciones)
  → ConsentimientoService::firmar()      (el caso de uso completo)
  → CaseStateMachine::transicionar()     (la regla de negocio: sin consentimiento no hay en_gestion)
  → ConsentimientoResource                (la respuesta)
```

Las 6 herramientas del agente siguen el mismo patrón a través de `HerramientasAgenteService`, con
un Form Request por herramienta (`app/Http/Requests/Agente/`) y Resources para `Caso`,
`Documento`, `Evento` y `Consentimiento`.

Autorización: `CasoPolicy` y `ConsentimientoPolicy` (Laravel Policies estándar) deciden si la
persona autenticada puede ver o actuar sobre un caso/consentimiento — se invocan desde los propios
Form Requests (`$this->user()->can(...)`), no desde código repetido en cada controlador.

### Dominio (`backend/app/Domain/Casos`)

| Pieza | Responsabilidad |
|---|---|
| `CasoEstado` | Enum + grafo de transiciones válidas |
| `CaseStateMachine` | Único punto autorizado a cambiar `casos.estado`; exige consentimiento vigente para `en_gestion` |
| `DeadlineClock` | Días hábiles (feriados de Ecuador configurables), cálculo y detección de vencimiento |
| `EscaladorDePlazos` | Escala a `escalado` todo caso `en_gestion` vencido (comando `casos:escalar-vencidos`) |
| `Agentes\Centinela` | Detecta consultas no reconocidas sin caso, abre el caso, notifica (comando `centinela:ejecutar`) |
| `Agentes\Gestor` | Redacta la oposición (vía `AgenteRedactorInterface`), la persiste, avanza el caso |

### La IA visible del producto: el Gestor con contexto de la persona

La única función de IA que se presenta como funcional (no simulada) es la redacción de la
oposición. Al firmar, la persona puede agregar un texto libre ("contanos qué recordás de esa
fecha") que se guarda en `consentimientos.contexto` y entra directo al prompt de
`ClaudeRedactor`/`RedactorStub`: el documento generado cita ese texto como argumento de hecho, en
vez de repetir solo los datos que ya traía el caso. El prompt incluye una guarda explícita contra
inventar fechas, montos o lugares que la persona no haya dicho (ver punto 16 de `AI_USAGE.md`). Es
demostrable en vivo: el mismo tipo de caso, firmado con y sin ese campo, produce dos documentos
distintos.

Por la misma razón (explicar en vez de solo mostrar) `PanoramaService::scoreDeContexto()` no
devuelve un número suelto: devuelve el total más un desglose de 5 factores con peso y dirección,
leyendo señales reales de la persona (consultas sin reconocer, antigüedad verificada) — la
respuesta a "¿por qué no aplico?" que la entidad nunca da.

### Modelo de datos: `entidades` normalizada

El diagrama del [brief](docs/BRIEF-decision-data.md) (documento de planificación, anterior al
desarrollo) muestra `consultas.entidad_nombre` como texto libre. En la implementación final se
extrajo a su propia tabla:

```
entidades              consultas
────────               ─────────
id                     id
nombre (único)         persona_id  ──► personas.id
tipo                   entidad_id  ──► entidades.id
email_contacto         motivo
                       consultada_en
                       reconocida
```

Evita repetir el nombre de cada banco/cooperativa en cada fila, da un lugar natural para datos que
un caso real necesitaría de la entidad (a quién dirigir la oposición, con qué correo) y protege la
integridad referencial (`restrictOnDelete`: no se puede borrar una entidad con consultas asociadas).
El resto del dominio (`Centinela`, `Gestor`, los redactores, los tests) sigue leyendo
`$consulta->entidad_nombre` sin cambios — es un accessor en el modelo `Consulta` que resuelve
`$this->entidad->nombre`, así que el cambio de esquema no se filtró a docenas de archivos.

### Capas del frontend

Mismo principio que el backend: las páginas no llaman a `fetch` directamente.

```
pages/        → solo presentación: usan un hook y pintan el resultado
hooks/        → un hook por pantalla (usePanorama, useCaso, useCasos, useHuella):
                 estado, polling en vivo, acciones (firmar/revocar)
api/<módulo>  → una función por endpoint (auth.js, panorama.js, casos.js,
                 consentimientos.js), todas sobre el mismo cliente
api/client.js → el único lugar que sabe hacer fetch, manejar el token y
                 traducir errores HTTP a los tres estados de la interfaz
utils/        → helpers puros sin estado (fechas, formateo de texto)
```

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

49 tests, todos sobre el motor de casos y su superficie HTTP: transiciones inválidas, agente sin
consentimiento vigente, cálculo de días hábiles y vencimiento, escalamiento automático, bitácora
inmutable, validación de cédula ecuatoriana (dígito verificador módulo 10, no solo formato), el
contrato completo de las 6 herramientas del agente con tokens de alcance y expiración, que el
contexto que escribe la persona se cita en el documento generado, el desglose de factores del
score, y el camino corto de reconocer una consulta (`descartado`).

```bash
cd frontend
npm run build                     # falla si hay errores de compilación/import
```

## Recorrido de demostración (con los datos sembrados)

> ⚠️ **Antes de presentar:** el contenedor `scheduler` corre `centinela:ejecutar` cada 5 minutos y
> `casos:escalar-vencidos` cada hora de verdad (no es decoración). Si lo dejas corriendo un rato
> antes de la demo, va a adelantarse solo a los pasos 3 y 5 de abajo. Justo antes de presentar:
> ```bash
> docker compose stop scheduler
> docker compose exec app php artisan migrate:fresh --seed
> ```
> y corre esos dos comandos tú mismo, en el momento del guión, con `docker compose exec app`.

1. **Login** con la cédula `1710034065`.
2. **Panorama**: verás 3 casos (Cooperativa JEP en `notificado`, Produbanco en `en_gestion` con
   plazo ya vencido, Banco Guayaquil `resuelto`) y una consulta de Banco del Austro sin caso
   todavía — a propósito, para la detección en vivo.
3. **Detección en vivo** (desde una terminal, dentro del contenedor `app` o local):
   ```bash
   php artisan centinela:ejecutar
   ```
   Refresca Panorama: aparece un cuarto caso para Banco del Austro en estado `notificado`.
4. **El camino frecuente — reconocer**: entra al caso de Banco del Austro. Ahí el sistema pregunta
   "¿la reconocés?", no "autorizá esto". Click en **"Sí, fui yo"**: dos clics y el caso se cierra
   como `descartado`, sin consentimiento ni Gestor de por medio — porque no hay nada que gestionar.
   Es el camino que toma la mayoría de las consultas reales; el que sigue es la excepción.
5. **El camino de disputa — autorizar con contexto**: entra al caso de Cooperativa JEP, click en
   **"Yo no autoricé esto"**, escribe algo en "contanos qué recordás de esa fecha" (ej. *"yo nunca
   estuve en Cuenca, ni he pedido crédito en esa cooperativa"*) y firma. El estado pasa a
   `autorizado` de inmediato; unos segundos después (cuando el `worker` procesa `GestionarCasoJob`)
   pasa solo a `en_gestion` y el documento generado cita ese texto textualmente.
6. **Escalamiento en vivo**: el caso de Produbanco ya tiene `vence_en` en el pasado.
   ```bash
   php artisan casos:escalar-vencidos
   ```
   Refresca ese caso: pasa a `escalado`.
7. **Revocar**: en cualquier caso con autorización vigente, el botón "Revocar" pide confirmación
   antes de ejecutar.
8. **Bitácora**: cada caso muestra su historial completo, append-only.

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
- `APP_KEY` viene fija en `.env.example` (no vacía): `app`, `worker` y `scheduler` son contenedores
  separados que comparten la misma base, y `Persona::hashCedula()` usa esta key como sal del hash —
  si cada contenedor generara la suya al arrancar, un hash calculado por uno no coincidiría con el
  de otro. Como todos los datos son sintéticos, fijarla en el repo es un trade-off aceptado a
  propósito; en producción real sería un secreto inyectado, nunca commiteado.

## Limitaciones conocidas y próximos pasos

- **`consultas.reconocida` solo tiene pantalla real una vez que ya hay un caso abierto.** Es un
  booleano **nullable de 3 estados**: `null` = nadie la revisó todavía (así nace toda consulta
  nueva), `true` = la persona la reconoce, `false` = la persona confirmó que no. El Centinela
  (`where('reconocida', false)`) solo actúa sobre ese tercer estado explícito, nunca sobre `null` —
  una consulta recién llegada no dispara nada hasta que alguien diga algo. Una vez que el Centinela
  abre el caso, la persona ya tiene control real para decidir: "Sí, fui yo" (`POST
  /casos/{caso}/reconocer`, pasa a `descartado`) o "Yo no autoricé esto" (dispara la oposición). Lo
  que **todavía no existe** es un control en `HuellaPage.jsx` para decidir sobre una consulta que
  llegó en `null` **antes** de que el Centinela la toque — hoy ese valor de arranque solo lo pone el
  seeder de demo. Es la pieza que falta para que el flujo sea autosuficiente de punta a punta desde
  el primer día de una consulta, no solo una vez que ya se convirtió en caso.
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
- Sin tests end-to-end de frontend (Playwright/Cypress) por tiempo; sí hay 49 tests de backend
  sobre el motor, que es donde está el riesgo real.
- **Descartado a propósito, no por falta de tiempo:** un "coach" de salud financiera con KPIs de
  score (metas, tendencias, consejos genéricos). Se evaluó y no se construyó porque es la misma
  idea informativa descartada en una iteración anterior, y porque coincide función por función con
  un producto que ya existe en el mercado ecuatoriano. Lo que sí se construyó en su lugar —
  desglose explicable del score y contexto de la persona en la oposición generada por IA — ataca la
  misma raíz (la persona no ve por qué se decidió algo sobre ella) sin duplicar un producto
  existente. Ver `AI_USAGE.md`.
- Para producción: mover `NOTIFICATION_DRIVER` a `whatsapp` con plantilla aprobada por Meta,
  agregar un usuario de base de datos sin permisos de `UPDATE`/`DELETE` sobre `eventos` y
  `documentos` (hoy la inmutabilidad se aplica solo a nivel de aplicación), y mover el cálculo de
  feriados de Ecuador a una fuente mantenida en vez de la lista fija en `config/casos.php`.
