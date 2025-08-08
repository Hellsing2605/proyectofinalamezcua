<?php
require('fpdf/fpdf.php');
require_once('includes/load.php');

// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

if (isset($_GET['id'])) {
    $liquidation_id = (int)$_GET['id'];
    $liquidation = find_liquidation_by_id($liquidation_id);
    if (!$liquidation) {
        $session->msg("d", "Liquidación no encontrada.");
        redirect('liquidations.php');
    }

    // Obtener los productos y cantidades asociados a esta liquidación
    $liquidation_items = find_liquidation_items($liquidation_id);
} else {
    $session->msg("d", "ID de liquidación no proporcionado.");
    redirect('liquidations.php');
}

$pdf = new FPDF();
$pdf->AddPage();

// Agregar título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Detalles de la Liquidación'), 0, 1, 'C');
$pdf->Ln(10);

// Agregar los detalles de la liquidación centrados y con utf8_decode
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Color de fondo gris claro

$pdf->Cell(50, 10, utf8_decode('Código de Liquidación'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode($liquidation['liquidation_code']), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Supervisor'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode($liquidation['supervisor_name']), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Fecha'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(read_date($liquidation['date'])), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Almacén'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(remove_junk(find_by_id('warehouses', $liquidation['warehouse_id'])['name'])), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Liquidación de'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(
    !empty($liquidation['technician_name']) ? $liquidation['technician_name'] : 
    (!empty($liquidation['cuadrilla_name']) ? $liquidation['cuadrilla_name'] : 'N/A')
), 1, 1, 'C');

if (!empty($liquidation['obra_name'])) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('Operación'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($liquidation['operacion']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('OEI'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($liquidation['oei']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('OE'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($liquidation['obra_name']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('Central'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($liquidation['central']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('Ruta'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($liquidation['ruta']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('PEP'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($liquidation['pep']), 1, 1, 'C');
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Observaciones'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode($liquidation['observations']), 1, 1, 'C');

$pdf->Ln(10); // Espacio entre la tabla de detalles y la de materiales

// Agregar la tabla de materiales
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(81, 173, 237); // Color de fondo azul
$pdf->SetTextColor(255, 255, 255); // Texto blanco
$pdf->Cell(50, 10, utf8_decode('Código de Material'), 1, 0, 'C', true);
$pdf->Cell(50, 10, utf8_decode('Material'), 1, 0, 'C', true);
$pdf->Cell(50, 10, utf8_decode('Unidad'), 1, 0, 'C', true);
$pdf->Cell(40, 10, utf8_decode('Cantidad'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->SetFillColor(245, 245, 245); // Fondo gris claro

if ($liquidation_items) {
    foreach ($liquidation_items as $item) {
        $product = find_by_id('products', $item['product_id']);
        $pdf->Cell(50, 10, utf8_decode(remove_junk($product['material_code'])), 1, 0, 'C');
        $pdf->Cell(50, 10, utf8_decode(remove_junk($product['name'])), 1, 0, 'C');
        $pdf->Cell(50, 10, utf8_decode(remove_junk($item['category_name'])), 1, 0, 'C');
        $pdf->Cell(40, 10, (int)$item['quantity'], 1, 1, 'C');
    }
} else {
    $pdf->Cell(190, 10, utf8_decode('No hay materiales asociados con esta liquidación.'), 1, 1, 'C');
}

// Salida del PDF
$pdf->Output('I', 'liquidation_'.$liquidation['liquidation_code'].'.pdf');
?>
