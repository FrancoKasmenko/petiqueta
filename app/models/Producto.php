<?php
class Producto
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function agregar($datos)
    {
        $sql = "INSERT INTO producto (nombre, descripcion, precio, imagen_url, stock, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $datos['nombre'],
            $datos['descripcion'] ?? null,
            $datos['precio'],
            $datos['imagen_url'] ?? null,
            $datos['stock'] ?? 0
        ]);
    }

    // Obtiene los colores asignados a un producto
    public function obtenerColores($id_producto)
    {
        $stmt = $this->pdo->prepare("
            SELECT c.id_color, c.nombre, c.codigo_hex
            FROM color c
            INNER JOIN producto_color pc ON c.id_color = pc.id_color
            WHERE pc.id_producto = ?
        ");
        $stmt->execute([$id_producto]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtiene el precio según rol y cantidad para un producto dado
    public function obtenerPrecioSegunRolYCantidad(int $idProducto, string $rol, int $cantidad): float
    {
        // Consultar precios base
        $stmt = $this->pdo->prepare("SELECT precio, precio_proveedor FROM producto WHERE id_producto = ?");
        $stmt->execute([$idProducto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            return 0;
        }

        $precioCliente = floatval($producto['precio']);
        $precioProveedorBase = floatval($producto['precio_proveedor']);

        // Si no es proveedor o no hay precio especial para proveedor, se devuelve el precio para cliente
        if ($rol !== 'proveedor' || $precioProveedorBase <= 0) {
            return $precioCliente;
        }

        // Consultar precios escalonados por cantidad para ese producto y rol "proveedor"
        $stmt = $this->pdo->prepare("
            SELECT precio FROM producto_precio_por_cantidad
            WHERE id_producto = ? AND rol = ? AND cantidad_min <= ? AND cantidad_max >= ?
            ORDER BY cantidad_min DESC LIMIT 1
        ");
        $stmt->execute([$idProducto, $rol, $cantidad, $cantidad]);
        $precioEscalonado = $stmt->fetchColumn();

        if ($precioEscalonado !== false && floatval($precioEscalonado) > 0) {
            return floatval($precioEscalonado);
        }

        // Si no existe precio escalonado válido, devolver precio base proveedor
        return $precioProveedorBase;
    }
    public function obtenerPrecioSegunTamanio(int $idProducto, string $tamanio): float
    {
        $stmt = $this->pdo->prepare("SELECT precio FROM producto_tamanio WHERE id_producto = ? AND tamanio = ?");
        $stmt->execute([$idProducto, $tamanio]);
        $precioTamanio = $stmt->fetchColumn();

        if ($precioTamanio !== false) {
            return floatval($precioTamanio);
        }

        // Si no hay precio para el tamaño, devolver precio base del producto
        $stmtBase = $this->pdo->prepare("SELECT precio FROM producto WHERE id_producto = ?");
        $stmtBase->execute([$idProducto]);
        $precioBase = $stmtBase->fetchColumn();

        return $precioBase !== false ? floatval($precioBase) : 0;
    }

    public function obtenerFormas(int $idProducto): array
    {
        $stmt = $this->pdo->prepare("SELECT forma FROM producto_forma WHERE id_producto = ?");
        $stmt->execute([$idProducto]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
