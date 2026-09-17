# BRIEF — Reto técnico Decision Data · Plataforma B2C

> Documento de arranque para desarrollo. Contiene lo que el cliente pide (literal, sin interpretar)
> y la arquitectura que vamos a construir. Léelo completo antes de escribir la primera línea.

**Entrega:** viernes 18 de septiembre de 2026, 09:00, hora de Ecuador continental.
**Modalidad:** individual, repositorio personal de GitHub.
**Datos:** exclusivamente sintéticos.

---

## 1. Qué nos piden

Decision Data es un buró de información crediticia e inteligencia económica regulado en Ecuador.
Hoy su producto es B2B: le vende consultas a bancos y cooperativas. La persona solo existe como
el dato consultado.

El reto pide imaginar, diseñar y desarrollar una plataforma **B2C** para que una persona
**comprenda, proteja o utilice mejor** su información crediticia, financiera o de identidad.

No hay pantalla ni flujo previo que replicar. Libertad total en problema, usuario, propuesta de
valor, funcionalidades, recorrido, stack y papel de la IA.

Restricciones explícitas del enunciado:

- No puede reducirse a una banca web, un dashboard genérico ni una interfaz decorativa.
- La innovación debe aportar una utilidad que el usuario pueda comprender.
- No se evalúa cantidad de pantallas. **Una experiencia pequeña y completa vale más que una
  aplicación extensa e inconclusa.**
- Debe demostrar desarrollo full-stack real y manejo responsable de información sensible.
- Debe explicarse cómo evolucionaría hacia un producto real.

### Marca

Punto de partida: colores, logotipo e identidad visual de https://decisiondata.ec/.
Se permite evolución visual futurista si la marca sigue reconocible, el logo no se deforma y se
conserva legibilidad, coherencia y accesibilidad.

Se evalúa: jerarquía visual, navegación, **estados de la interfaz**, microinteracciones, mensajes,
**prevención de errores**, adaptación a móvil y facilidad de uso.

---

## 2. Requisitos técnicos mínimos (lista de verificación)

Todos son obligatorios. Ninguno es opcional.

- [ ] Frontend funcional y adaptable a escritorio y móvil
- [ ] Backend o capa de servicios funcional
- [ ] Comunicación entre componentes mediante una API
- [ ] Persistencia de información o estado
- [ ] Datos sintéticos suficientes para recorrer y probar la solución
- [ ] Validación de entradas y manejo de errores
- [ ] Estados de carga, vacío y falla
- [ ] Configuración mediante variables de entorno
- [ ] Pruebas automatizadas relevantes
- [ ] Medidas básicas de seguridad y privacidad

Prohibido: datos personales reales, reportes crediticios reales, credenciales reales,
información confidencial.

En la presentación hay que justificar el stack, explicar cómo se organizó frontend/backend/datos,
identificar riesgos y describir qué cambiaría para llevarlo a producción.

---

## 3. Contenido obligatorio del repositorio

Repositorio **dedicado exclusivamente a esta prueba**, en cuenta personal. No sirve un proyecto
anterior del portafolio.

Debe permitir a Decision Data: clonar en otro equipo, instalar dependencias, ejecutar la
aplicación, correr las pruebas y revisar la implementación.

- [ ] Código fuente completo de frontend y backend
- [ ] `README.md` con instalación, ejecución, pruebas y recorrido de demostración
- [ ] `.env.example` sin secretos
- [ ] Archivos de dependencias y lockfiles
- [ ] Migraciones o scripts para preparar la base de datos
- [ ] Datos sintéticos o instrucciones para generarlos
- [ ] Diagrama o explicación de arquitectura
- [ ] `AI_USAGE.md`
- [ ] Limitaciones conocidas y próximos pasos

No incluir: `.env` con valores reales, claves, tokens, contraseñas, datos personales, directorios
de dependencias, código confidencial de empleadores o terceros.

**Historial de commits razonable que permita observar la evolución.** No un único commit con todo.

