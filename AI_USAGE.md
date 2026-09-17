# Uso de IA en este proyecto

Este archivo se escribió **durante** el desarrollo (mismo día, misma sesión), no reconstruido de
memoria al final. Documenta el uso real de IA, incluidos los errores y las correcciones.

## Herramientas usadas

- **Claude.ai (conversación previa, fuera de este repositorio):** el candidato definió ahí el
  problema, el usuario objetivo, la propuesta de valor y una primera versión de la arquitectura,
  antes de empezar a programar. El resultado de esa conversación es
  [`docs/BRIEF-decision-data.md`](docs/BRIEF-decision-data.md), que sirvió como especificación de
  entrada para todo el desarrollo posterior. (Nota técnica: durante esta sesión se intentó releer
  esa conversación desde su enlace compartido para citarla con más detalle, pero el enlace de
  `claude.ai/share/...` es una SPA que no expone su contenido a una lectura automatizada sin
  navegador — solo se pudo confirmar que el correo de invitación original de Decision Data,
  adjunto como capturas en `docs/preview_*.webp`, es consistente con el brief.)
- **Claude Code (Sonnet 5), en esta sesión:** escribió la totalidad del código de este
  repositorio — backend, frontend, Docker, tests y esta documentación — en conversación directa
  con el candidato, quien revisó cada pieza, tomó las decisiones de producto/alcance, y verificó
  el comportamiento corriendo la app y la suite de tests en cada paso.
- **Runtime de la app (`ClaudeRedactor`, opcional):** si se configura `ANTHROPIC_API_KEY`, el
  agente Gestor llama a la Claude API (Messages API, sin tool use todavía — ver limitación abajo)
  para redactar el texto de la oposición LOPDP. Sin la key, usa `RedactorStub`, declarado como tal
  en el propio código y en el README.

## Decisiones tomadas directamente por el candidato

- **GitHub:** decidió instalar `gh` CLI y autenticarse él mismo (en vez de pegar un repo ya creado
  o subir todo al final manualmente), para que el historial de commits se construya en tiempo
  real desde el arranque, tal como pide el enunciado.
- **Claude API key:** decidió empezar con `RedactorStub` en vez de proveer una key de inmediato,
  para no bloquear el resto del desarrollo. Esto se documentó como una interfaz intercambiable
  (`AgenteRedactorInterface`) desde el diseño, precisamente para que conectar la key real después
  fuera un cambio de configuración, no de código.
- **Alcance del recorrido:** confirmó (siguiendo el propio brief) implementar un solo tipo de caso
  punta a punta en vez de repartir el tiempo entre varios tipos a medio terminar.
- **Instalar Laravel Boost:** el scaffold de `laravel new` sugiere instalarlo (`composer require
  laravel/boost`). Se decidió omitirlo por el tiempo tan ajustado de la entrega y porque su
  instalación es interactiva; se documenta aquí para que quede explícito por qué el `CLAUDE.md`
  que genera Laravel no está en el repo.

## Errores reales de la IA detectados y corregidos en esta sesión

Todos verificables en el historial de commits y en la propia base de código:

1. **Seeder rompía una transición de estado.** El seeder original guardaba el `Caso` en una
   variable antes de autorizarlo y gestionarlo, y luego intentaba transicionarlo a `resuelto`
   usando esa misma variable ya desactualizada (seguía en memoria como `autorizado`). El motor de
   estados rechazó correctamente la transición (`TransicionInvalidaException: no se puede pasar de
   'autorizado' a 'resuelto'`) al correr `php artisan migrate:fresh --seed`. Corrección: refrescar
   el modelo (`->fresh()`) antes de la transición final. El fallo lo hizo visible el propio motor
   de estados haciendo su trabajo — exactamente el tipo de control que se pedía tener.
2. **Cédulas sintéticas de prueba inventadas sin calcular el dígito verificador real.** Al escribir
   `CedulaEcuatorianaTest`, la primera versión usaba números de cédula "inventados a ojo" para los
   casos válido/inválido, sin correr el algoritmo módulo 10 que la propia regla implementa.
   Corrección: se generaron los dígitos verificadores reales con un script de una línea antes de
   fijarlos en el test, para que el test verifique el algoritmo de verdad y no una coincidencia.
3. **Orden del seeder rompía el escenario de demo en vivo.** La consulta pensada para demostrar la
   detección del Centinela **en vivo** durante la presentación se creaba *antes* de correr
   `Centinela::detectar()` en el seeder, así que quedaba procesada de una vez con las demás y no
   quedaba ninguna consulta pendiente para la demo. Se detectó inspeccionando los datos sembrados
   con `tinker` (no coincidía lo esperado con lo sembrado) y se corrigió moviendo esa consulta a
   después de la llamada a `detectar()`.
