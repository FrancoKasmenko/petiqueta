<?php
require_once('../vendor/fpdf/fpdf.php');
require_once('../vendor/phpqrcode/qrlib.php');

class PDF extends FPDF
{
    // Dibuja un círculo
    function Circle($x, $y, $r, $style = 'D')
    {
        $this->Ellipse($x, $y, $r, $r, $style);
    }

    // Dibuja una elipse (o círculo si rx=ry)
    function Ellipse($x, $y, $rx, $ry, $style = 'D')
    {
        if ($style == 'F')
            $op = 'f';
        elseif ($style == 'FD' || $style == 'DF')
            $op = 'B';
        else
            $op = 'S';

        $lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
        $ly = 4 / 3 * (M_SQRT2 - 1) * $ry;

        $k = $this->k;
        $h = $this->h;

        $this->_out(sprintf('%.2F %.2F m', ($x + $rx) * $k, ($h - ($y)) * $k));
        $this->_arc($x + $rx, $y - $ly, $x + $lx, $y - $ry, $x, $y - $ry);
        $this->_arc($x - $lx, $y - $ry, $x - $rx, $y - $ly, $x - $rx, $y);
        $this->_arc($x - $rx, $y + $ly, $x - $lx, $y + $ry, $x, $y + $ry);
        $this->_arc($x + $lx, $y + $ry, $x + $rx, $y + $ly, $x + $rx, $y);
        $this->_out($op);
    }

    function _arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $k = $this->k;
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1 * $k,
            ($h - $y1) * $k,
            $x2 * $k,
            ($h - $y2) * $k,
            $x3 * $k,
            ($h - $y3) * $k
        ));
    }
}

$pdf = new PDF('P', 'cm', 'A4');
$pdf->AddPage();

$codigos = ['ABC123', 'DEF456', 'GHI789', 'JKL012', 'MNO345', 'PQR678', 'STU901', 'VWX234', 'YZA567', 'BCD890', 'ABC123', 'DEF456', 'GHI789', 'JKL012', 'MNO345', 'PQR678', 'STU901', 'VWX234', 'YZA567', 'BCD890', 'ABC123', 'DEF456', 'GHI789', 'JKL012', 'MNO345', 'PQR678', 'STU901', 'VWX234', 'YZA567', 'BCD890', 'ABC123', 'DEF456', 'GHI789', 'JKL012', 'MNO345', 'PQR678', 'STU901', 'VWX234', 'YZA567', 'BCD890', 'ABC123', 'DEF456', 'GHI789'];

// Parámetros
$diametro_cm = 2.5;          // diámetro círculo en cm
$margen_interno = 0.15;        // margen interno para el QR
$lado_qr = $diametro_cm - 6 * $margen_interno;

$qr_por_fila = 6;
$espacio_horizontal = 3.0;
$espacio_vertical = 3.5;

$inicio_x = 1;
$inicio_y = 1;

$contador = 0;

foreach ($codigos as $codigo) {
    $col = $contador % $qr_por_fila;
    $row = floor($contador / $qr_por_fila);

    $x = $inicio_x + $col * $espacio_horizontal;
    $y = $inicio_y + $row * $espacio_vertical;

    // Generar QR temporal
    $filename = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
    QRcode::png("http://localhost/mascotag/https://petiqueta.uy/mascota.php?codigo=$codigo", $filename, QR_ECLEVEL_L, 3, 2);
    // ...

    $radio = $diametro_cm / 2;

    // Posición QR centrado en el círculo
    $x_qr = $x + $radio - $lado_qr / 2;
    $y_qr = $y + $radio - $lado_qr / 2;

    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.05);
    $pdf->Circle($x + $radio, $y + $radio, $radio);

    $pdf->Image($filename, $x_qr, $y_qr, $lado_qr, $lado_qr);

    // ...



    // Dibujar círculo centrado
    $radio = $diametro_cm / 2;
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.05);
    $pdf->Circle($x + $radio, $y + $radio, $radio);

    // Texto centrado debajo del QR
    $pdf->SetXY($x, $y + $diametro_cm + 0.1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell($espacio_horizontal, 0.5, $codigo, 0, 0, 'C');

    unlink($filename);

    $contador++;

    // Salto de página opcional
    if ($row >= 10 - 1) {
        $pdf->AddPage();
        $contador = 0;
    }
}

$pdf->Output();