La calificación inicial se hace sobre el commit del tag `entrega-final-2026-09-18`.
Los cambios posteriores a las 09:00 no entran.

### Datos a enviar por correo

1. Enlace al perfil personal de GitHub
2. Enlace al repositorio específico de la prueba
3. Nombre de la rama principal
4. SHA del commit final
5. Tag `entrega-final-2026-09-18`
6. Enlace a la aplicación desplegada, si existe
7. Instrucciones especiales para ejecutar

Si el repo es privado, hay que comunicarlo con anticipación y dar acceso a las cuentas que
Decision Data indique, antes del vencimiento.

---

## 4. Uso de IA

Permitido y esperado como acelerador. Se evalúa cómo se usa para velocidad, diseño, calidad,
pruebas, documentación y seguridad.

> **La aplicación NO está obligada a incorporar una función visible de IA.**
> Lo obligatorio es declarar cómo se usó la IA durante el desarrollo.

Hay que poder **comprender, explicar y modificar todo lo presentado**. Una app generada con un
único prompt sin revisión técnica no se considera suficiente.

`AI_USAGE.md` debe indicar:

- Herramientas de IA usadas y etapas en las que se emplearon
- Prompts o interacciones que influyeron en decisiones importantes
- Código, diseño o documentación generados o asistidos por IA
- **Errores detectados en las respuestas de la IA**
- **Correcciones y refactorizaciones hechas por el candidato**
- Pruebas y controles usados para verificar calidad y seguridad
- Decisiones tomadas directamente por el candidato

> Escribir este archivo **durante** el desarrollo, no al final. Los errores reales de la IA y sus
> correcciones son lo que se evalúa, y no se reconstruyen de memoria el viernes a las 6 a.m.

---

## 5. Criterios de evaluación y sus pesos

| Criterio | Peso |
|---|---|
| Programación full-stack | 20% |
| Arquitectura y capacidad de evolución | 15% |
| Experiencia de usuario y criterio de producto | 15% |
| Diseño visual, interfaz y adaptación responsive | 15% |
| Seguridad y privacidad | 10% |
| Calidad de código, pruebas y manejo de errores | 10% |
| Uso efectivo de inteligencia artificial | 10% |
| Dominio, autoría y presentación | 5% |

La originalidad se evalúa dentro de todos los criterios, no aparte. Una idea llamativa solo vale
si es útil, funciona y está correctamente implementada.

**Lectura de los pesos:** UX + diseño visual suman 30%, más que programación full-stack. El
frontend pulido y responsive no es decoración: es casi un tercio de la nota.

---

## 6. Agenda de la presentación (30 min)

| Tiempo | Contenido |
|---|---|
| 3 min | Problema seleccionado, usuario y visión del producto |
| 9 min | Demostración funcional del recorrido principal |
| 7 min | Arquitectura, repositorio y revisión de código |
| 4 min | Seguridad, pruebas y uso de IA |
| 4 min | **Modificación pequeña en vivo solicitada por el panel** |
| 3 min | Preguntas finales |

Hay que presentar la aplicación **funcionando** y mostrar el repositorio real de GitHub.
Las diapositivas son opcionales y no sustituyen la demostración.

---

## 7. Qué invalida la entrega

Cualquiera de estos anula el trabajo:

- El repositorio no existe, no está accesible o no puede clonarse
- La solución no puede ejecutarse siguiendo las instrucciones entregadas
- Faltan partes esenciales del código fuente
- Se exponen credenciales, tokens, claves o datos personales
- La entrega es únicamente visual y no demuestra desarrollo full-stack
- Se utiliza código confidencial de un empleador o un tercero
- Se oculta o se presenta de forma inexacta el uso de IA
- **Se presenta como funcional una característica simulada sin declararlo**
- El candidato no puede explicar o modificar su propia solución

---

# PARTE II — La propuesta

## 8. El problema elegido

