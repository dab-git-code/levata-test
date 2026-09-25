# ARCHITECTURE.md — Sistema de quotes inmutables Levata

## 1. Resumen ejecutivo

`Levata_ImmutableQuote` implementa una capa B2B de **quotes inmutables** sobre Magento 2.4.9 sin alterar columnas core de `quote` (**opción A2**).

Una quote Magento es **inmutable** cuando existe una fila en `quote_inmutable` con `is_locked = 1`. En runtime se expone un booleano vía extension attributes (`levata_is_immutable`), hidratado con un registro en caché de petición.

**Mejoras frente a un módulo típico de micro-plugins / repositorio incompleto:**

| Área | Mejora |
|------|--------|
| Prevención | Un solo `QuoteImmutabilityGuard` + plugins finos |
| Repositorio | Contrato completo: get / save / delete / getList(SearchCriteria) |
| Eventos | Eventos de dominio en create, lock, unlock, enable, delete, blocked |
| Auditoría | Log PSR-3 dedicado `var/log/immutable_quote_audit.log` |
| API | Superficie REST completa + rate limit + ACL granular |
| Modelo de datos | A2 no invasivo; camino documentado hacia metadata consolidada (B) |

---

## 2. Decisión de modelo de datos — **Opción A2**

### Elección

**Opción A (extender quote Magento) + almacenamiento no invasivo:**

- **Sin** columna `quote.is_immutable` (evita acoplamiento al schema core).
- Tabla `quote_inmutable` (1:1 `quote_id`, FK CASCADE).
- Fuente de verdad: `is_locked`.
- Flag API / UI: extension attributes en `CartInterface`.

### ¿Por qué no A1 (columna en `quote`)?

Un flag denormalizado es más rápido para SQL ad hoc, pero:

- Rompe el punto fuerte “no invasivo” frente a upgrades Magento.
- Empuja a ensuciar más tablas core conforme crecen las features.

La caché de petición (`ImmutableStatusRegistry`) elimina la necesidad práctica de una columna core en los caminos calientes.

### ¿Por qué no B (tabla consolidada de metadata) *en esta entrega*?

B es la respuesta de **plataforma** correcta ante la proliferación de micro-tablas, pero:

- Exige acuerdo de migración entre módulos (ExpiredQuote, CustomFee, …).
- Queda fuera del alcance de un entregable enfocado en ~16 h.

**Este módulo:** A2 ahora. **Hoja de ruta:** migrar flags a `quote_extension_metadata` (opción B) más adelante — ver §3.

### ¿Por qué no C (sistema de quotes aparte)?

La duplicación de carrito/checkout/conversión a pedido supera los beneficios en un B2B nativo Magento.

### Estrategia de migración (si se pasa de A2 → B)

1. Introducir `quote_extension_metadata` (PK `quote_id` + columnas tipadas/JSON).
2. Dual-write desde `QuoteImmutableRepository`.
3. Backfill desde `quote_inmutable`.
4. Cambiar las lecturas del registry; eliminar la tabla auxiliar.

---

## 3. Análisis de la arquitectura de micro-tablas

### Patrón habitual (problema del enunciado)

N módulos de feature × 1 tabla auxiliar × FK a `quote` ⇒ N JOINs / N cargas a repositorio por ciclo de vida de la quote.

### Valoración

Solo es sostenible con **pocas** features. Con 6–10 módulos se convierte en coste de consultas y de comprensión.

### Mitigación en esta entrega

1. **Una** tabla de metadata para inmutabilidad (A2).
2. **Una** carga de registry por `quote_id` y petición (sin N+1 dentro de plugins).
3. **Roadmap explícito a B** para metadata de toda la organización (immutable, expiry, PO, T&C, …) en una sola fila/documento JSON.
4. Modularidad por servicios PHP que escuchan eventos — no por tablas extra.

### Impacto de rendimiento (cualitativo)

| Operación | Antes (típico) | Después (este módulo) |
|-----------|----------------|------------------------|
| `isImmutable` en 7 plugins | Hasta 7 SELECTs idénticos | 1 SELECT, luego memoria |
| Listado de quotes del cliente | N lookups de estado | Oportunidad de batch; hoy carga por id, O(n) pero cacheada en la petición |
| Metadata futura de toda la org | 8–10 JOINs | 1 JOIN / 1 fila |

---

## 4. Patrones de diseño

