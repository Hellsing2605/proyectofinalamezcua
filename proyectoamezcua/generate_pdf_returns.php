<?php
require('fpdf/fpdf.php');
require_once('includes/load.php');

// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

if (isset($_GET['id'])) {
    $return_id = (int)$_GET['id'];
    $return = find_return_by_id($return_id);
    if (!$return) {
        $session->msg("d", "Retorno no encontrado.");
        redirect('returns.php');
    }

    // Obtener los productos y cantidades asociados a este retorno
    $return_items = find_return_items($return_id);
} else {
    $session->msg("d", "ID de retorno no proporcionado.");
    redirect('returns.php');
}

$pdf = new FPDF();
$pdf->AddPage();

// Agregar título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Detalles de la Devolución'), 0, 1, 'C');
$pdf->Ln(10);

// Agregar los detalles del retorno centrados y con utf8_decode
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Color de fondo gris claro

$pdf->Cell(50, 10, utf8_decode('Código de Devolución'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode($return['return_code']), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Supervisor'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 1, utf8_decode($return['supervisor_name']), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Fecha'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(read_date($return['date'])), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Almacén'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(remove_junk(find_by_id('warehouses', $return['warehouse_id'])['name'])), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Devolución por parte de'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(
    !empty($return['technician_name']) ? $return['technician_name'] : 
    (!empty($return['cuadrilla_name']) ? $return['cuadrilla_name'] : 'N/A')
), 1, 1, 'C');

if (!empty($return['obra_name'])) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('Operación'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($return['operacion']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('OEI'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($return['oei']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('OE'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($return['obra_name']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('Central'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($return['central']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('Ruta'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($return['ruta']), 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('PEP'), 1, 0, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 10, utf8_decode($return['pep']), 1, 1, 'C');
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Observaciones'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode($return['observations']), 1, 1, 'C');

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

if ($return_items) {
    foreach ($return_items as $item) {
        $product = find_by_id('products', $item['product_id']);
        $pdf->Cell(50, 10, utf8_decode(remove_junk($product['material_code'])), 1, 0, 'C');
        $pdf->Cell(50, 10, utf8_decode(remove_junk($product['name'])), 1, 0, 'C');
        $pdf->Cell(50, 10, utf8_decode(remove_junk($item['category_name'])), 1, 0, 'C');
        $pdf->Cell(40, 10, (int)$item['quantity'], 1, 1, 'C');
    }
} else {
    $pdf->Cell(190, 10, utf8_decode('No hay materiales asociados con esta devolución.'), 1, 1, 'C');
}

// Salida del PDF
$pdf->Output('I', 'return_'.$return['return_code'].'.pdf');
?>