En Ecuador nadie actúa a favor de la persona cuando su información crediticia está mal.
Los burós informan, explican, diagnostican y hasta recomiendan. Pero cuando aparece una consulta
que el titular no reconoce, una deuda que no es suya o un dato incorrecto, el trámite queda
entero en manos de la persona: averiguar a quién reclamar, redactar el escrito, saber qué artículo
invocar, contar los días hábiles, insistir, y escalar a la Superintendencia si nadie responde.

La mayoría no lo hace. No por desinterés, sino porque no sabe que puede.

**Usuario objetivo:** la persona que descubre en su reporte una consulta o un registro que no
reconoce y no sabe qué hacer con eso.

**Propuesta de valor, en una frase:** el buró deja de solo informarte y empieza a gestionar por ti.

**Diferenciador frente a la competencia existente** (Buró de Crédito Ecuador, Buró Ecuador, Aval
Buró, todos con app y score gratuito): ellos informan y acompañan. Nosotros ejecutamos el trámite.

---

## 9. El producto: un motor de casos

No es un dashboard. Es una máquina de estados con plazos legales, consentimiento verificable y
agentes que ejecutan pasos dentro de límites explícitos.

El puntaje y la huella de consulta están presentes, pero como contexto del caso, no como el
producto en sí.

### Recorrido principal (el único implementado punta a punta)

**Consulta no reconocida → oposición LOPDP**

1. **Detección.** El Centinela corre como job programado y encuentra una consulta que la persona
   no ha reconocido.
2. **Apertura.** Se crea el caso, arranca el reloj del plazo legal, sale el aviso por WhatsApp.
3. **Autorización.** La persona firma el consentimiento. Sin este estado ningún agente avanza.
4. **Gestión.** El Gestor redacta la oposición con los artículos aplicables, la dirige a la
   entidad y la registra como evidencia.
5. **Cierre o escalamiento.** Si la entidad responde, se cierra. Si vence el plazo, escala a la
   Superintendencia automáticamente.

### La regla que sostiene todo

> **El humano autoriza, el agente ejecuta.**
> Ninguna transición hacia `en_gestion` es posible sin un consentimiento firmado y vigente para
> ese caso específico y esa entidad específica. Esto se prueba con un test.

---

## 10. Arquitectura

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
                           │ reloj de plazos     │        │    (razonamiento
                           │ consentimiento      │        │     de agentes)
                           │ bitácora inmutable  │        │
                           └──────────┬──────────┘        └──► Meta Cloud API
                                      │                        (plantilla + webhook)
                           ┌──────────▼──────────┐
                           │ AGENTES             │
                           │ Centinela · Gestor  │
                           │ (Vocero → fase 2)   │
                           └─────────────────────┘
```

### Tres reglas de integración, no negociables

1. **Laravel es el único dueño del estado.** Ningún otro servicio escribe en MySQL. Compartir base
   rompe la trazabilidad y anula la auditoría.
2. **Las herramientas del agente son endpoints de Laravel con token de alcance limitado por caso**,
   expirable. El agente no accede a la base ni a la API completa.
3. **La bitácora es append-only.** Sin `update`, sin `delete`. Es la evidencia.

### Los agentes

| Agente | Qué hace | Estado |
|---|---|---|
| **Centinela** | Job programado. Corre sin usuario. Detecta consultas nuevas y patrones anómalos, abre el caso y notifica. | Implementado |
| **Gestor** | Redacta la oposición, la dirige a la entidad, vigila el plazo, escala a la Superintendencia si vence. | Implementado |
| **Vocero** | Llama a la entidad reportante, navega el IVR, sostiene la conversación; el motor extrae el compromiso de la transcripción. | **Fase 2 — no implementado, declarado como tal en el README y en la UI** |

> El punto 10 del enunciado invalida la entrega si se presenta como funcional algo simulado.
> El Vocero aparece dibujado en la arquitectura y marcado explícitamente como fase 2 en los tres
> lugares donde se menciona. Esto no resta: demuestra capacidad de evolución, que vale 15%.

---

## 11. Modelo de datos

```
personas              consultas              casos
────────              ─────────              ─────
id                    id                     id
cedula_hash           persona_id ──────┐     persona_id ────► personas.id
nombre                entidad_nombre   └───► consulta_id ───► consultas.id
telefono_e164         motivo                 tipo
identidad_verificada_en  consultada_en       estado
                      reconocida             abierto_en
                                             vence_en

