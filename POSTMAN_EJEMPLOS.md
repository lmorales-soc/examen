# Ejemplos de requests para Postman – API Orders

Base URL: `http://tu-dominio.local/api` (ajusta según tu entorno).

---

## 1. Listar órdenes

- **Método:** GET  
- **URL:** `{{base}}/orders`  
- **Body:** ninguno  

---

## 2. Crear orden

- **Método:** POST  
- **URL:** `{{base}}/orders`  
- **Headers:** `Content-Type: application/json`  
- **Body (raw JSON):**

```json
{
    "cliente": "Juan Pérez",
    "producto": "Laptop",
    "cantidad": 2,
    "precio_unitario": 599.99
}
```

*(El total y la fecha se calculan en el servidor.)*

---

## 3. Obtener orden por ID

- **Método:** GET  
- **URL:** `{{base}}/orders/1`  
- **Body:** ninguno  

---

## 4. Cancelar orden

- **Método:** PATCH  
- **URL:** `{{base}}/orders/1/cancel`  
- **Body:** ninguno (opcional body vacío `{}`)  

---

## Respuestas de ejemplo

**201 – Orden creada:**

```json
{
    "id": 1,
    "cliente": "Juan Pérez",
    "producto": "Laptop",
    "cantidad": 2,
    "precio_unitario": "599.99",
    "fecha": "2025-03-13",
    "total": "1199.98",
    "estado": "Pendiente",
    "created_at": "...",
    "updated_at": "..."
}
```

**422 – Validación (ej. misma orden por cliente y día):**

```json
{
    "errors": {
        "cliente": ["El cliente no puede tener más de una orden en el mismo día."]
    }
}
```

**422 – No se puede cancelar (orden Procesada):**

```json
{
    "message": "No se puede cancelar una orden que ya está Procesada."
}
```

**404 – Orden no encontrada:**

```json
{
    "message": "Orden no encontrada."
}
```
