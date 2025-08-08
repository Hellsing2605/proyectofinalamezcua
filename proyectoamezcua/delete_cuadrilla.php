<?php
require_once('includes/load.php');


// Verificar si se proporcionó un ID de técnico
if (isset($_GET['id'])) {
    $cuadrilla_id = (int)$_GET['id'];
    
    // Eliminar los registros relacionados en la tabla `transfers`
    $delete_transfers_query = "DELETE FROM transfers WHERE cuadrilla_id = '{$cuadrilla_id}'";
    if ($db->query($delete_transfers_query)) {
        // Ahora eliminar la cuadrilla de la tabla `cuadrillas`
        $delete_cuadrilla_query = "DELETE FROM cuadrillas WHERE id = '{$cuadrilla_id}'";
        if ($db->query($delete_cuadrilla_query)) {
            $session->msg("s", "Cuadrilla eliminada exitosamente.");
        } else {
            $session->msg("d", "Error al eliminar cuadrilla.");
        }
    } else {
        $session->msg("d", "Error al eliminar los registros relacionados en transfers.");
    }
} else {
    $session->msg("d", "ID de la cuadrilla no proporcionado.");
}

// Redirigir a la página de cuadrillas después de la eliminación
redirect('cuadrillas.php');

?>