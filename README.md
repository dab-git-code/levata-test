# Levata Immutable Quote — README

Módulo Magento 2.4.x para **quotes inmutables B2B**: las integraciones de ventas crean y negocian quotes vía REST; el cliente las lista, activa y hace checkout sin modificar las quotes bloqueadas.

## Requisitos

- Magento Open Source / Adobe Commerce 2.4.9+ (probado en 2.4.9)
- PHP 8.1+
- Tema Luma (frontend)

## Instalación

```bash
# Ruta del módulo
app/code/Levata/ImmutableQuote

bin/magento module:enable Levata_ImmutableQuote
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

Asigna los recursos ACL al rol de integración/admin en **Levata Immutable Quotes**.

## Configuración

**Tiendas → Configuración → Ventas → Immutable Quotes**

- **Enable Immutable Quotes** (interruptor maestro; por defecto: Sí)
- Activar rate limiting (por defecto: sí)
- Máximo de peticiones por hora (por defecto: 100)
- Activar auditoría (por defecto: sí) → `var/log/immutable_quote_audit.log`

Cuando el interruptor maestro está en **No**:

- No se aplican los locks (las quotes se comportan como mutables)
- Se ocultan el menú My Quotes y los avisos
- Los endpoints REST de Immutable Quote rechazan las peticiones con un error claro
- Se conservan las filas de `quote_inmutable` (al reactivar se recupera el comportamiento)

## Flujo API típico

1. `POST /V1/immutable-quotes/quotes` — crear vacía o clonar (`source_quote_id`), con `items` + `lock` + `enable` opcionales
2. O por pasos: añadir ítems → fijar pago → lock → enable
3. El cliente abre **Mi cuenta → My Quotes**, activa y hace checkout

Consulta [API_DOCUMENTATION.md](./API_DOCUMENTATION.md).

## Frontend

- Enlace en la cuenta del cliente: **My Quotes** (`/immutablequote/quotes/index`)
- Carrito activo bloqueado: banner de aviso + qty/cupón/eliminar deshabilitados por CSS; el guard de backend impone las reglas

## Postman

Colección lista en la cuenta **david.arteaga.blazquez@gmail.com**:

- Workspace: *David Arteaga Blazquez's Workspace*
- Collection: **Levata Immutable Quote**
- UID: `58462847-b5449693-1a37-426c-a6c9-2009528947e3`
- Copia local: [`postman/Levata_Immutable_Quote.postman_collection.json`](./postman/Levata_Immutable_Quote.postman_collection.json)

Variables a ajustar: `base_url`, `admin_username`, `admin_password`, `customer_id`, `sku`.

Orden recomendado en la carpeta **2. Stepwise happy path**: token → create → item → **enable** → direcciones (core Magento) → payment → lock.

Las direcciones usan endpoints del core (`/V1/carts/:id/shipping-information` y `/billing-address`): requieren carrito **activo** (`Enable` antes) y quedan bloqueadas tras `Lock`. Reimporta la colección local si la cloud aún no las tiene.

```bash
cd /var/www/html/levata
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist \
  app/code/Levata/ImmutableQuote/Test/Unit
```

## Documentación

- [ARCHITECTURE.md](./ARCHITECTURE.md) — decisiones y análisis de micro-tablas
- [IMPROVEMENTS.md](./IMPROVEMENTS.md) — por qué supera a un módulo solo de plugins finos
- [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) — endpoints

## Licencia

Propietaria — uso de evaluación / proyecto según lo acordado con Levata.
