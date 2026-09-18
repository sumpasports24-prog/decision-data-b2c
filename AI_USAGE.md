# Uso de IA en este proyecto

Este archivo se escribió **durante** el desarrollo (mismo día, misma sesión), no reconstruido de
memoria al final. Documenta el uso real de IA, incluidos los errores y las correcciones.

## Herramientas usadas

- **Claude.ai (conversación previa, fuera de este repositorio):** el candidato definió ahí el
  problema, el usuario objetivo, la propuesta de valor, la arquitectura y el diseño visual, antes
  de empezar a programar. Esa conversación produjo cuatro artifacts: el brief
  ([`docs/BRIEF-decision-data.md`](docs/BRIEF-decision-data.md)), una bitácora completa de las
  decisiones tomadas y descartadas, un prototipo interactivo descartado ("Horizonte", un dashboard
  informativo — descartado precisamente por ser solo informativo, sin agentes), y un canvas de
  diseño ("Decision Data — Motor de Casos") con los mockups reales de Panorama, Caso, Estados y
  Móvil. El enlace compartido de esa conversación (`claude.ai/share/...`) no se pudo releer
  directamente en esta sesión porque es una SPA sin contenido accesible sin navegador; sí se
  pudieron leer los cuatro artifacts guardados en la cuenta del candidato, que es de donde salió
  todo lo anterior.
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
- **Flujo de ramas `adrian` → `developer` → `producción`:** se decidió reestructurar el repositorio
  para que no todo viviera en `main` — cada commit nace en `adrian` (desarrollo), se integra en
  `developer`, y solo lo verificado llega a `producción` (la rama principal reportada a Decision
  Data). Es un cambio de proceso pedido explícitamente por el candidato, no una sugerencia de la IA.
- **Actualización en vivo del Panorama y del caso:** el candidato pidió más diferenciación visual
  frente a otras propuestas. La IA propuso pulir la interfaz sin cambiar el fondo del producto; el
  candidato pidió, en cambio, que la diferenciación fuera funcional y visible en vivo. De ahí salió
  el polling en segundo plano (Panorama y detalle de caso se actualizan solos si el Centinela o el
  Gestor hacen algo, con aviso) y el mensaje real de WhatsApp en la bitácora.

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
11. **El primer frontend no seguía el diseño ya aprobado.** La IA construyó una primera versión de
    Panorama y Caso a partir del brief y su propio criterio de diseño, sin haber leído el canvas de
    Claude Design ("Decision Data — Motor de Casos") que ya existía con los mockups reales, porque
    esa lectura requiere una llamada explícita al Artifact tool que nadie había hecho todavía. El
    candidato lo notó ("el diseño propuesto es otro") al comparar lo construido con lo que recordaba
    haber diseñado. Corrección: se leyeron los 9 archivos del canvas real y se reconstruyó el
    frontend para que calzara — nav superior con 3 secciones y avatar, layout de dos columnas,
    stepper de 5 estados, indicador "vigilancia activa", y la nota de fase 2 del Vocero directamente
    en la interfaz (antes solo estaba en el README, y el brief exige declararlo en tres lugares).
    Lección operativa: cuando existe una fase previa de diseño en Claude.ai, hay que pedir y leer
    sus artifacts *antes* de escribir la primera pantalla, no confiar en que el brief textual basta.
12. **El frontend no era accesible fuera de la máquina donde corre.** `VITE_API_URL` se resolvía en
    build-time a `http://localhost:8000/api`. Al abrir el frontend desde otro dispositivo en la red
    (reportado por el candidato probando desde su celular), "localhost" apuntaba al propio
    dispositivo, no al servidor, y el login nunca conectaba. Corrección: si no se fija explícitamente,
    la URL de la API se calcula en runtime con `window.location.hostname`, así la misma build sirve
    por `localhost`, por IP de red o por un dominio real sin reconfigurar nada.
13. **El stepper de 5 estados se cortaba en móvil.** "Detecta…", "Notifica…", "Autoriza…" — cinco
    píldoras no caben legibles en 390px. Se detectó con las capturas de Playwright en viewport móvil,
    no revisando el código. Corrección: en pantallas angostas colapsa a una sola etiqueta (el estado
    actual) más una barra de progreso "2/5", en vez de intentar comprimir el texto de las 5.
