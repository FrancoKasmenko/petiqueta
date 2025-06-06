<?php
session_start();

if (empty($_SESSION['carrito'])) {
    header('Location: carrito');
    exit;
}

$carrito = $_SESSION['carrito'];

function calcularSubtotal($carrito)
{
    $subtotal = 0;
    foreach ($carrito as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }
    return $subtotal;
}

$subtotal = calcularSubtotal($carrito);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Finalizar Compra - Petiqueta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="icon" type="image/png" href="assets/img/icons/favicon.png">
</head>

<body class="bg-[var(--beige)] min-h-screen font-sans flex flex-col">
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
    </style>
    <script src="https://sdk.mercadopago.com/js/v2"></script>

    <?php include __DIR__ . '/../templates/navbar.php'; ?>

    <main class="flex-grow max-w-5xl mx-auto p-8 bg-[var(--blanco-caldo)] rounded-3xl shadow-md mt-12 mb-12 min-h-screen">

        <h1 class="text-4xl font-extrabold mb-8 text-[var(--marron)] text-center">Finalizar Compra</h1>

        <div class="flex flex-col lg:flex-row gap-10">

            <section
                class="w-full lg:w-1/3 sticky top-24 self-start bg-[var(--beige)] rounded-xl p-6 shadow-md max-h-[calc(100vh-6rem)] overflow-y-auto"
                style="z-index: 10;">

                <h2 class="text-2xl font-semibold mb-6 text-[var(--marron)]">Resumen del Pedido</h2>

                <ul class="divide-y divide-gray-300 max-h-[60vh] overflow-y-auto">
                    <?php foreach ($carrito as $item): ?>
                        <li class="py-3 flex flex-col gap-1">
                            <div class="flex justify-between items-center gap-4">
                                <?php if (!empty($item['imagen_url'])): ?>
                                    <img src="<?= htmlspecialchars($item['imagen_url']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>" class="w-14 h-14 object-cover rounded" />
                                <?php else: ?>
                                    <div class="w-14 h-14 bg-gray-200 rounded"></div>
                                <?php endif; ?>

                                <span class="font-medium text-[var(--marron)] flex-grow"><?= htmlspecialchars($item['nombre']) ?> x <?= $item['cantidad'] ?></span>
                                <span class="font-semibold text-[var(--caramelo)] whitespace-nowrap">$<?= number_format($item['precio'] * $item['cantidad'], 0, ',', '.') ?> UYU</span>
                            </div>
                            <div class="text-sm text-gray-600 pl-20 mt-3">
                                <span><strong>Tamaño:</strong> <?= htmlspecialchars($item['tamanio'] ?? 'No especificado') ?></span><br />

                                <span><strong>Color(es):</strong>
                                    <?php if (!empty($item['colores_hex']) && is_array($item['colores_hex'])): ?>
                                        <?php foreach ($item['colores_hex'] as $idx => $hex): ?>
                                            <span
                                                title="<?= htmlspecialchars(explode(',', $item['color'])[$idx] ?? 'Color') ?>"
                                                style="
            display: inline-block;
            width: 20px;
            height: 20px;
            background-color: <?= htmlspecialchars($hex) ?>;
            border-radius: 50%;
            border: 1px solid #ccc;
            margin-right: 6px;
            vertical-align: middle;
            cursor: default;
          ">
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        No especificado
                                    <?php endif; ?>
                                </span>
                            </div>

                        </li>
                    <?php endforeach; ?>
                </ul>


                <p id="envioCostoResumen" class="mt-4 font-semibold text-[var(--marron)] text-right"></p>

                <p id="totalConEnvio" class="mt-6 font-bold text-xl text-[var(--caramelo)] text-right">Total: $<?= number_format($subtotal, 0, ',', '.') ?> UYU</p>
            </section>

            <section class="w-full lg:w-2/3 bg-[var(--beige)] rounded-xl p-6 shadow-md">

                <form action="procesar_compra" method="POST" class="space-y-8">

                    <fieldset class="border border-gray-300 rounded-lg p-6">
                        <legend class="font-semibold text-[var(--marron)] text-lg mb-4">Contacto</legend>

                        <label class="block mb-2 text-[var(--marron)] font-semibold" for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required
                            placeholder="Nombre"
                            class="w-full p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />

                        <label class="block mb-2 text-[var(--marron)] font-semibold mt-4" for="apellido">Apellido</label>
                        <input type="text" id="apellido" name="apellido" required
                            placeholder="Apellido"
                            class="w-full p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />

                        <label class="block mb-2 text-[var(--marron)] font-semibold mt-4" for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" required
                            placeholder="Correo electrónico"
                            class="w-full p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                    </fieldset>

                    <fieldset class="border border-gray-300 rounded-lg p-6">
                        <legend class="font-semibold text-[var(--marron)] text-lg mb-4">Entrega</legend>

                        <div class="flex flex-col md:flex-row gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="metodo_entrega" value="envio" checked />
                                <span>Envío</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="metodo_entrega" value="retiro" />
                                <span>Retiro en tienda</span>
                            </label>
                        </div>

                        <div id="direccionEnvio" class="mt-6">
                            <div class="mt-4">
                                <input type="text" name="direccion" placeholder="Dirección" required
                                    class="w-full p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <input type="text" name="codigo_postal" placeholder="Código postal (opcional)"
                                    class="p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                                <input type="text" name="ciudad" placeholder="Ciudad" required
                                    class="p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                                <select name="region" id="region" required
                                    class="p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none">
                                    <option value="" disabled selected>Seleccione región</option>
                                    <option value="Artigas">Artigas</option>
                                    <option value="Canelones">Canelones</option>
                                    <option value="Cerro Largo">Cerro Largo</option>
                                    <option value="Colonia">Colonia</option>
                                    <option value="Durazno">Durazno</option>
                                    <option value="Flores">Flores</option>
                                    <option value="Florida">Florida</option>
                                    <option value="Lavalleja">Lavalleja</option>
                                    <option value="Maldonado">Maldonado</option>
                                    <option value="Montevideo">Montevideo</option>
                                    <option value="Paysandú">Paysandú</option>
                                    <option value="Río Negro">Río Negro</option>
                                    <option value="Rivera">Rivera</option>
                                    <option value="Rocha">Rocha</option>
                                    <option value="Salto">Salto</option>
                                    <option value="San José">San José</option>
                                    <option value="Soriano">Soriano</option>
                                    <option value="Tacuarembó">Tacuarembó</option>
                                    <option value="Treinta y Tres">Treinta y Tres</option>
                                </select>
                                <input type="text" name="telefono" placeholder="Teléfono" required
                                    class="p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" />
                            </div>
                        </div>

                        <!-- Info Retiro en tienda -->
                        <div id="retiroTienda" class="hidden mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-300 text-[var(--marron)]">
                            <p><strong>Sucursal para retiro:</strong></p>
                            <p>Solferino 4041, Montevideo, UY-MO</p>
                            <p><span class="font-semibold">GRATIS</span></p>
                            <p>Normalmente está listo en 1 hora</p>
                        </div>
                    </fieldset>

                    <!-- Métodos de envío -->
                    <fieldset class="border border-gray-300 rounded-lg p-6">
                        <legend class="font-semibold text-[var(--marron)] text-lg mb-4">Métodos de envío</legend>
                        <p id="metodoEnvioText" class="text-center text-gray-500 font-semibold">Ingresa tu dirección de envío para ver los métodos disponibles.</p>
                        <p id="costoEnvio" class="text-center text-[var(--caramelo)] font-bold text-xl mt-2"></p>
                    </fieldset>

                    <!-- Pago -->
                    <fieldset class="border border-gray-300 rounded-lg p-6">
                        <legend class="font-semibold text-[var(--marron)] text-lg mb-4">Pago</legend>

                        <div class="space-y-4">
                            <label class="flex items-center gap-3 p-3 border border-yellow-300 rounded-lg cursor-pointer bg-yellow-50">
                                <input type="radio" name="metodo_pago" value="mercadopago" checked />
                                <img src="assets/img/icons/mercadopago.svg" alt="Mercado Pago" style="width: 50px;" />
                                <span class="font-semibold text-[var(--marron)]">Mercado Pago</span>
                            </label>

                            <label class="flex items-center gap-3 p-3 border border-gray-300 rounded-lg cursor-pointer">
                                <input type="radio" name="metodo_pago" value="transferencia" />
                                <span class="font-semibold text-[var(--marron)]">Transferencia Bancaria</span>
                            </label>
                        </div>
                    </fieldset>

                    <!-- Dirección facturación -->
                    <fieldset class="border border-gray-300 rounded-lg p-6">
                        <legend class="font-semibold text-[var(--marron)] text-lg mb-4">Dirección de facturación</legend>

                        <div class="mb-4 flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="facturacion_opcion" id="factura_misma" value="misma" />
                                <span class="text-[var(--marron)] font-semibold">La misma dirección de envío</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="facturacion_opcion" id="factura_distinta" value="distinta" checked />
                                <span class="text-[var(--marron)] font-semibold">Usar una dirección de facturación distinta</span>
                            </label>
                        </div>

                        <div id="facturacionCampos">
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="text" class="p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" name="fact_nombre" placeholder="Nombre" />
                                <input type="text" class="p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" name="fact_apellido" placeholder="Apellido" />
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <input type="text" class="p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" name="fact_direccion" placeholder="Dirección" />
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <input type="text" class="p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" name="fact_codigo_postal" placeholder="Código postal" />
                                <input type="text" class="p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" name="fact_ciudad" placeholder="Ciudad" />
                                <select class="p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none" name="fact_region">
                                    <option value="" disabled selected>Seleccione región</option>
                                    <option value="Artigas">Artigas</option>
                                    <option value="Canelones">Canelones</option>
                                    <option value="Cerro Largo">Cerro Largo</option>
                                    <option value="Colonia">Colonia</option>
                                    <option value="Durazno">Durazno</option>
                                    <option value="Flores">Flores</option>
                                    <option value="Florida">Florida</option>
                                    <option value="Lavalleja">Lavalleja</option>
                                    <option value="Maldonado">Maldonado</option>
                                    <option value="Montevideo">Montevideo</option>
                                    <option value="Paysandú">Paysandú</option>
                                    <option value="Río Negro">Río Negro</option>
                                    <option value="Rivera">Rivera</option>
                                    <option value="Rocha">Rocha</option>
                                    <option value="Salto">Salto</option>
                                    <option value="San José">San José</option>
                                    <option value="Soriano">Soriano</option>
                                    <option value="Tacuarembó">Tacuarembó</option>
                                    <option value="Treinta y Tres">Treinta y Tres</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Factura con RUT -->
                    <fieldset class="border border-gray-300 rounded-lg p-6">
                        <legend class="font-semibold text-[var(--marron)] text-lg mb-4">Factura con RUT</legend>

                        <label class="flex items-center gap-2 cursor-pointer mb-4">
                            <input type="checkbox" name="facturar_empresa" id="facturar_empresa" />
                            <span class="text-[var(--marron)] font-semibold">Deseo factura con RUT</span>
                        </label>

                        <div id="empresaCampos" style="display:none;">
                            <input type="text" name="rut" placeholder="RUT" class="w-full p-3 rounded-lg border border-gray-300 mb-2" />
                            <input type="text" name="razon_social" placeholder="Razón social" class="w-full p-3 rounded-lg border border-gray-300 mb-2" />
                            <input type="text" name="direccion_fiscal" placeholder="Dirección fiscal" class="w-full p-3 rounded-lg border border-gray-300" />
                        </div>
                    </fieldset>
                    <fieldset class="border border-gray-300 rounded-lg p-6">
                        <legend class="font-semibold text-[var(--marron)] text-lg mb-4">Comentarios adicionales</legend>
                        <textarea name="comentarios_adicionales" rows="4" placeholder="Agrega cualquier comentario extra o indicación para tu pedido"
                            class="w-full p-3 rounded-lg border border-gray-300 focus:border-[var(--caramelo)] focus:ring focus:ring-[var(--caramelo)] focus:outline-none"></textarea>
                    </fieldset>
                    <input type="hidden" id="costo_envio" name="costo_envio" value="0" />
                    <button type="submit"
                        class="w-full bg-[var(--caramelo)] text-white py-3 rounded-lg font-semibold hover:bg-[var(--marron)] transition">
                        Pagar ahora
                    </button>

                </form>

            </section>

        </div>

    </main>

    <?php include __DIR__ . '/../templates/footer.php'; ?>

    <script>
        const mp = new MercadoPago('token', {
            locale: 'es-UY'
        });

        mp.bricks().create('wallet', 'wallet_container', {
            initialization: {
            }
        });
    </script>

    <script>
        const envioRadio = document.querySelector('input[name="metodo_entrega"][value="envio"]');
        const retiroRadio = document.querySelector('input[name="metodo_entrega"][value="retiro"]');
        const direccionEnvio = document.getElementById('direccionEnvio');
        const retiroTienda = document.getElementById('retiroTienda');

        const regionSelect = document.getElementById('region');
        const metodoEnvioText = document.getElementById('metodoEnvioText');
        const costoEnvio = document.getElementById('costoEnvio');
        const envioCostoResumen = document.getElementById('envioCostoResumen');
        const totalConEnvio = document.getElementById('totalConEnvio');

        const facturaMisma = document.getElementById('factura_misma');
        const facturaDistinta = document.getElementById('factura_distinta');
        const facturacionCampos = document.getElementById('facturacionCampos');

        const facturarEmpresaCheckbox = document.getElementById('facturar_empresa');
        const empresaCampos = document.getElementById('empresaCampos');

        function toggleEntrega() {
            if (envioRadio.checked) {
                direccionEnvio.classList.remove('hidden');
                retiroTienda.classList.add('hidden');

                direccionEnvio.querySelectorAll('input, select').forEach(el => el.required = true);
            } else {
                direccionEnvio.classList.add('hidden');
                retiroTienda.classList.remove('hidden');

                direccionEnvio.querySelectorAll('input, select').forEach(el => el.required = false);

                metodoEnvioText.textContent = 'Retiro en tienda - GRATIS';
                costoEnvio.textContent = '';
                envioCostoResumen.textContent = '';
                totalConEnvio.textContent = `Total: $<?= number_format($subtotal, 0, ',', '.') ?> UYU`;
            }
        }

        function toggleFacturacion() {
            if (retiroRadio.checked) {
                facturaMisma.parentElement.style.display = 'none';
                facturaDistinta.parentElement.style.display = 'none';
                facturacionCampos.style.display = 'block';
                facturacionCampos.querySelectorAll('input, select').forEach(el => el.required = true);
            } else {
                facturaMisma.parentElement.style.display = 'inline-flex';
                facturaDistinta.parentElement.style.display = 'inline-flex';

                if (facturaMisma.checked) {
                    facturacionCampos.style.display = 'none';
                    facturacionCampos.querySelectorAll('input, select').forEach(el => el.required = false);
                } else {
                    facturacionCampos.style.display = 'block';
                    facturacionCampos.querySelectorAll('input, select').forEach(el => el.required = true);
                }
            }
        }

        function actualizarCostoEnvio() {
            const region = regionSelect.value;
            let costo = 0;

            if (!region) {
                metodoEnvioText.textContent = 'Ingresa tu dirección de envío para ver los métodos disponibles.';
                costoEnvio.textContent = '';
                envioCostoResumen.textContent = '';
                totalConEnvio.textContent = `Total: $<?= number_format($subtotal, 0, ',', '.') ?> UYU`;
                document.getElementById('costo_envio').value = 0;
                return;
            }

            if (region === 'Montevideo') {
                metodoEnvioText.textContent = 'Envío estándar a Montevideo';
                costo = 150;
                costoEnvio.textContent = '$150 UYU';
                envioCostoResumen.textContent = `Envío: $150 UYU`;
            } else {
                metodoEnvioText.textContent = `Envío estándar a ${region}`;
                costo = 290;
                costoEnvio.textContent = '$290 UYU';
                envioCostoResumen.textContent = `Envío: $290 UYU`;
            }

            document.getElementById('costo_envio').value = costo;

            const totalConCostoEnvio = <?= $subtotal ?> + costo;
            totalConEnvio.textContent = `Total: $${totalConCostoEnvio.toLocaleString('es-UY')} UYU`;
        }

        envioRadio.addEventListener('change', () => {
            toggleEntrega();
            actualizarCostoEnvio();
            toggleFacturacion();
        });

        retiroRadio.addEventListener('change', () => {
            toggleEntrega();
            toggleFacturacion();
        });

        facturaMisma.addEventListener('change', toggleFacturacion);
        facturaDistinta.addEventListener('change', toggleFacturacion);

        regionSelect.addEventListener('change', actualizarCostoEnvio);

        facturarEmpresaCheckbox.addEventListener('change', () => {
            empresaCampos.style.display = facturarEmpresaCheckbox.checked ? 'block' : 'none';
        });

        toggleEntrega();
        actualizarCostoEnvio();
        toggleFacturacion();
    </script>

</body>

</html>