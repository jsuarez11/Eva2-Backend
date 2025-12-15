# Plan de Implementación: Controlador de Favoritos

Este plan detalla los pasos para implementar la lógica de favoritos en `suarezEva2`, permitiendo a los usuarios autenticados agregar, eliminar y ver sus productos favoritos.

## 1. Crear el Controlador `FavoriteController`

Generaremos un nuevo controlador en `app/Http/Controllers/FavoriteController.php`.

### Métodos Propuestos:

*   **`index(Request $request)`**:
    *   **Propósito**: Listar todos los productos que el usuario autenticado ha marcado como favoritos.
    *   **Lógica**: Utilizar la relación `$request->user()->favourites()->get()` (o paginado).
    *   **Respuesta**: JSON con la lista de productos y código 200.

*   **`store(Request $request)`** (Alternativa: `toggle`):
    *   **Propósito**: Agregar un producto a favoritos.
    *   **Validación**: Verificar que el `product_id` exista en la tabla `products`.
    *   **Lógica**: Usar `$request->user()->favourites()->attach($product_id)` o `syncWithoutDetaching`. Verificar si ya existe evitar duplicados (o manejarlo con `toggle`).
    *   **Respuesta**: Mensaje de éxito.

*   **`destroy(Request $request, $productId)`**:
    *   **Propósito**: Eliminar un producto de favoritos.
    *   **Lógica**: Usar `$request->user()->favourites()->detach($productId)`.
    *   **Respuesta**: Mensaje de éxito.

*   **Opción `toggle(Request $request)`** (Recomendada para UI moderna):
    *   **Propósito**: Agregar si no existe, eliminar si existe. Simplifica la lógica del frontend.
    *   **Lógica**: `$request->user()->favourites()->toggle($product_id)`.

## 2. Definir Rutas en `routes/api.php`

Las rutas deben estar protegidas por el middleware `auth:sanctum` para asegurar que solo usuarios logueados accedan.

```php
Route::middleware("auth:sanctum")->group(function () {
    Route::get("/favorites", [FavoriteController::class, "index"]);
    Route::post("/favorites/toggle", [FavoriteController::class, "toggle"]); // O store/destroy separados
    // Si usamos store/destroy
    // Route::post("/favorites", [FavoriteController::class, "store"]);
    // Route::delete("/favorites/{product_id}", [FavoriteController::class, "destroy"]);
});
```

## 3. Pasos de Ejecución

1.  Crear el archivo `app/Http/Controllers/FavoriteController.php` con la estructura básica y los métodos (`index` y `toggle` es lo más eficiente).
2.  Importar los modelos necesarios (`User`, `Product`).
3.  Registrar las rutas en `routes/api.php`.
4.  (Opcional) Verificar si se requiere Validación extra.
