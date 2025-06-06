<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

require_once __DIR__ . '/../app/core/config.php';
require_once __DIR__ . '/../app/controllers/DireccionController.php';

$direccionController = new DireccionController($pdo);
$direcciones = $direccionController->obtenerDirecciones($_SESSION['user_id']);
$userId = $_SESSION['user_id'];

// --- Obtener datos usuario
$stmt = $pdo->prepare("
    SELECT u.nombre, u.email, u.telefono,
           d.direccion, d.departamento, d.barrio
    FROM usuario u
    LEFT JOIN direccion d ON d.id_usuario = u.id_usuario
    WHERE u.id_usuario = ?
    ORDER BY d.id_direccion ASC
    LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


// --- Obtener órdenes del usuario logueado
$stmtOrdenes = $pdo->prepare("
    SELECT id_orden, total, estado, fecha_creacion, metodo_pago, metodo_entrega
    FROM orden
    WHERE id_usuario = ?
    ORDER BY fecha_creacion DESC
");
$stmtOrdenes->execute([$userId]);
$ordenesUsuario = $stmtOrdenes->fetchAll(PDO::FETCH_ASSOC);


// --- Obtener código Mascotag asignado
$stmt = $pdo->prepare("SELECT code FROM mascotag_codes WHERE assigned_to_user = ?");
$stmt->execute([$userId]);
$codigoMascotag = $stmt->fetchColumn() ?: 'No asignado';

// --- Obtener mascotas asociadas con su dirección
$stmt = $pdo->prepare("
    SELECT m.*, d.direccion, d.barrio, d.departamento FROM mascota m
LEFT JOIN direccion d ON m.id_direccion = d.id_direccion
WHERE m.id_usuario = ?

");
$stmt->execute([$userId]);
$mascotas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Perfil - Petiqueta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
</head>

<body class="bg-[var(--beige)] min-h-screen font-sans flex flex-col">
    <style>
        :root {
      --marron: #7a5c39;
      --caramelo: #c89e6a;
      --beige: #fcf4e7;
      --blanco-caldo: #fffaf3;
    }
@media (max-width: 900px) {
  main.flex-grow.max-w-4xl {
    padding: 1.5rem !important;
    margin-top: 1.5rem !important;
    margin-bottom: 1.5rem !important;
    border-radius: 16px !important;
  }
  .grid.md\:grid-cols-2,
  .grid.grid-cols-1.md\:grid-cols-2 {
    grid-template-columns: 1fr !important;
    gap: 1.2rem !important;
  }
  .md\:col-span-2 {
    grid-column: span 1 !important;
    width: 100% !important;
  }
  button[type="submit"].md\:col-span-2,
  .mb-6.text-center button,
  #abrirModalMascota {
    width: 100% !important;
    max-width: none !important;
    margin: 1rem 0 0 0 !important;
  }
  section.mb-6.text-center {
    margin-bottom: 1.5rem !important;
  }
  .flex.flex-col.gap-6 {
    gap: 1rem !important;
  }
  .grid.grid-cols-1.md\:grid-cols-2.gap-6 {
    grid-template-columns: 1fr !important;
    gap: 1rem !important;
  }
  section.mt-16,
  section.mt-10 {
    margin-top: 2rem !important;
  }
}

@media (max-width: 650px) {
  main.flex-grow.max-w-4xl {
    padding: 0.7rem !important;
    border-radius: 12px !important;
  }
  h1.text-4xl {
    font-size: 1.5rem !important;
    margin-bottom: 1rem !important;
    text-align: center !important;
  }
  h2.text-3xl {
    font-size: 1.15rem !important;
    margin-bottom: 1rem !important;
  }
  table.min-w-full {
    font-size: 0.92rem !important;
  }
  .overflow-x-auto {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
  }
  .rounded-3xl,
  .rounded-xl {
    border-radius: 12px !important;
  }
  .modal > div,
  .modal .bg-\[var\(--blanco-caldo\)\] {
    padding: 1.1rem !important;
    max-width: 98vw !important;
    border-radius: 10px !important;
  }
  .btnMostrarDatos,
  .btnVerDetalleOrden,
  #abrirModalDirecciones,
  #abrirModalMascota {
    width: 100% !important;
    display: block !important;
    margin: 0.7rem 0 0 0 !important;
    padding: 0.85rem 0 !important;
    font-size: 1.07rem !important;
  }
  .flex.items-center.justify-between {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 0.8rem !important;
  }
}

@media (max-width: 500px) {
  main.flex-grow.max-w-4xl {
    padding: 0.5rem !important;
    border-radius: 7px !important;
  }
  h1, h2, h3 {
    font-size: 1rem !important;
    text-align: left !important;
  }
  .modal > div,
  .modal .bg-\[var\(--blanco-caldo\)\] {
    padding: 0.6rem !important;
    border-radius: 8px !important;
  }
  table th, table td {
    padding-left: 0.15rem !important;
    padding-right: 0.15rem !important;
  }
  .rounded-lg {
    border-radius: 8px !important;
  }
}
</style>

    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 9999;
        }

        .modal>div {
            max-width: 600px;
            width: 90%;
            margin: 0 20px;
            max-height: 90vh;
            overflow-y: auto;
            background-color: var(--blanco-caldo);
            padding: 24px;
            border-radius: 24px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }


        .modal.active {
            opacity: 1;
            pointer-events: auto;
        }
        

