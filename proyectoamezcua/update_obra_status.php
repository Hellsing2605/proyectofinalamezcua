<?php
require_once('includes/load.php');

function update_obra_status($obra_id) {
    global $db;
    $obra = find_by_id('obras', $obra_id);
    if (!$obra) {
        return false;
    }

    // Verificar si la obra ya está cerrada
    if ($obra['status'] == '0') { // 0 representa 'cerrada'
        return "La obra ya está cerrada.";
    }

    $inventory = find_obras_inventory($obra_id);
    $returns = find_obras_returns($obra_id);
    $liquidations = find_obras_liquidations($obra_id);

    $total_inventory = 0;
    $total_returns = 0;
    $total_liquidations = 0;

    foreach ($inventory as $item) {
        $total_inventory += (int)$item['quantity'];
    }

    foreach ($returns as $item) {
        $total_returns += (int)$item['quantity'];
    }

    foreach ($liquidations as $item) {
        $total_liquidations += (int)$item['quantity'];
    }

    $total_returns_and_liquidations = $total_returns + $total_liquidations;

    // Asegurarse de que hay inventario antes de poder cerrar la obra
    if ($total_inventory > 0 && $total_inventory == $total_returns_and_liquidations) {
        $obra_status = '0'; // 0 representa 'cerrada'
        $query  = "UPDATE obras SET status = '{$obra_status}' WHERE id = '{$obra_id}'";
        return $db->query($query) ? true : "Error al actualizar el estado de la obra en la base de datos.";
    }
    return "La obra no cumple con las condiciones para ser cerrada.";
}

if (isset($_GET['id'])) {
    $obra_id = (int)$_GET['id'];
    $result = update_obra_status($obra_id);
    if ($result === true) {
        $session->msg("s", "El estado de la obra ha sido actualizado correctamente.");
    } else {
        $session->msg("d", "Error: " . $result);
    }
    redirect('obras.php');
} else {
    $session->msg("d", "ID de la obra no proporcionado.");
    redirect('obras.php');
}
?>


