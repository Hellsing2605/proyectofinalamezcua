<?php
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

// Comprobar si se recibió el ID del almacén
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $session->msg("d","ID del almacén falta o no es válido.");
    redirect('warehouses.php');
}

// Obtener el ID del almacén de la URL y buscar el almacén correspondiente en la base de datos
$warehouse = find_by_id('warehouses', (int)$_GET['id']);

// Verificar si el almacén fue encontrado
if(!$warehouse){
    $session->msg("d","Almacén no encontrado.");
    redirect('warehouses.php');
}

// Eliminar el almacén
$delete_result = delete_by_id('warehouses', $warehouse['id']);

// Verificar si la eliminación fue exitosa
if($delete_result){
    $session->msg("s","Almacén eliminado exitosamente.");
    redirect('warehouses.php');
} else {
    $session->msg("d","Error al eliminar el almacén.");
    redirect('warehouses.php');
}
?>

