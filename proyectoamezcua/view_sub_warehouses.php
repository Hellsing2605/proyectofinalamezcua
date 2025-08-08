<head>
    <style>
        thead th {
            background-color: #333; /* Fondo negro claro */
            color: white; /* Texto blanco */
        }
    </style>
</head>
<?php
$page_title = 'Ver Inventario de Sub-Almacén Bajantes';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

if (isset($_GET['id'])) {
    $technician_id = (int)$_GET['id'];
    $technician = find_by_id('technicians', $technician_id);
    if (!$technician) {
        $session->msg("d", "ID del técnico no encontrado.");
        redirect('sub_warehouses.php');
    }

    $inventory = find_technician_inventory($technician_id);
    $returns = find_technician_returns($technician_id);
    $liquidations = find_technician_liquidations($technician_id);

    // Combinar inventario, devoluciones y liquidaciones en un solo array
    $inventory_with_returns_and_liquidations = [];
    foreach ($inventory as $item) {
        $inventory_with_returns_and_liquidations[$item['product_id']] = [
            'product_name' => $item['product_name'],
            'quantity' => (int)$item['quantity'],
            'returned_quantity' => 0,
            'liquidated_quantity' => 0
        ];
    }
    foreach ($returns as $item) {
        if (isset($inventory_with_returns_and_liquidations[$item['product_id']])) {
            $inventory_with_returns_and_liquidations[$item['product_id']]['returned_quantity'] = (int)$item['quantity'];
            // Restar la cantidad de devueltos del inventario
            $inventory_with_returns_and_liquidations[$item['product_id']]['quantity'] -= (int)$item['quantity'];
        } else {
            $inventory_with_returns_and_liquidations[$item['product_id']] = [
                'product_name' => $item['product_name'],
                'quantity' => -1 * (int)$item['quantity'], // Asumimos que no hay inventario inicial, por lo que se resta
                'returned_quantity' => (int)$item['quantity'],
                'liquidated_quantity' => 0
            ];
        }
    }
    foreach ($liquidations as $item) {
        if (isset($inventory_with_returns_and_liquidations[$item['product_id']])) {
            $inventory_with_returns_and_liquidations[$item['product_id']]['liquidated_quantity'] = (int)$item['quantity'];
            $inventory_with_returns_and_liquidations[$item['product_id']]['quantity'] -= (int)$item['quantity'];
        } else {
            $inventory_with_returns_and_liquidations[$item['product_id']] = [
                'product_name' => $item['product_name'],
                'quantity' => -1 * (int)$item['quantity'],
                'returned_quantity' => 0,
                'liquidated_quantity' => (int)$item['quantity']
            ];
        }
    }
    
} else {
    $session->msg("d", "ID del técnico no proporcionado.");
    redirect('sub_warehouses.php');
}

?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default table-centered">
            <div class="panel-heading table-centered">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Inventario de <?php echo remove_junk($technician['name']); ?></span>
                </strong>
                <div class="pull-right">
                    <a href="sub_warehouses.php" class="btn btn-primary">Volver a Sub-Almacenes</a>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Devoluciones</th>
                            <th class="text-center">Liquidaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory_with_returns_and_liquidations as $item): ?>
                            <tr>
                                <td class="text-center"><?php echo remove_junk($item['product_name']); ?></td>
                                <td class="text-center"><?php echo (int)$item['quantity']; ?></td>
                                <td class="text-center"><?php echo (int)$item['returned_quantity']; ?></td>
                                <td class="text-center"><?php echo (int)$item['liquidated_quantity']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($inventory_with_returns_and_liquidations)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay productos en este sub-almacén.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>
