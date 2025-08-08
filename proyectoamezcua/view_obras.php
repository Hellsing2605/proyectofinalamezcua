<?php
$page_title = 'Ver Inventario de Obra';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

if (isset($_GET['id'])) {
    $obra_id = (int)$_GET['id'];
    $obra = find_by_id('obras', $obra_id);
    if (!$obra) {
        $session->msg("d", "ID de la obra no encontrado.");
        redirect('obras.php');
    }

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
            'pending_quantity' => (int)$item['quantity'] // Inicialmente, los pendientes son iguales a la cantidad.
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

?>

<?php include_once('layouts/header.php'); ?>
<head>
    <style>
        thead th {
            background-color: #333; /* Fondo negro claro */
            color: white; /* Texto blanco */
        }
        .table-container {
            display: flex;
            flex-direction: column;
        }
        .table {
            margin-bottom: 20px;
        }
        .panel-heading {
            background-color: #f8f8f8;
            border-bottom: 1px solid #ddd;
        }
        .panel-heading strong {
            font-size: 18px;
        }
    </style>
</head>
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
                    <span>Obra <?php echo remove_junk($obra['operacion'] . ' - ' . $obra['oei'] . ' - ' . $obra['oe'] . ' - ' . $obra['central'] . ' - ' . $obra['ruta'] . ' - ' . $obra['pep'] ) ?></span>
                </strong>
                <div class="pull-right">
                    <a href="obras.php" class="btn btn-primary">Volver a Obras</a>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Surtido</th>
                            <th class="text-center">Liquidaciones</th>
                            <th class="text-center">Devoluciones</th>
                            <th class="text-center">Pendientes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory_with_returns_and_liquidations as $item): ?>
                            <tr>
                                <td class="text-center"><?php echo remove_junk($item['product_name']); ?></td>
                                <td class="text-center"><?php echo (int)$item['quantity']; ?></td>
                                <td class="text-center"><?php echo (int)$item['liquidated_quantity']; ?></td>
                                <td class="text-center"><?php echo (int)$item['returned_quantity']; ?></td>
                                <td class="text-center"><?php echo (int)$item['pending_quantity']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($inventory_with_returns_and_liquidations)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay productos en esta obra.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>








