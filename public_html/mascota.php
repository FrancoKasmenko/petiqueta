<?php
session_start();
require_once __DIR__ . '/../app/core/config.php';
require_once __DIR__ . '/../app/models/Mascota.php';

$codigo = $_GET['codigo'] ?? null;

if (!$codigo) {
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8" />
        <title>Código inválido - Petiqueta</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #fcf4e7;
                color: #7a5c39;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                flex-direction: column;
                text-align: center;
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
                margin-bottom: 1rem;
                color: #c89e6a;
            }

            p {
                font-size: 1.2rem;
                margin-bottom: 2rem;
            }

            a {
                background-color: #c89e6a;
                color: white;
                padding: 0.8rem 1.5rem;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 600;
                transition: background-color 0.3s ease;
            }

            a:hover {
                background-color: #7a5c39;
            }
        </style>
    </head>

    <body>
        <h1>Código inválido o no especificado</h1>
        <p>El código que has ingresado no es válido o no está asignado a ninguna mascota.</p>
        <a href="https://petiqueta.uy">Volver al inicio</a>
    </body>

    </html>
<?php
    exit;
}

$mascotaModel = new Mascota($pdo);

$stmtCode = $pdo->prepare("SELECT * FROM mascotag_codes WHERE code = ?");
$stmtCode->execute([$codigo]);
$codigoInfo = $stmtCode->fetch(PDO::FETCH_ASSOC);

if (!$codigoInfo) {
    // Código no válido
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8" />
        <title>Código inválido - Petiqueta</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #fcf4e7;
                color: #7a5c39;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                flex-direction: column;
                text-align: center;
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
                margin-bottom: 1rem;
                color: #c89e6a;
            }

            p {
                font-size: 1.2rem;
                margin-bottom: 2rem;
            }

            a {
                background-color: #c89e6a;
                color: white;
                padding: 0.8rem 1.5rem;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 600;
                transition: background-color 0.3s ease;
            }

            a:hover {
                background-color: #7a5c39;
            }
        </style>
    </head>

    <body>
        <h1>Código inválido o no asignado</h1>
        <p>El código que has ingresado no es válido o no está asignado a ninguna mascota.</p>
        <a href="https://petiqueta.uy">Volver al inicio</a>
    </body>

    </html>
<?php
    exit;
}

$status = $codigoInfo['status'] ?? null;

// No se puede usar si está "available"
if ($status === 'available') {
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8" />
        <title>Código no asignado - Petiqueta</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #fcf4e7;
                color: #7a5c39;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                flex-direction: column;
                text-align: center;
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
                margin-bottom: 1rem;
                color: #c89e6a;
            }

            p {
                font-size: 1.2rem;
                margin-bottom: 2rem;
            }

            a {
                background-color: #c89e6a;
                color: white;
                padding: 0.8rem 1.5rem;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 600;
                transition: background-color 0.3s ease;
            }

            a:hover {
                background-color: #7a5c39;
            }
        </style>
    </head>

    <body>
        <h1>Código aún no asignado</h1>
        <p>Este código está disponible pero no se puede usar para registrar una mascota.</p>
        <a href="https://petiqueta.uy">Volver al inicio</a>
    </body>

    </html>
<?php
    exit;
}

$mostrarFormulario = ($status === 'printed');
$mostrarPerfil = ($status === 'used');

$mascota = null;
$direccionCompleta = '';

if ($mostrarPerfil) {
    $mascota = $mascotaModel->obtenerMascotaPorCodigo($codigo);
    if (!empty($mascota['direccion'])) {
        $direccionCompleta = $mascota['direccion'];
        if (!empty($mascota['barrio'])) $direccionCompleta .= ', ' . $mascota['barrio'];
        if (!empty($mascota['departamento'])) $direccionCompleta .= ', ' . $mascota['departamento'];
    }
}


