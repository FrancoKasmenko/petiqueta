<?php
session_start();
require_once __DIR__ . '/../../app/core/config.php'; // Ajusta la ruta a tu config o conexión PDO
$rolUsuario = $_SESSION['user_rol'] ?? 'cliente';

// Validar id de producto en GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /productos'); // o donde tengas la lista general
    exit;
}




$idProducto = (int)$_GET['id'];

// Consultar producto en BD
$stmt = $pdo->prepare("SELECT * FROM producto WHERE id_producto = ?");
$stmt->execute([$idProducto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);
// Obtener tamaños disponibles para el producto actual desde producto_tamanio
$stmtTamanios = $pdo->prepare("SELECT tamanio FROM producto_tamanio WHERE id_producto = ? ORDER BY id");
$stmtTamanios->execute([$idProducto]);
$tamaniosDisponibles = $stmtTamanios->fetchAll(PDO::FETCH_COLUMN);
if (!$producto) {
    die("Producto no encontrado");
}
$rolUsuario = $_SESSION['user_rol'] ?? 'cliente';
$cantidadSeleccionada = 1; // Por defecto al cargar página

require_once __DIR__ . '/../../app/models/Producto.php';
$productoModel = new Producto($pdo);

$precioMostrar = $productoModel->obtenerPrecioSegunRolYCantidad($producto['id_producto'], $rolUsuario, $cantidadSeleccionada);

function obtenerPrecioSegunRol($producto, $rol)
{
    $precioCliente = isset($producto['precio']) ? floatval($producto['precio']) : 0;
    $precioProveedor = isset($producto['precio_proveedor']) ? floatval($producto['precio_proveedor']) : 0;

    if ($rol === 'proveedor' && $precioProveedor > 0) {
        return $precioProveedor;
    }
    return $precioCliente > 0 ? $precioCliente : 0;
}

$precioMostrar = obtenerPrecioSegunRol($producto, $rolUsuario);
$precioCliente = obtenerPrecioSegunRol($producto, "cliente");

// Consultar beneficios relacionados
$stmt = $pdo->prepare("SELECT * FROM producto_beneficios WHERE id_producto = ?");
$stmt->execute([$idProducto]);
$beneficios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultar imágenes relacionadas
$stmt = $pdo->prepare("SELECT * FROM producto_imagenes WHERE id_producto = ?");
$stmt->execute([$idProducto]);
$imagenesProducto = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Obtener formas asociadas al producto
$stmtFormas = $pdo->prepare("SELECT forma FROM producto_forma WHERE id_producto = ?");
$stmtFormas->execute([$idProducto]);
$formasDisponibles = $stmtFormas->fetchAll(PDO::FETCH_COLUMN);

// Obtener colores del producto
require_once __DIR__ . '/../../app/models/Producto.php';
$colores = $productoModel->obtenerColores($idProducto);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($producto['nombre']) ?> - Petiqueta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="icon" type="image/png" href="../assets/img/icons/favicon.png">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap"
        rel="stylesheet" />
    <style>
        body {
            font-family: "Poppins", sans-serif !important;
            background-color: #fcf4e7 !important;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: #7a5c39;
        }

        .precio-antiguo {
            text-decoration: line-through;
            color: #aaa;
        }

        .precio-nuevo {
            color: #c89e6a;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .beneficios-list {
            margin-top: 2rem;
            display: flex;
            gap: 2rem;
            justify-content: center;
        }

        .beneficio-item {
            background: #fffaf3;
            border-radius: 16px;
            padding: 1rem;
            box-shadow: 0 4px 8px rgb(200 158 106 / 0.2);
            max-width: 500px;
            text-align: center;
            color: #7a5c39;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .beneficio-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgb(200 158 106 / 0.3);
            margin-bottom: 0.8rem;
        }

        .beneficio-item h3 {
            font-weight: 700;
            margin-bottom: 0.4rem;
            color: #c89e6a;
        }

        .beneficio-item p {
            font-size: 0.95rem;
            color: #8b7153;
            margin: 0;
        }

        :root {
            --marron: #7a5c39;
            --caramelo: #c89e6a;
            --beige: #fcf4e7;
            --blanco-caldo: #fffaf3;
        }

        /* Responsive para pantallas más chicas */
        @media (max-width: 768px) {
            .beneficios-list {
                flex-direction: column;
            }

            .beneficio-item {
                max-width: 100%;
            }

            .beneficio-img {
                height: 220px;
            }
        }

        /* Contenedor padre que tiene posición relative */

        /* Solo los botones de flechas */
        button#prevImg,
        button#nextImg {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background-color: #c89e6a;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
        }

        /* Separar uno a la izquierda y otro a la derecha */
        button#prevImg {
            left: 10px;
        }

        button#nextImg {
            right: 10px;
        }

        /* Hover para mejor UX */
        button#prevImg:hover,
        button#nextImg:hover {
            background-color: #7a5c39;
        }

        /* Estilo colores */
        .colores-producto {
            margin-top: 1rem;
        }

        .colores-producto div {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid #ccc;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .colores-producto div:hover {
            transform: scale(1.2);
            border-color: var(--caramelo);
        }
    </style>