14. **`worker` y `scheduler` se morían solos y no volvían.** Al reconstruir las imágenes tras el
    refactor del backend, `docker compose ps` mostró `worker` y `scheduler` en `Exited (1)`. Sus
    logs mostraban `SQLSTATE... Base table or view not found: personas` durante el seeder — los tres
    contenedores (`app`, `worker`, `scheduler`) corrían el mismo `entrypoint.sh`, que migra y siembra
    de forma independiente; si dos lo hacen casi al mismo tiempo, uno puede leer la tabla justo
    cuando el otro la está recreando. Sin política de reinicio, el contenedor que perdía la carrera
    quedaba muerto para siempre, sin que nada lo relevantara. De paso, cada contenedor generaba su
    propia `APP_KEY` aleatoria al arrancar — un problema real aparte, porque `Persona::hashCedula()`
    usa esa key como sal: con keys distintas por contenedor, un hash calculado por uno nunca
    coincidiría con el de otro. Corrección: `worker` y `scheduler` usan un entrypoint separado
    (`entrypoint-worker.sh`) que solo espera a la base y nunca migra ni siembra — confían en
    `depends_on: app: condition: service_healthy`, que ya garantiza que `app` terminó de migrar
    antes de que ellos arranquen; se fijó una `APP_KEY` real en `.env.example` para que los tres
    contenedores compartan la misma; y se agregó `restart: unless-stopped` a todos los servicios
    como red de seguridad. Verificado forzando la condición de carrera original (un
    `migrate:fresh --seed` manual mientras `worker`/`scheduler` seguían corriendo) y confirmando que
    ya no se caen.
15. **El modelo `Entidad` apuntaba a una tabla que no existía.** Al normalizar
    `consultas.entidad_nombre` en su propia tabla `entidades`, los tests fallaron con
    `no such table: entidads`. Eloquent pluraliza nombres de modelo al inglés por defecto
    ("Entidad" → "entidads"), y la migración sí creaba "entidades" (español). Corrección:
    `protected $table = 'entidades';` explícito en el modelo — algo que hasta ahora no había hecho
    falta porque los demás modelos (`Persona`→personas, `Consulta`→consultas, `Caso`→casos) sí
    coinciden por accidente con la pluralización naive en inglés.

16. **Riesgo anticipado (no observado en producción, mitigado antes de que ocurriera): el prompt
    del Gestor podía inventar hechos.** Al agregar el campo donde la persona describe qué recuerda
    de la consulta ("contanos qué recordás de esa fecha") y pasarlo al prompt de `ClaudeRedactor`,
    el riesgo evidente es que el modelo "complete" la historia con fechas, montos o lugares que la
    persona nunca mencionó — un documento legal no puede llevar hechos inventados. Corrección
    aplicada en el propio prompt (`ClaudeRedactor.php` y su espejo en `RedactorStub.php` para que la
    demo sin API key muestre el mismo comportamiento): instrucción explícita de citar el texto de
    la persona como declaración textual y de no agregar datos que no estén ahí o en el caso. No se
    trata de un error detectado en runtime (no hubo llamadas reales a la API durante el desarrollo
    de esta función), sino de una mitigación de diseño hecha antes de habilitarla.

17. **El botón de autorización sonaba a que la persona estaba dando consentimiento para que le
    consultaran el buró, cuando esa consulta ya había ocurrido — es justo lo que se está
    disputando.** Detectado en una revisión de copy la última noche: el botón decía "Autorizar y
    firmar" sin decir a quién se autoriza, y el texto de arriba arrancaba con un residuo de
    desarrollo visible al usuario final ("v1: Autorizo a Decision Data…", donde "v1" es la versión
    interna del texto legal, `config('casos.texto_consentimiento.version')`, filtrada por error a
    la interfaz). Corrección en `CasoPage.jsx`: se quitó el "v1: " literal, el botón pasó a
    "Autorizar a Decision Data" (deja explícito el sujeto de la autorización), y se agregó una
    línea aclaratoria explícita distinguiendo "autorizar una consulta nueva" de "autorizar a
    Decision Data a actuar sobre la consulta que ya existe".
