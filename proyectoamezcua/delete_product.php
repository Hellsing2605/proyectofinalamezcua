<?php
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);
?>

<?php
$product = find_by_id('products', (int)$_GET['id']);
if (!$product) {
    $session->msg("d", "ID vacío");
    redirect('product.php');
}

// Verificar si el producto tiene registros en transferencias, liquidaciones o devoluciones
$product_id = (int)$product['id'];

// Consultas para verificar registros asociados
$sql_transfer = "SELECT COUNT(*) AS count FROM transfer_items WHERE product_id = '{$product_id}'";
$sql_liquidation = "SELECT COUNT(*) AS count FROM liquidation_items WHERE product_id = '{$product_id}'";
$sql_return = "SELECT COUNT(*) AS count FROM return_items WHERE product_id = '{$product_id}'";

// Ejecutar las consultas
$transfer_count = $db->query($sql_transfer)->fetch_assoc()['count'];
$liquidation_count = $db->query($sql_liquidation)->fetch_assoc()['count'];
$return_count = $db->query($sql_return)->fetch_assoc()['count'];

// Si existen registros en alguna de las tablas, mostrar un mensaje de error y evitar la eliminación
if ($transfer_count > 0 || $liquidation_count > 0 || $return_count > 0) {
    $session->msg("d", "Error: no se puede eliminar un producto que haya sido trasladado, devuelto o liquidado, ya que existe registro y dichas acciones van ligadas.");
    redirect('product.php');
} else {
    // Si no hay registros asociados, proceder con la eliminación
    $delete_id = delete_by_id('products', $product_id);
    if ($delete_id) {
        $session->msg("s", "Producto eliminado");
        redirect('product.php');
    } else {
        $session->msg("d", "Eliminación falló");
        redirect('product.php');
    }
}
?>