consentimientos       eventos                documentos
───────────────       ───────                ──────────
id                    id                     id
caso_id ──► casos.id  caso_id ──► casos.id   caso_id ──► casos.id
alcance               actor                  tipo
texto_version         tipo                   contenido
firmado_en            carga                  generado_por
canal                 ocurrio_en             creado_en
```

Notas de implementación:

- `cedula_hash`: la cédula nunca se guarda en claro.
- `consentimientos.texto_version`: se guarda la versión exacta del texto que la persona aceptó,
  no una referencia mutable.
- `eventos`: append-only. Sin `updated_at`. Migración sin permisos de update/delete para el
  usuario de aplicación si da el tiempo; si no, se documenta la intención.
- `casos.estado`: gobernado por la máquina de estados, nunca escrito directamente.

### Máquina de estados

```
detectado ──► notificado ──► autorizado ──► en_gestion ──┬──► resuelto
                                                          └──► escalado ──► resuelto
```

| Estado | Significado |
|---|---|
| `detectado` | Caso abierto por el Centinela, nadie notificado aún |
| `notificado` | Aviso entregado, esperando decisión de la persona |
| `autorizado` | Consentimiento firmado — **única puerta hacia la acción** |
| `en_gestion` | Oposición emitida, plazo corriendo |
| `escalado` | Plazo vencido sin respuesta, va a la Superintendencia |
| `resuelto` | Estado final, evidencia archivada |

Cualquier transición fuera de este grafo debe ser rechazada por el motor y cubierta por un test.

---

## 12. Herramientas del agente (contrato)

Endpoints de Laravel, token de alcance limitado por caso.

| Herramienta | Qué hace |
|---|---|
| `obtener_caso` | Lee el caso y su historial |
| `verificar_consentimiento` | Confirma alcance y vigencia antes de actuar |
| `redactar_oposicion` | Genera el documento y lo guarda |
| `registrar_evento` | Escribe en la bitácora inmutable |
| `avanzar_estado` | Solicita una transición; el motor la valida o la rechaza |
| `notificar_persona` | Envía el mensaje por el canal registrado |

---

## 13. Qué se prueba (tests automatizados)

Mínimo, todos sobre el motor porque es donde está el valor:

1. Transición inválida rechazada por la máquina de estados
2. Agente sin consentimiento vigente no puede avanzar a `en_gestion`
3. Cálculo de días hábiles y detección de vencimiento
4. Escalamiento disparado automáticamente al vencer el plazo
5. La bitácora no admite modificación ni borrado
6. Validación de cédula rechaza entradas mal formadas

---

## 14. Estados de interfaz (requisito explícito)

| Estado | Tratamiento |
|---|---|
| **Cargando** | Esqueleto con la forma del contenido real. Nunca pantalla en blanco ni salto de layout. |
| **Vacío** | Explica *por qué* está vacío y ofrece la siguiente acción. En "sin casos abiertos", además tranquiliza: el Centinela sigue vigilando. |
| **Falla** | No culpa al usuario, aclara que nada se perdió (los plazos siguen corriendo), da un código de referencia y un botón de reintento. |

También cubiertos: validación de cédula con mensaje específico por campo, sesión expirada con
recuperación de contexto, caso sin permiso de lectura, fallo de envío de WhatsApp con reintento en
cola, confirmación antes de revocar una autorización.

---

## 15. Identidad visual

Base tomada de decisiondata.ec, con evolución propia.

| Token | Valor | Uso |
|---|---|---|
| Fondo | `#090E22` | Color base de la marca |
| Superficie | `#0F1733` | Tarjetas |
| Superficie 2 | `#141D42` | Bloques internos, destacados |
| Borde | `#23305C` | Separadores y contornos |
| Texto | `#EEF2FF` | Principal |
| Texto secundario | `#94A2CC` | Apoyo |
| Azul | `#4C8DFF` | Acción primaria, motor |
| Teal | `#23D3B4` | Agentes, confirmaciones |
| Ámbar | `#F5A623` | Plazos, autorización pendiente |
| Verde | `#35C27F` | Resuelto |
| Rojo | `#FF5C47` | Falla, revocación |

