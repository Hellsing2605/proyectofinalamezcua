<?php
require_once('includes/load.php');


// Verificar si se proporcionó un ID de técnico
if (isset($_GET['id'])) {
    $technician_id = (int)$_GET['id'];
    
    // Eliminar los registros relacionados en la tabla `transfers`
    $delete_transfers_query = "DELETE FROM transfers WHERE technician_id = '{$technician_id}'";
    if ($db->query($delete_transfers_query)) {
        // Ahora eliminar el técnico de la tabla `technicians`
        $delete_technician_query = "DELETE FROM technicians WHERE id = '{$technician_id}'";
        if ($db->query($delete_technician_query)) {
            $session->msg("s", "Técnico eliminado exitosamente.");
        } else {
            $session->msg("d", "Error al eliminar el técnico.");
        }
    } else {
        $session->msg("d", "Error al eliminar los registros relacionados en transfers.");
    }
} else {
    $session->msg("d", "ID del técnico no proporcionado.");
}

// Redirigir a la página de técnicos después de la eliminación
redirect('technicians.php');

?>