@media (max-width: 650px) {
    html, body {
  width: 100vw !important;
  overflow-x: hidden !important;
  box-sizing: border-box !important;
}
main.flex-grow.max-w-4xl,
main.max-w-4xl {
  max-width: 100vw !important;
  width: 100vw !important;
  box-sizing: border-box !important;
  margin: 0 auto !important;
}
  main.flex-grow.max-w-4xl,
  main.max-w-4xl {
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
  }
  body {
    padding: 0 !important;
  }
  .rounded-3xl,
  .rounded-xl,
  .rounded-lg {
    border-radius: 10px !important;
  }
  .shadow-md,
  .shadow-lg {
    box-shadow: 0 1px 8px #c6c6c688 !important;
  }
  input, button, select, textarea {
    font-size: 1rem !important;
  }
}

        
        
    </style>


    <?php include '../templates/navbar.php'; ?>

    <main class="flex-grow max-w-4xl mx-auto p-8 bg-[var(--blanco-caldo)] rounded-3xl shadow-md mt-12 mb-12">
        <h1 class="text-4xl font-extrabold mb-8 text-[var(--marron)] text-center">Perfil de Usuario</h1>
        <?php if (isset($_SESSION['error_perfil'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_perfil'] ?></div>
            <?php unset($_SESSION['error_perfil']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_perfil'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje_perfil'] ?></div>
            <?php unset($_SESSION['mensaje_perfil']); ?>
        <?php endif; ?>

        <form action="perfil_guardar" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="flex flex-col gap-6">
                <label class="flex flex-col text-[var(--marron)] font-semibold">
                    Nombre
                    <input type="text" name="nombre" required
                        value="<?= htmlspecialchars($user['nombre']) ?>"
                        class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                </label>

                <label class="flex flex-col text-[var(--marron)] font-semibold">
                    Email
                    <input type="email" name="email" required
                        value="<?= htmlspecialchars($user['email']) ?>"
                        class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                </label>

                <label class="flex flex-col text-[var(--marron)] font-semibold">
                    Teléfono
                    <input type="tel" name="telefono" required
                        value="<?= htmlspecialchars($user['telefono']) ?>"
                        class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                </label>
                
                

            </div>

            <div class="flex flex-col gap-6">
                <label class="flex flex-col text-[var(--marron)] font-semibold">
                    Dirección
                    <input type="text" name="direccion"
                        value="<?= htmlspecialchars($user['direccion'] ?? '') ?>"
                        class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                </label>

                <label class="flex flex-col text-[var(--marron)] font-semibold">
                    Departamento
                    <input type="text" name="departamento"
                        value="<?= htmlspecialchars($user['departamento'] ?? '') ?>"
                        class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                </label>

                <label class="flex flex-col text-[var(--marron)] font-semibold">
                    Barrio
                    <input type="text" name="barrio"
                        value="<?= htmlspecialchars($user['barrio'] ?? '') ?>"
                        class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                </label>
            </div>

            <button
                type="submit"
                class="md:col-span-2 bg-[var(--caramelo)] text-white py-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition mt-4" style="width: 35%; margin: auto">
                Guardar cambios
            </button>
        
        </form>
        <br>
        <section class="mb-6 text-center">
            <button id="abrirModalDirecciones" class="bg-[var(--caramelo)] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition">
                Administrar Direcciones
           </section>
        
        <form method="POST" action="recuperar_password_enviar">
    <input type="hidden" name="email" value="<?= htmlspecialchars($usuario['email']) ?>">
    <section class="mb-6 text-center">
    <button type="submit" class="bg-[var(--caramelo)] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition">
                Cambiar mi contraseña
                 </button>
                 </section>
</form>

        <section class="mt-16">
            <h2 class="text-3xl font-bold mb-6" style="color: var(--caramelo);">Mis Mascotas</h2>
            <?php if (count($mascotas) === 0): ?>
                <p class="text-[var(--marron)]">No tenés mascotas registradas aún.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($mascotas as $index => $mascota): ?>
                        <div class="border border-[var(--caramelo)] rounded-lg p-4 shadow hover:shadow-lg transition cursor-pointer flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <img src="<?= htmlspecialchars($mascota['foto_url']) ?: 'assets/img/default-pet.png' ?>" alt="<?= htmlspecialchars($mascota['nombre']) ?>" class="w-16 h-16 object-cover rounded-full border-2 border-[var(--caramelo)]" />
                                <div>
                                    <h3 class="text-xl font-semibold mb-1 text-[var(--marron)]"><?= htmlspecialchars($mascota['nombre']) ?></h3>
                                    <p class="text-[var(--marron)] text-sm">Edad: <?= htmlspecialchars($mascota['edad']) ?> años</p>
                                </div>
                            </div>
                            <button
                                data-modal-target="modal-<?= $index ?>"
                                class="btnMostrarDatos bg-[var(--caramelo)] text-white px-3 py-2 rounded-lg font-semibold hover:bg-[var(--marron)] transition" style="margin-left: 10px;">
                                Mostrar Datos
                            </button>

                            <!-- Modal oculto -->
                            <div
                                id="modal-<?= $index ?>"
                                class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity">
                                <div class="bg-[var(--blanco-caldo)] p-6 rounded-xl max-w-md w-full relative shadow-lg">
                                    <button class="cerrarModal absolute top-2 right-2 text-[var(--marron)] hover:text-[var(--caramelo)] font-bold text-xl">&times;</button>
                                    <img src="<?= htmlspecialchars($mascota['foto_url']) ?: 'assets/img/default-pet.png' ?>" alt="<?= htmlspecialchars($mascota['nombre']) ?>" class="w-32 h-32 object-cover rounded-full border-4 border-[var(--caramelo)] mb-4 mx-auto" />
                                    <h3 class="text-2xl font-bold text-center mb-4 text-[var(--marron)]">
                                        <?= htmlspecialchars($mascota['nombre']) ?> #<?= htmlspecialchars($mascota['codigo_mascotag'] ?? '') ?>
                                    </h3>

                                    <p><strong>Raza:</strong> <?= htmlspecialchars($mascota['raza']) ?></p>
                                    <p><strong>Edad:</strong> <?= htmlspecialchars($mascota['edad']) ?> años</p>
                                    <p><strong>Sexo:</strong> <?= htmlspecialchars($mascota['sexo']) ?></p>
                                    <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($mascota['descripcion'])) ?></p>
                                    <p><strong>Alergias:</strong> <?= nl2br(htmlspecialchars($mascota['alergias'])) ?: 'Ninguna' ?></p>
                                    <?php if (!empty($mascota['direccion'])): ?>
                                        <p><strong>Dirección asignada:</strong>
                                            <?= htmlspecialchars($mascota['direccion']) ?>
                                            <?= htmlspecialchars($mascota['barrio'] ? ', ' . $mascota['barrio'] : '') ?>
                                            <?= htmlspecialchars($mascota['departamento'] ? ', ' . $mascota['departamento'] : '') ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($mascota['veterinaria_nombre'])): ?>
                                        <p><strong>Veterinaria:</strong> <?= htmlspecialchars($mascota['veterinaria_nombre']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($mascota['veterinaria_contacto'])): ?>
                                        <p><strong>Contacto Veterinaria:</strong> <?= htmlspecialchars($mascota['veterinaria_contacto']) ?></p>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Modal Direcciones -->
        <div id="modalDirecciones" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity z-50">
            <div class="bg-[var(--blanco-caldo)] p-6 rounded-3xl max-w-lg w-full relative shadow-lg max-h-[80vh] overflow-y-auto">
                <button id="cerrarModalDirecciones" class="absolute top-4 right-4 text-[var(--marron)] font-bold text-3xl hover:text-[var(--caramelo)]">&times;</button>
                <h2 class="text-3xl font-bold mb-6 text-[var(--marron)] text-center">Administrar Direcciones</h2>

                <div class="mb-6">
                    <h3 class="font-semibold mb-3 text-[var(--marron)]">Tus direcciones guardadas</h3>
                    <?php if (count($direcciones) === 0): ?>
                        <p class="text-[var(--marron)]">No tienes direcciones guardadas.</p>
                    <?php else: ?>
                        <ul class="list-disc list-inside text-[var(--marron)] mb-4">
                            <?php foreach ($direcciones as $dir): ?>
                                <li class="flex justify-between items-center">
                                    <span>
                                        <?= htmlspecialchars($dir['direccion']) ?>
                                        <?= htmlspecialchars($dir['barrio'] ? ' - ' . $dir['barrio'] : '') ?>
                                        <?= htmlspecialchars($dir['departamento'] ? ', ' . $dir['departamento'] : '') ?>
                                    </span>
                                    <form action="direcciones/direccion_eliminar" method="POST" style="margin-left: 10px;">
                                        <input type="hidden" name="id_direccion" value="<?= $dir['id_direccion'] ?>" />
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold" title="Eliminar dirección">&times;</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <form id="formAgregarDireccion" action="direcciones/direccion_agregar" method="POST" class="flex flex-col gap-6">
                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Dirección
                        <input type="text" name="direccion" required
                            class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </label>
                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Barrio
                        <input type="text" name="barrio"
                            class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </label>
                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Departamento
                        <input type="text" name="departamento"
                            class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </label>
                    <button type="submit" class="bg-[var(--caramelo)] text-white py-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition max-w-max mx-auto">
                        Agregar Dirección
                    </button>
                </form>
            </div>
        </div>

        <!-- Botón para abrir modal Agregar Mascota -->
        <section class="mt-10">
            <button id="abrirModalMascota" class="bg-[var(--caramelo)] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition">
                Agregar Mascota
            </button>
        </section>
        <!-- Modal Detalle Orden -->
        <div id="modalDetalleOrden" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity z-50">
            <div class="bg-[var(--blanco-caldo)] p-6 rounded-3xl max-w-3xl w-full relative shadow-lg max-h-[80vh] overflow-y-auto">
                <button id="cerrarDetalleOrden" class="absolute top-4 right-4 text-[var(--marron)] font-bold text-3xl hover:text-[var(--caramelo)]">&times;</button>
                <div id="contenidoDetalleOrden">
                    <p>Cargando...</p>
                </div>
            </div>
        </div>
        <!-- Modal Agregar Mascota -->
        <div id="modalMascota" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity z-50">
            <div class="bg-[var(--blanco-caldo)] p-6 rounded-3xl max-w-lg w-full relative shadow-lg">
                <button id="cerrarModalMascota" class="absolute top-4 right-4 text-[var(--marron)] font-bold text-3xl hover:text-[var(--caramelo)]">&times;</button>
                <h2 class="text-3xl font-bold mb-6 text-[var(--marron)] text-center">Agregar Mascota</h2>
                <form action="mascota_agregar" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Código Petiqueta
                        <input type="text" name="codigo_mascotag" maxlength="6" minlength="6" required
                            placeholder="Se encuentra bajo el QR"
                            class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </label>

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Nombre
                        <input type="text" name="nombre" required placeholder="Nombre de tu mascota"
                            class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </label>

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Raza
                        <input type="text" name="raza" required placeholder="Raza de tu mascota"
                            class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </label>

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Edad (años)
                        <input type="number" name="edad" required min="0" max="50" placeholder="Edad"
                            class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </label>

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Sexo
                        <select name="sexo" required
                            class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none">
                            <option value="" disabled selected>Selecciona</option>
                            <option value="Macho">Macho</option>
                            <option value="Hembra">Hembra</option>
                        </select>
                    </label>

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Descripción
                        <textarea name="descripcion" rows="3" placeholder="Descripción de tu mascota" required
                            class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none"></textarea>
                    </label>

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Alergias
                        <textarea name="alergias" rows="2" placeholder="Alergias de tu mascota (si tiene)"
                            class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none"></textarea>
                    </label>

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Foto
                        <input type="file" name="foto" accept="image/*" required
                            class="mt-2 p-1 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </label>

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Dirección (opcional)
                        <select name="id_direccion" class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none">
                            <option value="">Sin asignar</option>
                            <?php foreach ($direcciones as $direccion): ?>
                                <option value="<?= $direccion['id_direccion'] ?>">
                                    <?= htmlspecialchars($direccion['direccion']) ?>
                                    <?= htmlspecialchars($direccion['barrio'] ? ' - ' . $direccion['barrio'] : '') ?>
                                    <?= htmlspecialchars($direccion['departamento'] ? ', ' . $direccion['departamento'] : '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Nombre Veterinaria (opcional)
                        <input type="text" name="veterinaria_nombre" class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </label>

                    <label class="flex flex-col text-[var(--marron)] font-semibold">
                        Contacto Veterinaria (opcional)
                        <input type="text" name="veterinaria_contacto" class="mt-2 p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </label>

                    <button type="submit" class="bg-[var(--caramelo)] text-white py-3 p-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition max-w-max mx-auto">
                        Agregar Mascota
                    </button>
                </form>
            </div>
        </div>
        <!-- NUEVA SECCIÓN: Mis Órdenes -->
        <section class="mt-16">
            <h2 class="text-3xl font-bold mb-6 text-[var(--caramelo)]">Mis Órdenes</h2>

            <?php if (empty($ordenesUsuario)): ?>
                <p class="text-[var(--marron)]">No tenés órdenes registradas.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg shadow overflow-hidden">
                        <thead class="bg-[var(--caramelo)] text-white">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold">ID</th>
                                <th class="text-left py-3 px-4 font-semibold">Fecha</th>
                                <th class="text-right py-3 px-4 font-semibold">Total</th>
                                <th class="text-left py-3 px-4 font-semibold">Estado</th>
                                <th class="text-left py-3 px-4 font-semibold">Método Pago</th>
                                <th class="text-left py-3 px-4 font-semibold">Método Entrega</th>
                                <th class="text-left py-3 px-4 font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordenesUsuario as $index => $orden): ?>
                                <tr class="<?= $index % 2 === 0 ? 'bg-[var(--blanco-caldo)]' : '' ?>">
                                    <td class="py-3 px-4"><?= htmlspecialchars($orden['id_orden']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($orden['fecha_creacion']))) ?></td>
                                    <td class="py-3 px-4 text-right">$<?= number_format($orden['total'], 2, ',', '.') ?> UYU</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold
                                <?= strtolower($orden['estado']) === 'pendiente' ? 'bg-yellow-300 text-yellow-900' : (strtolower($orden['estado']) === 'pagado' ? 'bg-green-300 text-green-900' : (strtolower($orden['estado']) === 'enviado' ? 'bg-blue-300 text-blue-900' : (strtolower($orden['estado']) === 'cancelado' ? 'bg-red-300 text-red-900' : 'bg-gray-300 text-gray-900'))) ?>">
                                            <?= ucfirst(htmlspecialchars($orden['estado'])) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4"><?= ucfirst(htmlspecialchars($orden['metodo_pago'])) ?></td>
                                    <td class="py-3 px-4"><?= ucfirst(htmlspecialchars($orden['metodo_entrega'])) ?></td>
                                    <td class="py-3 px-3">
                                        <button
                                            class="btnVerDetalleOrden text-[var(--caramelo)] hover:underline font-semibold bg-transparent border-none cursor-pointer p-0"
                                            data-ordenid="<?= $orden['id_orden'] ?>">
                                            Ver detalles
                                        </button>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>


    </main>



    <?php include '../templates/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalOrdenDetalle = document.getElementById('modalDetalleOrden');
            const btnCerrarDetalle = document.getElementById('cerrarDetalleOrden');
            const contenidoDetalle = document.getElementById('contenidoDetalleOrden');

            if (btnCerrarDetalle) {
                btnCerrarDetalle.addEventListener('click', () => {
                    modalOrdenDetalle.classList.remove('active');
                });
            }

            modalOrdenDetalle.addEventListener('click', e => {
                if (e.target === modalOrdenDetalle) {
                    modalOrdenDetalle.classList.remove('active');
                }
            });
            document.querySelectorAll('.btnVerDetalleOrden').forEach(button => {
                button.addEventListener('click', () => {
                    const ordenId = button.getAttribute('data-ordenid');


                    console.log('ID de orden que envío:', ordenId);

                    // Abrir modal
                    modalOrdenDetalle.classList.add('active');

                    // Mostrar loading
                    contenidoDetalle.innerHTML = '<p>Cargando detalles...</p>';

                    // Cargar datos via AJAX
                    fetch(`orden_detalle_ajax.php?id=${ordenId}`)
                        .then(response => {
                            console.log('Response status:', response.status);
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(datos => {
                            console.log('Datos recibidos:', datos);

                            if (datos.error) {
                                contenidoDetalle.innerHTML = `<p class="text-red-600 font-semibold">${datos.error}</p>`;
                                return;
                            }

                            // Crear contenido HTML con detalles
                            let htmlDetalle = `<h2 class="text-2xl font-bold mb-4 text-[var(--marron)]">Detalle Orden #${ordenId}</h2>`;

                            htmlDetalle += `<p><strong>Fecha:</strong> ${datos.fecha_creacion}</p>`;
                            htmlDetalle += `<p><strong>Total:</strong> $${datos.total.toFixed(2)} UYU</p>`;
                            htmlDetalle += `<p><strong>Estado:</strong> ${datos.estado}</p>`;
                            htmlDetalle += `<p><strong>Método de pago:</strong> ${datos.metodo_pago}</p>`;
                            htmlDetalle += `<p><strong>Método entrega:</strong> ${datos.metodo_entrega}</p>`;

                            htmlDetalle += `<table class="min-w-full mt-4 border border-gray-300 rounded-lg">
            <thead class="bg-[var(--caramelo)] text-white">
                <tr>
                    <th class="text-left px-3 py-2">Producto</th>
                    <th class="text-right px-3 py-2">Cantidad</th>
                    <th class="text-right px-3 py-2">Precio Unitario</th>
                    <th class="text-right px-3 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody>`;

                            datos.detalle.forEach(item => {
                                const precioUnitarioNum = Number(item.precio_unitario);
                                const subtotal = item.cantidad * precioUnitarioNum;

                                htmlDetalle += `<tr class="border-t border-gray-300">
                <td class="px-3 py-2">${item.nombre}</td>
                <td class="text-right px-3 py-2">${item.cantidad}</td>
                <td class="text-right px-3 py-2">$${precioUnitarioNum.toFixed(2)}</td>
                <td class="text-right px-3 py-2">$${subtotal.toFixed(2)}</td>
            </tr>`;
                            });

                            htmlDetalle += `</tbody></table>`;

                            contenidoDetalle.innerHTML = htmlDetalle;
                        })
                        .catch(error => {
                            console.error('Error fetch detalles orden:', error);
                            contenidoDetalle.innerHTML = `<p class="text-red-600 font-semibold">Error cargando detalles de la orden.</p>`;
                        });

                });

            });

            const btnAbrirMascota = document.getElementById('abrirModalMascota');
            const modalMascota = document.getElementById('modalMascota');
            const btnCerrarMascota = document.getElementById('cerrarModalMascota');

            btnAbrirMascota.addEventListener('click', () => {
                modalMascota.classList.add('active');
            });

            btnCerrarMascota.addEventListener('click', () => {
                modalMascota.classList.remove('active');
            });

            modalMascota.addEventListener('click', e => {
                if (e.target === modalMascota) {
                    modalMascota.classList.remove('active');
                }
            });

            const btnAbrir = document.getElementById('abrirModalDirecciones');
            const modal = document.getElementById('modalDirecciones');
            const btnCerrar = document.getElementById('cerrarModalDirecciones');

            btnAbrir.addEventListener('click', () => modal.classList.add('active'));
            btnCerrar.addEventListener('click', () => modal.classList.remove('active'));
            modal.addEventListener('click', e => {
                if (e.target === modal) modal.classList.remove('active');
            });

            const botonesMostrar = document.querySelectorAll('.btnMostrarDatos');
            const modales = document.querySelectorAll('.modal');

            botonesMostrar.forEach(btn => {
                btn.addEventListener('click', () => {
                    const idModal = btn.getAttribute('data-modal-target');
                    const modal = document.getElementById(idModal);
                    if (modal) {
                        modal.classList.add('active');
                    }
                });
            });

            modales.forEach(modal => {
                const btnCerrar = modal.querySelector('.cerrarModal');
                btnCerrar.addEventListener('click', () => {
                    modal.classList.remove('active');
                });

                modal.addEventListener('click', e => {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                    }
                });
            });

            const btnAbrirDirecciones = document.getElementById('abrirModalDirecciones');
            const modalDirecciones = document.getElementById('modalDirecciones');
            const btnCerrarDirecciones = document.getElementById('cerrarModalDirecciones');

            btnAbrirDirecciones.addEventListener('click', () => {
                modalDirecciones.classList.add('active');
                console.log('Botón abrir modal direcciones:', btnAbrirDirecciones);
                console.log('Modal direcciones:', modalDirecciones);
                console.log('Botón cerrar modal direcciones:', btnCerrarDirecciones);

            });

            btnCerrarDirecciones.addEventListener('click', () => {
                modalDirecciones.classList.remove('active');
            });

            modalDirecciones.addEventListener('click', (e) => {
                if (e.target === modalDirecciones) {
                    modalDirecciones.classList.remove('active');
                }
            });

        });
    </script>

</body>

</html>