Tipografía: **IBM Plex Sans** (interfaz) e **IBM Plex Mono** (identificadores de caso, estados,
etiquetas técnicas).

Vocabulario de la marca que hay que conservar: *Panorama*, *huella de consulta*, *score /1000*,
*alertas*.

Accesibilidad: contraste mínimo 4.5:1 en texto normal, objetivos táctiles de al menos 44 px,
`<button>` y `<a href>` reales, `aria-label` en botones de solo ícono.

---

## 16. Stack y despliegue

| Capa | Elección | Justificación para la presentación |
|---|---|---|
| Frontend | React (SPA) | Componentes reutilizables para los estados repetidos de caso; responsive desde el diseño |
| Backend | Laravel | Colas, scheduler, migraciones y testing en la misma caja; el motor de casos encaja natural en servicios y eventos |
| Base | MySQL | Relacional porque el modelo es relacional y la auditoría exige integridad referencial |
| Colas | Redis | Job programado del Centinela y reintentos de notificación |
| Razonamiento | Claude API con tool use | Los agentes solo pueden llamar endpoints propios, nunca la base |
| Mensajería | Meta Cloud API | Plantilla de utilidad + webhook de respuesta |
| Empaque | Docker Compose | `app · mysql · redis · worker`. Clonar, copiar `.env.example`, levantar. |

**Requisito del enunciado:** si la solución depende de un servicio externo, debe existir una
alternativa razonable para demostrar el recorrido principal. Por lo tanto: el driver de
notificación debe ser conmutable por variable de entorno entre `whatsapp` y `log`, de modo que el
recorrido completo se pueda correr sin credenciales de Meta. Esto se documenta en el README.

---

## 17. Orden de construcción

El orden importa porque hay dependencias duras:

```
1. Modelo de datos + migraciones
        │
2. Máquina de estados + reloj de plazos  ◄── con sus tests
        │
3. API de herramientas (los 6 endpoints)
        │
        ├──► 4a. Agentes (Centinela, Gestor)
        │
        └──► 4b. Frontend (Panorama, Caso, estados de interfaz)
                    │
5. Driver WhatsApp real (conmutable) + seeders sintéticos
        │
6. README · AI_USAGE.md · .env.example · Docker Compose · tag
```

No empezar por el frontend. Sin API que responda, el frontend se construye contra datos inventados
y luego hay que rehacerlo.

---

## 18. Riesgos y mitigaciones

| Riesgo | Mitigación |
|---|---|
| Aprobación de la plantilla de WhatsApp en Meta demora | Driver conmutable a `log` desde el inicio; la demo funciona sin Meta |
| El alcance crece y nada queda terminado | Un solo tipo de caso. Los demás quedan definidos en el modelo (`casos.tipo`) sin implementar |
| Commits amontonados al final | Commit cada 30–45 min, mensajes convencionales, desde la primera hora |
| `AI_USAGE.md` reconstruido de memoria | Anotar errores de la IA y correcciones en el momento en que ocurren |
| No poder modificar código en vivo | Releer todo el código el jueves por la noche. Es el 4% directo del puntaje y el filtro real de la prueba |

---

## 19. Limitaciones conocidas (para el README)

- Solo se implementa un tipo de caso: consulta no reconocida → oposición LOPDP.
- El agente de voz (Vocero) no está implementado. Solo existe el contrato de integración.
- El envío a la entidad reportante y a la Superintendencia se registra como documento generado;
  no hay integración real con sus canales, porque no son públicos.
- La verificación de identidad es simulada sobre datos sintéticos.
- Todos los datos de personas, consultas y entidades son ficticios y generados por seeders.