function normalizarDireccionParaGoogleMaps(string $direccion): string
{
    $buscar = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'];
    $reemplazar = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'];
    $direccion = str_replace($buscar, $reemplazar, $direccion);

    $abreviaciones = [
        'Bvar.' => 'Boulevard',
        'Bvar' => 'Boulevard',
        'Av.' => 'Avenida',
        'Av' => 'Avenida',
        'Cno.' => 'Camino',
        'Cno' => 'Camino'
    ];

    foreach ($abreviaciones as $abrev => $completo) {
        $direccion = str_ireplace($abrev, $completo, $direccion);
    }


    return trim($direccion);
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title><?= $mostrarPerfil ? "Perfil de " . htmlspecialchars($mascota['nombre']) : "Registrar Mascota" ?> - Petiqueta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
    <style>
        :root {
            --marron: #7a5c39;
            --caramelo: #c89e6a;
            --beige: #fcf4e7;
            --blanco-caldo: #fffaf3;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--beige);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: var(--marron);
        }

        main {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem 1rem;
        }

        .perfil-container {
            background: var(--blanco-caldo);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 600px;
            width: 100%;
            padding: 2rem;
        }

        h1 {
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--caramelo);
            padding-bottom: 0.5rem;
            text-align: center;
        }

        .foto-mascota {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 1.5rem auto;
            border: 4px solid var(--caramelo);
            box-shadow: 0 0 8px rgba(200, 158, 106, 0.7);
            cursor: pointer;
        }

        .datos,
        .datos-dueno {
            margin-bottom: 2rem;
        }

        .datos p,
        .datos-dueno p {
            margin: 0.3rem 0;
            font-size: 1rem;
        }

        .datos p strong,
        .datos-dueno p strong {
            color: var(--caramelo);
        }

        .contactos {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .contactos a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            color: white;
            transition: background-color 0.3s ease;
        }

        .contactos a.whatsapp {
            background-color: #25D366;
        }

        .contactos a.whatsapp:hover {
            background-color: #1DA851;
        }

        .contactos a.email {
            background-color: #0072C6;
        }

        .contactos a.email:hover {
            background-color: #005a9e;
        }

        iframe {
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border: none;
            width: 100%;
            height: 250px;
            margin-top: 1rem;
        }

        .login-prompt {
            text-align: center;
            margin-top: 2rem;
        }

        .login-prompt p {
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .login-prompt a {
            background-color: var(--caramelo);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .login-prompt a:hover {
            background-color: var(--marron);
        }
    </style>
    <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
</head>

<body>
    <?php include __DIR__ . '/../templates/navbar.php'; ?>

    <main>
        <div class="perfil-container">
            <?php if ($mostrarPerfil && $mascota): ?>
                <h1>Perfil de <?= htmlspecialchars($mascota['nombre']) ?></h1>

                <?php if ($mascota['foto_url']): ?>
                    <img src="<?= htmlspecialchars($mascota['foto_url']) ?>" alt="Foto de <?= htmlspecialchars($mascota['nombre']) ?>" class="foto-mascota" id="fotoMascota" />
                <?php endif; ?>

                <div id="modalImagen" style="display:none;position:fixed;z-index:10000;left:0;top:0;width:100vw;height:100vh;background-color:rgba(0,0,0,0.8);justify-content:center;align-items:center;">
                    <span id="modalCerrar" style="position:fixed;top:20px;right:30px;color:white;font-size:2rem;font-weight:bold;cursor:pointer;user-select:none;z-index:11000;">&times;</span>
                    <img src="" alt="Imagen ampliada" id="imagenGrande" style="max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 0 30px rgba(255,255,255,0.3);" />
                </div>

                <script>
                    const fotoMascota = document.getElementById('fotoMascota');
                    const modalImagen = document.getElementById('modalImagen');
                    const imagenGrande = document.getElementById('imagenGrande');
                    const modalCerrar = document.getElementById('modalCerrar');

                    if (fotoMascota) {
                        fotoMascota.addEventListener('click', () => {
                            imagenGrande.src = fotoMascota.src;
                            modalImagen.style.display = 'flex';
                        });
                    }
                    modalCerrar.addEventListener('click', () => {
                        modalImagen.style.display = 'none';
                    });
                    modalImagen.addEventListener('click', (e) => {
                        if (e.target === modalImagen) {
                            modalImagen.style.display = 'none';
                        }
                    });
                </script>

                <section class="datos">
                    <p><strong>Raza:</strong> <?= htmlspecialchars($mascota['raza']) ?></p>
                    <p><strong>Edad:</strong> <?= htmlspecialchars($mascota['edad']) ?> años</p>
                    <p><strong>Sexo:</strong> <?= htmlspecialchars($mascota['sexo']) ?></p>
                    <p><strong>Alergias:</strong> <?= nl2br(htmlspecialchars($mascota['alergias'] ?: 'Ninguna')) ?></p>
                    <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($mascota['descripcion'] ?: '')) ?></p>
                    <p><strong>Veterinaria Nombre:</strong> <?= htmlspecialchars($mascota['veterinaria_nombre'] ?? 'No disponible') ?></p>
                    <p><strong>Contacto Veterinaria:</strong> <?= htmlspecialchars($mascota['veterinaria_contacto'] ?? 'No disponible') ?></p>
                </section>

                <hr style="border-color: var(--caramelo);" />

                <section class="datos-dueno">
                    <h2 style="color: var(--caramelo); font-weight: 700; margin-bottom: 1rem;">Datos del dueño</h2>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($mascota['nombre_dueño'] ?? 'No disponible') ?></p>
                    <p><strong>Correo:</strong> <?= htmlspecialchars($mascota['email'] ?? 'No disponible') ?></p>
                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($mascota['telefono'] ?? 'No disponible') ?></p>
                    <?php if ($direccionCompleta): ?>
                        <?php
                        $direccionGoogleMaps = normalizarDireccionParaGoogleMaps($direccionCompleta);
                        ?>
                        <iframe
                            src="https://www.google.com/maps/embed/v1/place?key=AIzaSyDC4Vi1zdlKXB8YXk4okPo1BXYcfAk_oeI&q=<?= urlencode($direccionGoogleMaps) ?>"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            style="border:0; width: 100%; height: 250px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); margin-top: 1rem;">
                        </iframe>


                    <?php endif; ?>

                </section>

                <?php if (!empty($mascota['telefono']) || !empty($mascota['email'])): ?>
                    <section class="contactos" aria-label="Contactos del dueño">
                        <?php if (!empty($mascota['telefono'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/\D+/', '', $mascota['telefono']) ?>" target="_blank" rel="noopener noreferrer" class="whatsapp" aria-label="WhatsApp">
                                <i class="fab fa-whatsapp fa-lg"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($mascota['email'])): ?>
                            <a href="mailto:<?= htmlspecialchars($mascota['email']) ?>" class="email" aria-label="Correo electrónico">
                                <i class="fa-solid fa-envelope fa-lg"></i>
                            </a>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

            <?php elseif ($mostrarFormulario): ?>
                <h1>Registrar Mascota</h1>

<?php if (isset($_SESSION['error'])): ?>
    <div style="color: red; background: #ffe9e9; padding: 8px 15px; border-radius: 8px; margin-bottom: 15px;">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

                <form action="mascota_agregar" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="codigo_mascotag" value="<?= htmlspecialchars($codigo) ?>" />
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Nombre</label>
                        <input name="nombre" type="text" required class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Raza</label>
                        <input name="raza" type="text" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Edad (años)</label>
                        <input name="edad" type="number" min="0" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div>

                        <label class="block font-semibold mb-1" style="color: var(--marron);">Sexo</label>
                        <select name="sexo" class="w-full p-2 border border-gray-300 rounded">
                            <option value="desconocido" selected>Desconocido</option>
                            <option value="Macho">Macho</option>
                            <option value="Hembra">Hembra</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Alergias</label>
                        <textarea name="alergias" class="w-full p-2 border border-gray-300 rounded"></textarea>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Descripción</label>
                        <textarea name="descripcion" class="w-full p-2 border border-gray-300 rounded"></textarea>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Veterinaria Nombre</label>
                        <input name="veterinaria_nombre" type="text" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Contacto Veterinaria</label>
                        <input name="veterinaria_contacto" type="text" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Foto</label>
                        <input name="foto" type="file" accept="image/*" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div>
                            <label class="block font-semibold mb-1" style="color: var(--marron);">Dirección (elige una guardada)</label>
                            <select name="id_direccion" class="w-full p-2 border border-gray-300 rounded">
                                <option value="">Sin asignar</option>
                                <?php
                                $stmtDir = $pdo->prepare("SELECT id_direccion, direccion, barrio, departamento FROM direccion WHERE id_usuario = ?");
                                $stmtDir->execute([$_SESSION['user_id']]);
                                $direcciones = $stmtDir->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($direcciones as $dir) {
                                    $text = $dir['direccion'];
                                    if ($dir['barrio']) $text .= ' - ' . $dir['barrio'];
                                    if ($dir['departamento']) $text .= ', ' . $dir['departamento'];
                                    echo "<option value=\"{$dir['id_direccion']}\">" . htmlspecialchars($text) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>

                            <label class="block font-semibold mb-1" style="color: var(--marron);">O ingresa una nueva dirección</label>
                            <input name="direccion_nueva" type="text" class="w-full p-2 border border-gray-300 rounded" placeholder="Ej: Calle 123, Barrio, Ciudad" />
                        </div>
                    <?php else: ?>
                        <div>
                            <label class="block font-semibold mb-1" style="color: var(--marron);">Dirección</label>
                            <input name="direccion_nueva" type="text" class="w-full p-2 border border-gray-300 rounded" placeholder="Ej: Calle 123, Barrio, Ciudad" />
                        </div>
                    <?php endif; ?>
                    <br>
                    <hr>
                    <br>
                    <center>
                        <h2 class="text-[var(--marron)] font-semibold mb-2">Datos del dueño</h2>
                    </center>
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Nombre del dueño</label>
                        <input name="dueño_nombre" type="text" required class="w-full p-2 border border-gray-300 rounded" placeholder="Nombre completo" />
                    </div>
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Email del dueño</label>
                        <input name="dueño_email" type="email" required class="w-full p-2 border border-gray-300 rounded" placeholder="Correo electrónico" />
                    </div>
                    <div>
                        <label class="block font-semibold mb-1" style="color: var(--marron);">Teléfono del dueño</label>
                        <input name="dueño_telefono" type="tel" required class="w-full p-2 border border-gray-300 rounded" placeholder="Número de teléfono" />
                    </div>
                    <br>
                    <button type="submit" class="bg-[var(--caramelo)] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition w-full">Guardar Mascota</button>
                </form>
            <?php else: ?>
                <p>No tienes permiso para ver esta página o el código no está activo.</p>
            <?php endif; ?>
        </div>
    </main>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>

</html>