18. **El frontend mostraba "En disputa" para una consulta que nadie había revisado todavía.**
    Surgió al responder una pregunta sobre el flujo real: `consultas.reconocida` es un booleano
    nullable de 3 estados (`null` = sin revisar, `true`/`false` = decisión de la persona), y el
    Centinela ya distinguía bien los tres (`where('reconocida', false)`, nunca actúa sobre `null`).
    Pero `PanoramaPage.jsx` y `HuellaPage.jsx` usaban `consulta.reconocida ? verde : ambar`, que
    trata `null` igual que `false` — una consulta recién llegada, sin decisión de nadie, se veía
    idéntica a una en disputa real. Con los datos sembrados no se notaba (el seeder solo usa `true`
    o `false` explícitos, nunca `null`), así que no lo detectó ningún test ni verificación visual
    anterior. Corrección: nuevo helper `frontend/src/utils/reconocimiento.js` con los 3 estados
    explícitos ("Reconocida" / "Pendiente de revisión" / "En disputa"), usado en ambas pantallas.

## Decisión de producto: el camino de "reconocer" (agregado horas antes del cierre)

Surgió al explicarle a el candidato, en lenguaje llano, qué hace el sistema — y a la mitad de esa
conversación él mismo notó el hueco: *"lo que no veo es una forma de decirle no es sospechoso"*. El
sistema, hasta ese momento, asumía que toda consulta con `reconocida = false` era necesariamente
una disputa, y no daba forma de decir simplemente "sí, fui yo". Eso contradecía algo que la propia
conversación ya había establecido: la mayoría de las consultas de un cliente real de un banco socio
SÍ están autorizadas por el contrato que firmó al hacerse cliente — el caso de oposición es la
excepción que justifica el motor, no el camino más transitado.

**Cambio implementado:**
- Nuevo estado `descartado` en `CasoEstado` (rama corta desde `notificado`, sin pasar por
  autorización ni Gestor — no hay nada que gestionar cuando la persona sí reconoce la consulta).
- Nuevo endpoint `POST /casos/{caso}/reconocer` (`CasoService::reconocer`), que marca
  `consultas.reconocida = true` y cierra el caso en dos pasos, sin consentimiento de por medio.
- En `CasoPage.jsx`, la pantalla de un caso `notificado` ahora pregunta "¿la reconoces?" con dos
  botones — "Sí, fui yo" y "Yo no autoricé esto" — en vez de ir directo a un formulario de
  autorización que presuponía la sospecha.
- Copy ajustado a propósito: nunca "aprobar" (un banco no espera tu aprobación para una consulta ya
  autorizada por contrato) y nunca "no es sospechoso" (habla del hecho, "sí fui yo", no de un
  juicio). La alerta en Panorama pasó de "Hay una consulta que no reconociste" (presuponía la
  conclusión) a "Hay una consulta por revisar".

No se tocó ninguna tabla nueva — el cambio completo es un estado más en un enum, un endpoint, y
texto de interfaz. Documentado acá porque cambia la lectura completa del producto: sin este camino,
un evaluador ve un sistema que trata cada consulta bancaria como sospechosa por defecto, lo cual es
falso y, sin querer, plantea a Decision Data como una herramienta contra sus propios socios
bancarios en vez de una a favor de la transparencia con el titular.

## Corrección de fondo: el Centinela vigilaba `false`, no `null` (detectado por el candidato)

El camino de "reconocer" (sección anterior) resolvió cómo se ve la decisión en pantalla, pero dejó
una grieta debajo que el candidato encontró revisando el propio catálogo de casos de uso: UC-01
describía una consulta que nace **ya** `reconocida = true`, sin pasar nunca por revisión ni caso —
mientras que las demás sí pasaban por el Centinela. Su pregunta fue exacta: *"¿no habíamos dicho que
toda consulta entra a revisión, por más que el cliente ya haya gestionado todo en el banco?"*

Tenía razón, y el motivo es una imposibilidad lógica que se había colado sin que nadie la notara: el
`Centinela::detectar()` filtraba `where('reconocida', false)` — es decir, **solo actuaba sobre
consultas que ya estaban marcadas como no reconocidas antes de que la persona dijera nada.** Pero el
sistema no tiene forma de saber de antemano que algo "no se reconoce" — eso solo lo puede decidir el
titular. La única fuente que producía ese `false` de entrada era el propio seeder de la demo,
simulando un resultado que en la app real nunca se generaría solo.

**Corrección:**
- `Centinela::detectar()` ahora filtra `whereNull('reconocida')`: vigila **toda** consulta sin
  revisar, sin distinguir de antemano cuál terminará reconocida y cuál en disputa.
- `ConsentimientoService::firmar()` ahora marca `consultas.reconocida = false` **en el momento en
  que la persona firma** diciendo "no la reconozco" — es la salida de esa decisión, ya no una
  condición previa. `CasoService::reconocer()` ya hacía lo simétrico con `true`.
