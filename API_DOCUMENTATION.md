# Documentación API — Levata Immutable Quotes

Prefijo base: `/rest/<store_code>/V1` (o `/rest/V1`).

**Autenticación:** token de admin o OAuth de Integration. Los recursos ACL se indican en cada ruta.

**Límite de peticiones:** 100 req/hora/consumidor por defecto (configurable). Si se supera, falla con `AuthorizationException`.

**Interruptor maestro:** si *Enable Immutable Quotes* está desactivado en admin, estas APIs rechazan las peticiones.

---

## Crear quote (vacía, clon, lock/enable/items opcionales)

`POST /V1/immutable-quotes/quotes`  
**ACL:** `Levata_ImmutableQuote::quote_manage`

```json
{
  "request": {
    "customer_id": 12,
    "store_id": 1,
    "source_quote_id": null,
    "items": [
      {
        "sku": "24-MB01",
        "qty": 2,
        "extension_attributes": {
          "levata_custom_price": 49.5
        }
      }
    ],
    "lock": true,
    "enable": false,
    "internal_reference": "PO-7781",
    "notes": "Negociado Q3"
  }
}
```

**Clonar:** indica `source_quote_id` de una quote del mismo cliente. Crea una quote **nueva e inactiva**; la origen no se modifica.

**Orden de operaciones:** create/clone → items → lock → enable.

**Respuesta:** `CartInterface` con extension attributes:

- `levata_is_immutable`
- `levata_immutable_locked_at`
- `levata_immutable_internal_reference`

---

## Obtener quote

`GET /V1/immutable-quotes/quotes/:quoteId`  
**ACL:** `Levata_ImmutableQuote::quote_view`

---

## Listar quotes del cliente

`GET /V1/customers/:customerId/immutable-quotes`  
**ACL:** `Levata_ImmutableQuote::quote_view`

Devuelve carritos activos e inactivos con los extension attributes de inmutabilidad hidratados.

---

## Bloquear quote (lock)

`POST /V1/immutable-quotes/quotes/:quoteId/lock`  
**ACL:** `Levata_ImmutableQuote::quote_manage`

```json
{
  "lockRequest": {
    "internal_reference": "PO-7781",
    "notes": "No cambiar el envío"
  }
}
```

`lockRequest` es opcional.

---

## Desbloquear quote (unlock)

`POST /V1/immutable-quotes/quotes/:quoteId/unlock`  
**ACL:** `Levata_ImmutableQuote::quote_unlock`

Pone `is_locked = 0` (la fila se conserva por auditoría).

---

## Activar quote (enable)

`POST /V1/immutable-quotes/quotes/:quoteId/enable`  
**ACL:** `Levata_ImmutableQuote::quote_enable`

Desactiva el resto de quotes del cliente (`is_active = 0`) y deja esta como activa.

> El cliente activa desde el storefront (My Quotes); este endpoint es para admin/integración.

---

## Añadir / actualizar línea (solo mientras es mutable — antes del lock)

`POST /V1/immutable-quotes/quotes/:quoteId/items`  
**ACL:** `Levata_ImmutableQuote::quote_manage`

```json
{
  "cartItem": {
    "sku": "24-MB01",
    "qty": 1,
    "quote_id": 0,
    "extension_attributes": {
      "levata_custom_price": 40
    }
  }
}
```

El `quote_id` del body lo sobrescribe el parámetro de la URL.

---

## Fijar pago (solo mutable — hacerlo antes del lock)

`POST /V1/immutable-quotes/quotes/:quoteId/payment-information`  
**ACL:** `Levata_ImmutableQuote::quote_manage`

```json
{
  "paymentMethod": {
    "method": "checkmo"
  }
}
```

Tras el lock no se puede cambiar el pago. El place order debe reutilizar el método bloqueado.

---

## Eliminar quote

`DELETE /V1/immutable-quotes/quotes/:quoteId`  
**ACL:** `Levata_ImmutableQuote::quote_delete`

Borra en duro la quote de Magento; `quote_inmutable` se elimina por FK CASCADE.

---

## Comportamiento ante errores

| Situación | Resultado |
|-----------|-----------|
| Modificar quote bloqueada | `LocalizedException` — mensaje accionable (My Quotes / soporte) |
| Límite de rate excedido | Error de autorización con el texto del límite |
| Quote bloqueada sin pago en checkout | Error localizado: fijar pago antes del lock o desbloquear |
| Precio custom &lt; 0 | `LocalizedException` |
| Funcionalidad desactivada en admin | `LocalizedException` indicando que hay que habilitarla |

El mapeo HTTP sigue Magento Web API (normalmente 400 en errores de negocio, 401/403 auth, 404 entidad inexistente).
