<?php
session_start();
$rolUsuario = $_SESSION['user_rol'] ?? 'cliente';
require_once '../app/core/config.php'; 

$stmt = $pdo->query("SELECT * FROM producto WHERE id_producto != 2");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Productos - Petiqueta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://petiqueta.uy/assets/css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            font-family: "Poppins", sans-serif !important;
            background-color: #fcf4e7;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --marron: #7a5c39;
            --caramelo: #c89e6a;
            --beige: #fcf4e7;
            --blanco-caldo: #fffaf3;
        }

        .line-clamp-3 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
    </style>
    <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
</head>

<body class="bg-[var(--beige)] min-h-screen font-sans">

    <?php include '../templates/navbar.php'; ?>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-4xl font-extrabold mb-12 text-center text-[var(--marron)]">Todos los Productos</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-10">
            <?php
            function precioSegunRol($producto, $rol)
            {
                if ($rol === 'proveedor' && !empty($producto['precio_proveedor']) && $producto['precio_proveedor'] > 0) {
                    return $producto['precio_proveedor'];
                }
                return $producto['precio'];
            }
            foreach ($productos as $producto):
                $precioMostrar = precioSegunRol($producto, $rolUsuario);
                $tieneDescuento = !empty($producto['precio_descuento']) && $producto['precio_descuento'] < $producto['precio'];
                $precioOriginal = number_format($precioMostrar, 2, ',', '.');
                $precioDescuento = $tieneDescuento ? number_format($producto['precio_descuento'], 2, ',', '.') : null;
            ?>
                <div class="bg-white rounded-3xl shadow-md hover:shadow-lg transition p-6 flex flex-col">
                    <div class="relative">
                        <img
                            src="<?= BASE_URL ?><?= htmlspecialchars($producto['imagen_url']) ?>"
                            alt="<?= htmlspecialchars($producto['nombre']) ?>"
                            class="rounded-2xl w-full h-48 object-cover" />
                        <?php if ($tieneDescuento): ?>
                            <?php
                            $dto = round(100 * ($producto['precio'] - $producto['precio_descuento']) / $producto['precio']);
                            ?>
                            <div class="absolute top-3 left-3 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-lg shadow">
                                <?= $dto ?>% Dto.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex-1 flex flex-col justify-between" style="margin-top: 10px;">
                        <div>
                            <p class="text-xs font-semibold uppercase text-[var(--marron)] opacity-70 mb-1">Petiqueta</p>
                            <a href="productos/producto?id=<?= htmlspecialchars($producto['id_producto']) ?>" class="block">
                                <h2 class="font-semibold text-lg text-[var(--marron)] mb-3"><?= htmlspecialchars($producto['nombre']) ?></h2>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?= htmlspecialchars($producto['descripcion']) ?></p>
                            </a>
                            
                        </div>


                        <div>
                            <?php if ($tieneDescuento): ?>
                                <p class="text-sm text-gray-500 line-through mb-1">$<?= $precioOriginal ?></p>
                                <p class="text-[var(--caramelo)] font-bold text-lg mb-4">$<?= $precioDescuento ?></p>
                            <?php else: ?>
                                <p class="text-[var(--marron)] font-bold text-lg mb-4">$<?= $precioOriginal ?></p>
                            <?php endif; ?>

                            <a href="productos/producto?id=<?= htmlspecialchars($producto['id_producto']) ?>" class="inline-block px-4 py-2 bg-[var(--caramelo)] text-white rounded-lg font-semibold hover:bg-[var(--marron)] transition">
                                Ver Producto
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php include '../templates/footer.php'; ?>
</body>

</html>
</html>