| Patrón | Dónde | Por qué |
|--------|-------|---------|
| **Repository** | `QuoteImmutableRepository` | Persistencia estándar Magento + SearchCriteria |
| **Guard / Policy** | `QuoteImmutabilityGuard`, `PaymentMethodGuard` | Centralizar autorización de mutaciones |
| **Registry (caché de petición)** | `ImmutableStatusRegistry` | Evitar lecturas repetidas a BD |
| **Extension Attributes** | Cart + CartItem | Superficie API no invasiva |
| **Plugin (AOP)** | Adaptadores finos sobre servicios Magento | Interceptar sin reescribir core |
| **Capa de servicio** | `QuoteImmutableManagement`, `QuoteCloner`, `CustomPriceApplier` | Orquestación de negocio |
| **Factory** | Factories generados Magento | Construcción amigable con DI |

**Descartado:** CQRS pesado en 16 h; agregados DDD como estructura obligatoria (exceso para envolver quotes Magento).

---

## 5. Arquitectura de eventos

| Evento | Payload | Casos de uso |
|--------|---------|--------------|
| `levata_immutable_quote_created` | `quote`, `customer_id`, `source_quote_id` | Sync ERP, analítica |
| `levata_immutable_quote_locked` | `quote_id`, `customer_id`, `immutable` | Auditoría, webhook, email al cliente |
| `levata_immutable_quote_unlocked` | igual | Reabrir negociación |
| `levata_immutable_quote_enabled` | `quote_id`, `customer_id`, `quote` | Hooks de sesión/carrito |
| `levata_immutable_quote_deleted` | `quote_id`, `customer_id`, `was_immutable` | Limpieza de integraciones |
| `levata_immutable_quote_modification_blocked` | `quote_id`, `action` | Monitorización de seguridad |

Los listeners deben vivir en **módulos aparte** (abierto/cerrado).

---

## 6. Optimizaciones de rendimiento

1. **Registry de ámbito petición** para estado de lock + metadata.
2. **Desactivar en batch** el resto de quotes al activar (`UPDATE quote SET is_active=0 …`).
3. **Clonado sin `Quote::merge`** para evitar plugins de merge y permitir clonar orígenes ya bloqueados.
4. **Contadores de rate limit** en tabla dedicada con bloqueo de fila (`FOR UPDATE`) por consumidor/hora.
5. Índices: PK `quote_id`; índice en `is_locked`.

---

## 7. Medidas de seguridad

- **ACL:** `quote_view`, `quote_manage`, `quote_enable`, `quote_unlock`, `quote_delete`, `config`.
- **Creación solo por API** de quotes negociadas (token admin/integración).
- **Activación del cliente** solo vía POST frontend con CSRF.
- **Rate limiting** (100 req/h por defecto, configurable).
- **Validación de entrada:** ownership, precio custom negativo rechazado.
- **Encadenado de excepciones** en save/delete del repositorio (`$previous`).
- **Audit log:** quién (tipo/id de usuario), qué, quote, IP en bloqueos.
- **Interruptor en admin** para desactivar toda la funcionalidad sin borrar datos.

---

## 8. Compromisos y limitaciones

| Compromiso | Motivo |
|------------|--------|
| A2 en lugar de B ahora | Calidad entregable en 16 h; B exige migración de organización |
| El pago debe fijarse **antes del lock** | Inmutabilidad estricta también del pago; place-order reutiliza el método bloqueado |
| UX suave (CSS) en controles del carrito | El guard de backend es la autoridad; el pulido Luma es secundario |
| Tests unitarios centrados en guards/servicios | Mejor ROI frente a una suite de integración Magento completa |
| Nombre de tabla `quote_inmutable` | Ortografía intencional del diseño inicial; renombrar implica migración |

### Limitaciones conocidas / trabajo futuro

- API GraphQL
- Precarga en batch del registry en listados de cliente
- Consolidación de metadata opción B
- E2E Cypress / MFTF
- Outbox de webhooks

---

## 9. Estructura del módulo

**Módulo único** `Levata_ImmutableQuote` — dominio cohesivo para la prueba; API + frontend incluidos. Separar en paquetes Api/Frontend solo si el código crece con varios consumidores.

**Tema frontend:** Luma (esfuerzo ~20 %).

---

## 10. Reglas de negocio (acordadas)

1. Inmutable ⇔ `quote_inmutable.is_locked = 1`.
2. Creación solo por API (vacía o **clon** a quote nueva inactiva).
3. El create one-shot puede incluir `items`, `lock`, `enable`.
4. Líneas + precio custom solo mientras es mutable; después el guard bloquea todas las mutaciones.
5. Una sola quote activa por cliente (`is_active`), semántica Magento.
6. Unlock por API quita la inmutabilidad (`is_locked=0`, se conserva la fila).
7. Delete borra en duro la quote Magento; metadata CASCADE.
8. El cliente activa solo desde My Quotes en Luma.
9. Envío + pago fijos tras el lock; el pago se fija por API antes del lock.
10. Se puede desactivar toda la funcionalidad desde admin (*Enable Immutable Quotes*).
