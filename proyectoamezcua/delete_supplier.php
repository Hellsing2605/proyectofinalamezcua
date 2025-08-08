<?php
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

// Comprobar si se recibió el ID del proveedor
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $session->msg("d","ID del proveedor falta o no es válido.");
    redirect('suppliers.php');
}

// Obtener el ID del proveedor de la URL y buscar el proveedor correspondiente en la base de datos
$supplier = find_by_id('suppliers', (int)$_GET['id']);

// Verificar si el proveedor fue encontrado
if(!$supplier){
    $session->msg("d","Proveedor no encontrado.");
    redirect('suppliers.php');
}

// Eliminar el proveedor
$delete_result = delete_by_id('suppliers', $supplier['id']);

// Verificar si la eliminación fue exitosa
if($delete_result){
    $session->msg("s","Proveedor eliminado exitosamente.");
    redirect('suppliers.php');
} else {
    $session->msg("d","Error al eliminar el proveedor.");
    redirect('suppliers.php');
}
?>
