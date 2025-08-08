<?php
require_once('includes/load.php');
require_once('fpdf/fpdf.php');

// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

// Obtener el ID de la recepción desde la URL
if (isset($_GET['id'])) {
    $reception_id = (int)$_GET['id'];
    $reception = find_by_id('receptions', $reception_id);

    if (!$reception) {
        $session->msg("d", "Recepción no encontrada.");
        redirect('receptions.php');
    }

    // Obtener los productos y cantidades asociados a esta recepción
    $reception_items = find_reception_items($reception_id);
} else {
    $session->msg("d", "ID de recepción no proporcionado.");
    redirect('receptions.php');
}

// Crear un nuevo PDF utilizando FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Configurar la fuente para el título
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('Detalles de la Recepción'), 0, 1, 'C');

// Espacio entre el título y el contenido
$pdf->Ln(5);

// Configurar la fuente para el contenido
$pdf->SetFont('Arial', 'B', 12);

// Agregar los detalles de la recepción centrados
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Color de fondo gris claro

$pdf->Cell(50, 10, utf8_decode('Código de Recepción'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, $reception['reception_code'], 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Fecha', 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, date('d/m/Y', strtotime($reception['date'])), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, utf8_decode('Almacén'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(remove_junk(find_by_id('warehouses', $reception['warehouse_id'])['name'])), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Supervisor', 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(remove_junk(find_by_id('supervisors', $reception['supervisor_id'])['name'])), 1, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Observaciones', 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode(remove_junk($reception['observations'])), 1, 1, 'C');


// Espacio antes de la tabla de productos
$pdf->Ln(10);

// Configurar la fuente para la tabla
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(81, 173, 237); // Fondo azul
$pdf->SetTextColor(255, 255, 255); // Texto blanco

// Cabeceras de la tabla
$pdf->Cell(50, 10, utf8_decode('Código de Material'), 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Material', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Unidad', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Cantidad', 1, 1, 'C', true);

// Restablecer colores para el contenido de la tabla
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->SetFillColor(245, 245, 245); // Fondo gris claro

// Contenido de la tabla
$fill = false;
foreach ($reception_items as $item) {
    $product = find_by_id('products', $item['product_id']);
    
    $pdf->Cell(50, 10, remove_junk($product['material_code']), 1, 0, 'C', $fill);
    $pdf->Cell(70, 10, utf8_decode(remove_junk($product['name'])), 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, utf8_decode(remove_junk($item['category_name'])), 1, 0, 'C', $fill);
    $pdf->Cell(40, 10, (int)$item['quantity'], 1, 1, 'C', $fill);
    $fill = !$fill;
}

// Output the PDF
$pdf->Output('D', 'Recepcion_' . $reception['reception_code'] . '.pdf');
?>