4. **`@dataProvider` de PHPUnit obsoleto.** La primera versión de `CedulaEcuatorianaTest` usaba la
   anotación de docblock `@dataProvider`, que PHPUnit 12 ya no soporta (falló con "Too few
   arguments... 0 passed... exactly 1 expected"). Corrección: atributo `#[DataProvider(...)]`.
5. **Excepción de dominio filtrándose como error 500 en un endpoint.** `redactarOposicion` en
   `AgenteHerramientasController` lanzaba `ConsentimientoRequeridoException` sin capturarla,
   devolviendo un 500 genérico en vez de un 422 claro (inconsistente con cómo `avanzarEstado` sí
   maneja ese mismo caso). Se corrigió antes de que hubiera un test que lo hiciera evidente,
   revisando la simetría entre ambos métodos.
6. **Los tests dentro de Docker corrían contra la base de datos real de la demo**, no contra
   sqlite en memoria. `phpunit.xml` declara `DB_CONNECTION=sqlite` como variable de entorno, pero
   sin `force="true"` PHPUnit no sobreescribe una variable que el proceso ya trae del sistema real
   — y dentro del contenedor `app`, `DB_CONNECTION` sí llega como variable real (vía `env_file` en
   `docker-compose.yml`). Resultado: `php artisan test` ejecutó `migrate:fresh` sobre la base MySQL
   de la demo y la dejó vacía. Se descubrió corriendo la suite manualmente dentro del contenedor
   como parte de la verificación de este mismo stack, no por un reporte externo. Corrección: forzar
   (`force="true"`) las variables críticas en `phpunit.xml`, y quitar además el `env_file` que
   filtraba la configuración de desarrollo local hacia los contenedores (ver README/commit
   `fix: 5 bugs reales...` para el resto de bugs de Docker encontrados de la misma forma: probando
   el `docker compose up` real en vez de asumir que un `docker-compose.yml` que "se ve bien" ya
   funciona).
7. **Healthcheck de `app` fallaba siempre.** `wget http://localhost:8000/up` desde dentro del
   propio contenedor daba "connection refused", aunque el log mostraba el servidor corriendo. Causa:
   en Alpine, `localhost` resuelve primero a `::1` (IPv6), y el servidor embebido de PHP solo
   escucha en IPv4. Corrección: usar `127.0.0.1` explícito en el healthcheck.
8. **mkdir con expansión de llaves no funciona en `sh`.** El Dockerfile tenía
   `mkdir -p storage/framework/{cache,sessions,views,testing}`, que en bash crea 4 carpetas pero en
   `sh` (el shell real de `RUN` en Docker) crea una sola carpeta llamada literalmente
   `{cache,sessions,views,testing}`. Faltaba `storage/framework/sessions`, y con `SESSION_DRIVER=file`
   la app respondía 500 en todo, incluida `/up` ("Please provide a valid cache path"). Corrección:
   rutas explícitas, una por una.
9. **`artisan key:generate` se niega a correr dentro de Docker.** Con `APP_KEY` presente como
   variable de entorno real (aunque vacía), el comando aborta con "APP_KEY is already present in
   the environment" en vez de escribir en `.env`. Corrección: generar la key y escribirla
   directamente en el entrypoint, sin pasar por ese comando.
10. **La espera a la base de datos en el entrypoint nunca terminaba.** Usaba `php artisan db:show`
    como sonda de "¿ya puedo conectarme?", pero ese comando usa `Number::format()`, que requiere la
    extensión `intl` — no instalada en la imagen — y truena con `RuntimeException` aunque la
    conexión a MySQL sí funcione. El contenedor `worker` se quedaba en un loop infinito de
    "Esperando a la base de datos...". Corrección: sondar con una conexión PDO directa en vez de un
    comando que hace trabajo de más; se instaló `intl` de todas formas por si algo más lo necesita.

## Pruebas y controles usados para verificar calidad y seguridad

- 37 tests automatizados (`php artisan test`) cubriendo los 6 puntos mínimos que pedía el
  enunciado, más el contrato completo de las 6 herramientas del agente y el flujo de
  consentimiento vía HTTP.
- Verificación manual end-to-end con `curl` contra un servidor real (no solo tests unitarios):
  login → panorama → firmar consentimiento → cola → `en_gestion` con documento generado, y
  `centinela:ejecutar` / `casos:escalar-vencidos` corridos de verdad, no solo simulados en tests.
- `npm run build` del frontend como control de que no hay errores de compilación/imports antes de
  dar por buena cualquier pantalla.
- Revisión manual de que ningún archivo con secretos (`.env`, `database.sqlite`) quedó en el
  historial de git (`.gitignore` revisado antes del primer commit).

## Limitación declarada sobre el uso de IA en el runtime de la app

El diseño arquitectónico (sección 12 del brief) describe 6 herramientas del agente como endpoints
de Laravel que un loop agéntico de Claude (con tool use) llamaría en secuencia, decidiendo él mismo
el orden. Lo que existe hoy:

- Los 6 endpoints están completos, protegidos por token de alcance limitado y expirable, y
  probados de forma independiente (`tests/Feature/AgenteHerramientasTest.php`) — se pueden invocar
  con `curl` exactamente como lo haría un agente real.
- Los agentes `Centinela` y `Gestor` (`app/Domain/Casos/Agentes/`) **orquestan hoy en proceso**
  (llaman directamente a los mismos servicios de dominio que hay detrás de esos endpoints), no
  todavía a través de un loop de tool use real contra la Claude API.
- Esto se declara explícitamente aquí y en el README: **no se presenta como funcional un loop
  agéntico que no existe.** Lo que sí es real y funcional es la redacción del documento vía
  `ClaudeRedactor` cuando hay `ANTHROPIC_API_KEY`, y todo el resto del recorrido (persistencia,
  máquina de estados, bitácora, notificaciones, frontend).
