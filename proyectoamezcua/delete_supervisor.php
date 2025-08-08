<?php
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

// Comprobar si se recibió el ID del supervisor
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $session->msg("d","ID del supervisor falta o no es válido.");
    redirect('supervisors.php');
}

// Obtener el ID del supervisor de la URL y buscar el supervisor correspondiente en la base de datos
$supervisor = find_by_id('supervisors', (int)$_GET['id']);

// Verificar si el supervisor fue encontrado
if(!$supervisor){
    $session->msg("d","Supervisor no encontrado.");
    redirect('supervisors.php');
}

// Eliminar el supervisor
$delete_result = delete_by_id('supervisors', $supervisor['id']);

// Verificar si la eliminación fue exitosa
if($delete_result){
    $session->msg("s","Supervisor eliminado exitosamente.");
    redirect('supervisors.php');
} else {
    $session->msg("d","Error al eliminar el supervisor.");
    redirect('supervisors.php');
}
?>
