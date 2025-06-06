<?php
require_once __DIR__ . '/../models/Producto.php';

class ProductoController
{
    private $pdo;
    private $productoModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->productoModel = new Producto($pdo);
    }

    /**
     * Agrega un producto (solo admin puede)
     * $datos es un array con los campos necesarios
     * $userId es el id del usuario que ejecuta la acción (ejemplo para permisos)
     */
    public function agregarProducto($datos, $userId)
    {
        if ($userId !== 1) {  // Cambia esta lógica según tu sistema de roles
            return ['error' => 'Solo el admin puede agregar productos.'];
        }

        $sql = "INSERT INTO producto (nombre, descripcion, precio, imagen_url, stock, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);

        $exito = $stmt->execute([
            $datos['nombre'],
            $datos['descripcion'] ?? '',
            $datos['precio'],
            $datos['imagen_url'] ?? '',
            $datos['stock'] ?? 0
        ]);

        if ($exito) {
            // Si quieres asignar colores tras crear el producto
            $idProducto = $this->pdo->lastInsertId();

            if (!empty($datos['colores']) && is_array($datos['colores'])) {
                $this->asignarColores($idProducto, $datos['colores']);
            }

            return ['success' => 'Producto agregado correctamente.', 'id_producto' => $idProducto];
        } else {
            return ['error' => 'Error al agregar producto.'];
        }
    }

    public function obtenerFormas(int $idProducto): array
    {
        $stmt = $this->pdo->prepare("SELECT forma FROM producto_forma WHERE id_producto = ?");
        $stmt->execute([$idProducto]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }


    /**
     * Asigna uno o varios colores a un producto.
     * $colores es un array con ids de colores existentes.
     */
    public function asignarColores($idProducto, array $colores)
    {
        // Primero eliminar relaciones previas
        $stmtDel = $this->pdo->prepare("DELETE FROM producto_color WHERE id_producto = ?");
        $stmtDel->execute([$idProducto]);

        // Insertar nuevas relaciones
        $stmtIns = $this->pdo->prepare("INSERT INTO producto_color (id_producto, id_color) VALUES (?, ?)");
        foreach ($colores as $idColor) {
            $stmtIns->execute([$idProducto, $idColor]);
        }
    }

    // Agrega otros métodos necesarios, por ejemplo para editar, eliminar, listar productos, etc.
}
