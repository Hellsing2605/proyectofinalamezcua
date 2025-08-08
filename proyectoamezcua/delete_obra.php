<?php
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

// Comprobar si se recibió el ID de la obra
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $session->msg("d","ID de la obra falta o no es válido.");
    redirect('obras.php');
}

// Obtener el ID de la obra de la URL y buscar la obra correspondiente en la base de datos
$obra = find_by_id('obras', (int)$_GET['id']);

// Verificar si la obra fue encontrada
if(!$obra){
    $session->msg("d","Obra no encontrada.");
    redirect('add_obras.php');
}

// Eliminar la obra
$delete_result = delete_by_id('obras', $obra['id']);

// Verificar si la eliminación fue exitosa
if($delete_result){
    $session->msg("s","Obra eliminada exitosamente.");
    redirect('add_obras.php');
} else {
    $session->msg("d","Error al eliminar la obra.");
    redirect('add_obras.php');
}
?>
