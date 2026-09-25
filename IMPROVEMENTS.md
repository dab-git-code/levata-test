# IMPROVEMENTS.md — Por qué esta solución es superior

Mapeado a las debilidades del brief de evaluación.

| Debilidad | Estado típico anterior | Mejora Levata | Evidencia |
|-----------|------------------------|---------------|-----------|
| Repositorio incompleto | Solo get/save | Repositorio completo + `getList` con SearchCriteria, delete | `QuoteImmutableRepository` |
| Sin eventos de dominio | Cero dispatch | Eventos en create/lock/unlock/enable/delete/blocked | `QuoteImmutableManagement`, `QuoteImmutabilityGuard` |
| Excepciones tragadas | `throw new X(__($e))` | Encadenado con `$previous` en fallos de save/delete/clone | Repositorio + `QuoteCloner` |
| Proliferación de plugins | 7 comprobaciones `isImmutable` independientes | Guard central; plugins de una línea | `QuoteImmutabilityGuard` |
| Sin pista de auditoría | Ninguna | Audit log estructurado + contexto de usuario + IP en bloqueos | `AuditLogger`, `immutable_quote_audit.log` |
| Sin caché | Recálculo en cada llamada | `ImmutableStatusRegistry` de ámbito petición | Model registry |
| API limitada | 2 endpoints | Create/get/list/lock/unlock/enable/items/payment/delete | `webapi.xml` |
| Sin rate limiting | Ilimitado | Limitador horario configurable | `RateLimiter` + system.xml |
| Feedback UX pobre | Errores genéricos | Excepción accionable + banner My Quotes + CSS del carrito | Mensaje del guard + plantillas Luma |
| Micro-tablas | Patrón sin cuestionar | A2 justificada + hoja de ruta a metadata consolidada (B) | `ARCHITECTURE.md` §2–3 |
| Integridad de pago / envío | A menudo olvidada | Envío bloqueado; pago congelado tras el lock | `PaymentMethodGuard` + plugins de shipping |
| Riesgo al crear multi-quote | `createEmptyCartForCustomer` reutiliza el carrito activo | Siempre quote nueva inactiva / clon explícito | `QuoteCloner` |
| Precios negociados | Solo catálogo | Extension attribute `levata_custom_price` | `CustomPriceApplier` |

## Ganancias medibles / observables

1. **Mantenimiento de plugins:** los cambios de política de mutación viven en **una sola clase**.
2. **Lecturas a BD para comprobar lock:** **1 por quote id y petición**, independientemente del número de plugins.
3. **Completitud de la API:** las integraciones pueden automatizar el ciclo de vida completo sin UI de admin.
4. **Seguridad:** ACL partido + rate limit + auditoría de modificaciones bloqueadas.
5. **Correctitud en checkout:** pago/envío bloqueados evitan deriva silenciosa de precio/condiciones en el paso de pago.

## Qué se decidió no sobre-construir

- Plataforma completa de metadata opción B (documentada, no codificada).
- GraphQL / CQRS / outbox de webhooks (bonus; fuera del time-box).
- Afirmar 80 % de cobertura sin puerta de CI — los tests unitarios priorizan guards y servicios críticos.
