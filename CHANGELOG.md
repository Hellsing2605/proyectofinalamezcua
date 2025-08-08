## [1.0.1] - 2025-08-08
### Cambios
- Corrección de errores menores en el módulo de búsqueda de productos.
- Mejora en la interfaz del menú de usuario para evitar superposición con la barra de búsqueda.
- Optimización de consultas SQL para carga más rápida en `products.php`.
- Actualización de configuración de conexión a base de datos para entorno Docker.

### Notas
- Esta versión es compatible con MySQL 5.7 y PHP 7.4.
- Se recomienda ejecutar `docker-compose down && docker-compose up -d --build` para actualizar la imagen.