</head>

<body class="bg-[var(--beige)] min-h-screen font-sans">

    <?php include '../../templates/navbar.php';
    ?>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="flex flex-col md:flex-row gap-10">

            <!-- Galería de imágenes -->
            <div class="flex-1 max-w-md mx-auto md:mx-0">
                <div class="relative">
                    <img id="imagenPrincipal" src="../<?= htmlspecialchars($producto['imagen_url']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="rounded-xl shadow-lg object-cover w-full h-auto max-h-[400px]" />

                    <!-- Flechas izquierda/derecha -->
                    <button id="prevImg" class="btn-flecha">‹</button>
                    <button id="nextImg" class="btn-flecha">›</button>

                </div>

                <!-- Miniaturas -->
                <div class="flex gap-3 mt-4 overflow-x-auto">
                    <img src="../<?= htmlspecialchars($producto['imagen_url']) ?>" alt="Imagen principal" class="miniatura rounded-lg cursor-pointer border-2 border-[var(--caramelo)]" style="width: 80px; height: 80px; object-fit: cover;" />
                    <?php foreach ($imagenesProducto as $img): ?>
                        <img src="../<?= htmlspecialchars($img['imagen_url']) ?>" alt="Imagen adicional" class="miniatura rounded-lg cursor-pointer" style="width: 80px; height: 80px; object-fit: cover;" />
                    <?php endforeach; ?>
                </div>
            </div>



            <!-- Información del producto -->
            <div class="flex-1 max-w-lg flex flex-col justify-between">
                <div>
                    <p class="uppercase text-sm tracking-wider text-[#c89e6a] font-semibold mb-1">Stock limitado</p>
                    <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($producto['nombre']) ?></h1>

                    <div class="flex items-center gap-4 mb-6">
                        <span class="precio-antiguo">$<?php

                                                        if (isset($_SESSION["user_rol"])) {
                                                            if ($_SESSION["user_rol"] == "proveedor") {
                                                                echo number_format($precioCliente, 2, ',', '.');
                                                            } else {
                                                                echo number_format($precioMostrar * 1.2, 2, ',', '.');
                                                            }
                                                        } else {
                                                            echo number_format($precioMostrar * 1.2, 2, ',', '.');
                                                        }

                                                        ?></span>

                        <span class="precio-nuevo">$<?= number_format($precioMostrar, 2, ',', '.') ?></span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold"><?php

                                                                                    if (isset($_SESSION["user_rol"])) {
                                                                                        if ($_SESSION["user_rol"] == "proveedor") {
                                                                                            echo "PRECIO PREFERENCIAL DISTRIBUIDOR";
                                                                                        } else {
                                                                                            echo "20% Dto. - Precio variable según tamaño";
                                                                                        }
                                                                                    } else {
                                                                                        echo "20% Dto. - Precio variable según tamaño";
                                                                                    }
                                                                                    ?></span>
                    </div>



                    <p class="mb-6 leading-relaxed"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

                    <!-- Botón agregar al carrito -->
                    <form action="carrito_agregar.php" method="POST" class="mb-10">
                        <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>" />

                        <label for="tamanio" class="block mb-2 font-semibold text-[#7a5c39]">Selecciona tamaño:</label>
                        <select name="tamanio" id="tamanio" required class="mb-4 p-2 rounded border border-[#c89e6a]">
                            <option value="">-- Selecciona un tamaño --</option>
                            <?php foreach ($tamaniosDisponibles as $tamanio): ?>
                                <option value="<?= htmlspecialchars($tamanio) ?>"><?= htmlspecialchars($tamanio) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label for="cantidad" class="block mb-2 font-semibold text-[#7a5c39]">Cantidad:</label>
                        <input
                            type="number"
                            name="cantidad"
                            id="cantidad"
                            min="1"
                            value="1"
                            required
                            class="mb-4 p-2 rounded border border-[#c89e6a]"
                            step="1" />



                        <?php if (!empty($colores)): ?>
                            <!-- Dentro del formulario de compra -->

                            <br><label>Colores disponibles (seleccioná hasta 2):</label>
                            <div id="coloresCliente" class="d-flex gap-2 flex-wrap">
                                <?php foreach ($colores as $color): ?>
                                    <label style="cursor:pointer;">
                                        <input type="checkbox" name="colores[]" value="<?= $color['id_color'] ?>" />
                                        <span style="display:inline-block; width: 24px; height: 24px; background: <?= htmlspecialchars($color['codigo_hex']) ?>; border-radius: 50%; border: 1px solid #ccc;"></span>
                                        <?= htmlspecialchars($color['nombre']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if (!empty($formasDisponibles)): ?>
                                <label for="forma" class="block mb-2 font-semibold text-[#7a5c39]">Selecciona forma:</label>
                                <select name="forma" id="forma" required class="mb-4 p-2 rounded border border-[#c89e6a]">
                                    <option value="">-- Selecciona una forma --</option>
                                    <?php foreach ($formasDisponibles as $forma): ?>
                                        <option value="<?= htmlspecialchars($forma) ?>"><?= htmlspecialchars($forma) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>

                            <script>
                                const form = document.querySelector('form'); // Asumí que el form es el único o poné el selector correcto
                                form.addEventListener('submit', function(e) {
                                    const checkedColors = document.querySelectorAll('input[name="colores[]"]:checked');
                                    if (checkedColors.length === 0) {
                                        e.preventDefault();
                                        alert('Por favor seleccioná al menos un color.');
                                    } else if (checkedColors.length > 1) {
                                        e.preventDefault();
                                        alert('Solo podés seleccionar 1 color (La huella es color blanco)');
                                    }
                                });

                                // Limitar selección a máximo 2
                                const checkboxes = document.querySelectorAll('input[name="colores[]"]');
                                checkboxes.forEach(chk => {
                                    chk.addEventListener('change', () => {
                                        const checked = document.querySelectorAll('input[name="colores[]"]:checked');
                                        if (checked.length > 1) {
                                            alert('Solo podés seleccionar 1 color (La huella es color blanco)');
                                            chk.checked = false;
                                        }
                                    });
                                });
                            </script>

                        <?php endif; ?>

                        <input type="hidden" id="colorSeleccionado" name="color" value="" required />

                        <button type="submit" class="bg-[#c89e6a] text-white rounded-lg py-3 px-6 font-bold hover:bg-[#7a5c39] transition w-full max-w-xs">Añadir al carrito</button>
                    </form>

                </div>

                <!-- Sección extra: garantías, preguntas, etc -->
                <div>
                    <h2 class="font-semibold text-xl mb-3">Garantía y devolución</h2>
                    <p class="mb-3">Garantía de 6 meses sin preocupaciones.</p>
                    <h2 class="font-semibold text-xl mb-3">Pago seguro</h2>
                    <p>Pago 100% encriptado con SSL.</p>
                </div>
            </div>
            <!-- Mostrar beneficios -->

        </div>

    </main>
    <?php if ($beneficios): ?>
        <section class="beneficios-list">
            <?php foreach ($beneficios as $b): ?>
                <article class="beneficio-item">
                    <?php if (!empty($b['imagen_url'])): ?>
                        <img src="../assets/img/productos/<?= htmlspecialchars($b['imagen_url']) ?>" alt="<?= htmlspecialchars($b['titulo']) ?>" class="beneficio-img" />
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($b['titulo']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($b['descripcion'])) ?></p>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <br>
    <?php include '../../templates/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const imagenPrincipal = document.getElementById('imagenPrincipal');
            const miniaturas = document.querySelectorAll('.miniatura');

            let indexActual = 0;
            const imagenes = Array.from(miniaturas).map(img => img.src);

            function mostrarImagen(i) {
                if (i < 0) i = imagenes.length - 1;
                if (i >= imagenes.length) i = 0;
                indexActual = i;
                imagenPrincipal.src = imagenes[i];
                miniaturas.forEach((mini, idx) => {
                    mini.style.borderColor = idx === i ? 'var(--caramelo)' : 'transparent';
                });
            }

            miniaturas.forEach((mini, idx) => {
                mini.addEventListener('click', () => mostrarImagen(idx));
            });

            document.getElementById('prevImg').addEventListener('click', () => mostrarImagen(indexActual - 1));
            document.getElementById('nextImg').addEventListener('click', () => mostrarImagen(indexActual + 1));

            mostrarImagen(0);

            // Manejo selección color
            const divsColores = document.querySelectorAll('.colores-producto div');
            const inputColor = document.getElementById('colorSeleccionado');

            divsColores.forEach(div => {
                div.addEventListener('click', () => {
                    // Deseleccionar todos
                    divsColores.forEach(d => d.style.outline = 'none');
                    // Seleccionar el clickeado
                    div.style.outline = '3px solid var(--caramelo)';
                    inputColor.value = div.getAttribute('data-color');
                });

                // Accesibilidad con teclado
                div.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        div.click();
                    }
                });
            });
        });
    </script>

</body>

</html>