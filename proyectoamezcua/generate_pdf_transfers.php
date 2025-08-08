<?php
require('fpdf/fpdf.php');
require_once('includes/load.php');

// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

if (isset($_GET['id'])) {
    $transfer_id = (int)$_GET['id'];
    $transfer = find_transfer_by_id($transfer_id);
    if (!$transfer) {
        $session->msg("d", "Traspaso no encontrado.");
        redirect('transfers.php');
    }

    // Obtener los productos y cantidades asociados a este traslado
    $transfer_items = find_transfer_items($transfer_id);
} else {
    $session->msg("d", "ID de traspaso no proporcionado.");
    redirect('transfers.php');
}

$pdf = new FPDF();
$pdf->AddPage();

// Agregar título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Detalles del Traspaso'), 0, 1, 'C');
$pdf->Ln(10);

// Agregar los detalles del traspaso centrados y con utf8_decode
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Color de fondo gris claro

$pdf->Cell(50, 10, utf8_decode('Código de Traspaso'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode($transfer['transfer_code']), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Supervisor'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode($transfer['supervisor_name']), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Fecha'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(read_date($transfer['date'])), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Almacén'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(remove_junk(find_by_id('warehouses', $transfer['warehouse_id'])['name'])), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Traspaso a'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(
    !empty($transfer['technician_name']) ? $transfer['technician_name'] : 
    (!empty($transfer['cuadrilla_name']) ? $transfer['cuadrilla_name'] : 'N/A')
), 1, 1, 'C');

if (!empty($transfer['obra_name'])) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('Operación'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($transfer['operacion']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('OEI'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($transfer['oei']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('OE'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($transfer['obra_name']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('Central'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($transfer['central']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('Ruta'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($transfer['ruta']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('PEP'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($transfer['pep']), 1, 1, 'C');
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Observaciones'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode($transfer['observations']), 1, 1, 'C');

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

if ($transfer_items) {
    foreach ($transfer_items as $item) {
        $product = find_by_id('products', $item['product_id']);
        $pdf->Cell(50, 10, utf8_decode(remove_junk($product['material_code'])), 1, 0, 'C');
        $pdf->Cell(50, 10, utf8_decode(remove_junk($product['name'])), 1, 0, 'C');
        $pdf->Cell(50, 10, utf8_decode(remove_junk($item['category_name'])), 1, 0, 'C');
        $pdf->Cell(40, 10, (int)$item['quantity'], 1, 1, 'C');
    }
} else {
    $pdf->Cell(190, 10, utf8_decode('No hay materiales asociados con este traspaso.'), 1, 1, 'C');
}

// Salida del PDF
$pdf->Output('I', 'transfer_'.$transfer['transfer_code'].'.pdf');
?>
