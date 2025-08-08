<?php
require_once('includes/load.php');
require_once('fpdf/fpdf.php');

// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

// Obtener el ID de la obra desde la URL
if (isset($_GET['id'])) {
    $obra_id = (int)$_GET['id'];
    $obra = find_by_id('obras', $obra_id);

    if (!$obra) {
        $session->msg("d", "Obra no encontrada.");
        redirect('obras.php');
    }

    // Obtener los productos y cantidades asociados a esta obra
    $inventory = find_obras_inventory($obra_id);
    $returns = find_obras_returns($obra_id);
    $liquidations = find_obras_liquidations($obra_id);

    // Combinar inventario, devoluciones y liquidaciones en un solo array
    $inventory_with_returns_and_liquidations = [];
    foreach ($inventory as $item) {
        $inventory_with_returns_and_liquidations[$item['product_id']] = [
            'product_name' => $item['product_name'],
            'quantity' => (int)$item['quantity'],
            'returned_quantity' => 0,
            'liquidated_quantity' => 0,
            'pending_quantity' => (int)$item['quantity']
        ];
    }
    foreach ($returns as $item) {
        if (isset($inventory_with_returns_and_liquidations[$item['product_id']])) {
            $inventory_with_returns_and_liquidations[$item['product_id']]['returned_quantity'] += (int)$item['quantity'];
            $inventory_with_returns_and_liquidations[$item['product_id']]['pending_quantity'] -= (int)$item['quantity'];
        }
    }
    foreach ($liquidations as $item) {
        if (isset($inventory_with_returns_and_liquidations[$item['product_id']])) {
            $inventory_with_returns_and_liquidations[$item['product_id']]['liquidated_quantity'] += (int)$item['quantity'];
            $inventory_with_returns_and_liquidations[$item['product_id']]['pending_quantity'] -= (int)$item['quantity'];
        }
    }
} else {
    $session->msg("d", "ID de la obra no proporcionado.");
    redirect('obras.php');
}

// Crear un nuevo PDF utilizando FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Configurar la fuente para el título
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('Materiales destinados a la Obra'), 0, 1, 'C');

// Espacio entre el título y el contenido
$pdf->Ln(5);

// Configurar la fuente para el contenido
$pdf->SetFont('Arial', 'B', 12);

// Agregar los detalles de la obra centrados
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Color de fondo gris claro

$pdf->Cell(50, 10, utf8_decode('Obra'), 1, 0, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(140, 10, utf8_decode($obra['operacion'] . ' - ' . $obra['oei'] . ' - ' . $obra['oe'] . ' - ' . $obra['central'] . ' - ' . $obra['ruta'] . ' - ' . $obra['pep']), 1, 1, 'C');

// Espacio antes de la tabla de productos
$pdf->Ln(10);

// Configurar la fuente para la tabla
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(81, 173, 237); // Fondo azul
$pdf->SetTextColor(255, 255, 255); // Texto blanco

// Cabeceras de la tabla
$pdf->Cell(70, 10, utf8_decode('Producto'), 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Surtido', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Liquidaciones', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Devoluciones', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Pendientes', 1, 1, 'C', true);

// Restablecer colores para el contenido de la tabla
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->SetFillColor(245, 245, 245); // Fondo gris claro

// Contenido de la tabla
$fill = false;
foreach ($inventory_with_returns_and_liquidations as $item) {
    $pdf->Cell(70, 10, utf8_decode(remove_junk($item['product_name'])), 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, (int)$item['quantity'], 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, (int)$item['liquidated_quantity'], 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, (int)$item['returned_quantity'], 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, (int)$item['pending_quantity'], 1, 1, 'C', $fill);
    $fill = !$fill;
}

if (empty($inventory_with_returns_and_liquidations)) {
    $pdf->Cell(0, 10, 'No hay productos en esta obra.', 1, 1, 'C');
}

// Output the PDF
$pdf->Output('D', 'Obra_' .$obra['operacion'] . ' - ' . $obra['oei'] . ' - ' . $obra['oe'] . ' - ' . $obra['central'] . ' - ' . $obra['ruta'] . ' - ' . $obra['pep'] . '.pdf');
?>