- El mensaje de WhatsApp (`MensajeNotificacion`) dejó de decir "detectamos que... y no la
  reconociste" (presuponía la conclusión) y pasó a "confirma si la reconoces".
- El seeder de demo se reescribió para que las 4 consultas de Ana nazcan `null` y el Centinela las
  abra a todas por igual (incluida la de Banco Pichincha, que ahora también pasa por "notificado" y
  se resuelve con "Sí, fui yo" en vivo, en vez de nacer ya resuelta).
- 3 tests nuevos/reescritos (`CentinelaTest`, `ConsentimientoFlowTest`) fijan la semántica correcta:
  el Centinela no actúa sobre una consulta ya reconocida ni sobre una ya disputada con caso propio,
  y firmar deja constancia de `reconocida = false` como resultado.

Es el tipo de error que no rompe ningún test hasta que alguien piensa el flujo completo de punta a
punta en vez de por partes — exactamente lo que se le pide al candidato poder hacer en la
modificación en vivo de la presentación.

## Decisiones de producto evaluadas y descartadas la última noche

Antes de cerrar, se discutieron con el asistente de IA dos ideas adicionales y se decidió no
construirlas, con el argumento documentado acá para que quede explícito que la decisión fue
deliberada y no una limitación de tiempo disfrazada:

- **Agente Vocero (llamada de voz de seguimiento).** Ya estaba declarado como fase 2, sin
  implementar, en el README y en la interfaz (ver tarjeta "Fase 2" en `CasoPage.jsx`). Se descartó
  reforzarlo porque WhatsApp ya cubre el canal de notificación real y con constancia escrita —
  agregar voz simulada a 12 horas del cierre sumaba superficie sin sumar evidencia.
- **"Coach de salud financiera" con KPIs de score.** Se evaluó y se descartó por tres razones: (1)
  es la misma idea descartada dos días antes por ser solo informativa — pantallas sin acción real
  detrás; (2) coincide, función por función, con un producto que ya existe en el mercado ecuatoriano
  (Buró de Crédito Ecuador); (3) el enunciado no exige una función de IA visible, solo declarar su
  uso durante el desarrollo — no había ningún requisito que cubrir con eso. En su lugar se invirtió
  el tiempo en profundizar la única función de IA que sí es real y propia del caso: el Gestor
  redactando con el contexto de la persona (punto 16 arriba), y en un desglose de factores del score
  (`PanoramaService::scoreDeContexto()`) para poder explicar el número en vez de solo mostrarlo —
  relevante porque el planteamiento original en la entrevista de trabajo fue "mostrarle a la persona
  su informe", y la conclusión a la que se llegó construyendo el producto fue que mostrar no basta si
  no se explica: es la misma intuición, un paso más adelante.

## Pruebas y controles usados para verificar calidad y seguridad

- 50 tests automatizados (`php artisan test`) cubriendo los 6 puntos mínimos que pedía el
  enunciado, el contrato completo de las 6 herramientas del agente, el flujo de consentimiento vía
  HTTP (incluido que el contexto que escribe la persona se persiste y se cita textualmente en el
  documento generado, y que su ausencia no rompe nada), el desglose de factores del score, y el
  camino corto de reconocer una consulta (transición a `descartado`, quién puede hacerlo, y que un
  caso ya cerrado por eso no puede reabrirse).
- Verificación manual end-to-end con `curl` contra un servidor real (no solo tests unitarios):
  login → panorama → firmar consentimiento → cola → `en_gestion` con documento generado, y
  `centinela:ejecutar` / `casos:escalar-vencidos` corridos de verdad, no solo simulados en tests.
- `npm run build` del frontend como control de que no hay errores de compilación/imports antes de
  dar por buena cualquier pantalla.
- Revisión manual de que ningún archivo con secretos (`.env`, `database.sqlite`) quedó en el
  historial de git (`.gitignore` revisado antes del primer commit).
- Verificación visual real del frontend, no solo "se ve bien en el código": se instaló Playwright
  con Chromium headless y se corrió contra el stack de Docker real (login, Panorama y detalle de
  caso, en viewport de escritorio y de móvil de 390px, más los estados de error de cédula y el
  modal de confirmación de revocar). Eso fue lo que hizo evidente un wrap feo de la fecha en
  "huella de consulta" en móvil, que se corrigió antes de darlo por terminado.

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
