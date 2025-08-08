<?php
$page_title = 'Detalles de Liquidación';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

if (isset($_GET['id'])) {
    $liquidation_id = (int)$_GET['id'];
    $liquidation = find_liquidation_by_id($liquidation_id);
    if (!$liquidation) {
        $session->msg("d", "Liquidación no encontrada.");
        redirect('liquidations.php');
    }

    // Obtener los productos y cantidades asociados a esta liquidación
    $liquidation_items = find_liquidation_items($liquidation_id);
} else {
    $session->msg("d", "ID de liquidación no proporcionado.");
    redirect('liquidations.php');
}
?>

<?php include_once('layouts/header.php'); ?>
<style>
    .table thead th {
        background-color: #51aded; /* Fondo azul */
        color: white; /* Texto blanco */
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #dee2e6; /* Borde gris */
    }
    .table td {
        text-align: center; /* Centrar texto */
    }
</style>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="pull-right">
                    <a href="liquidations.php" class="btn btn-primary">Volver a Liquidaciones</a>
                </div>
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Detalles de la Liquidación</span>
                </strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Código de Liquidación</th>
                            <td><?php echo remove_junk($liquidation['liquidation_code']); ?></td>
                        </tr>
                        <tr>
                            <th>Nombre del Supervisor</th>
                            <td><?php echo remove_junk($liquidation['supervisor_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha</th>
                            <td><?php echo read_date($liquidation['date']); ?></td>
                        </tr>
                        <tr>
                            <th>Almacén</th>
                            <td><?php echo remove_junk(find_by_id('warehouses', $liquidation['warehouse_id'])['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Liquidación de material por parte de</th>
                            <td>
                                <?php 
                                if (!empty($liquidation['technician_name'])) {
                                    echo remove_junk($liquidation['technician_name']);
                                } elseif (!empty($liquidation['cuadrilla_name'])) {
                                    echo remove_junk($liquidation['cuadrilla_name']);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php if (!empty($liquidation['obra_name'])): ?>
                        <tr>
                            <th>Operación</th>
                            <td><?php echo remove_junk($liquidation['operacion']); ?></td>
                        </tr>
                        <tr>
                            <th>OEI</th>
                            <td><?php echo remove_junk($liquidation['oei']); ?></td>
                        </tr>
                        <tr>
                            <th>OE</th>
                            <td><?php echo remove_junk($liquidation['obra_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Central</th>
                            <td><?php echo remove_junk($liquidation['central']); ?></td>
                        </tr>
                        <tr>
                            <th>Ruta</th>
                            <td><?php echo remove_junk($liquidation['ruta']); ?></td>
                        </tr>
                        <tr>
                            <th>PEP</th>
                            <td><?php echo remove_junk($liquidation['pep']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Observaciones</th>
                            <td><?php echo remove_junk($liquidation['observations']); ?></td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">Código de Material</th>
                            <th class="text-center">Material</th>
                            <th class="text-center">Unidad</th>
                            <th class="text-center">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($liquidation_items): ?>
                            <?php foreach ($liquidation_items as $item): ?>
                                <?php 
                                    $product = find_by_id('products', $item['product_id']); 
                                ?>
                                <tr>
                                    <td><?php echo remove_junk($product['material_code']); ?></td>
                                    <td><?php echo remove_junk($product['name']); ?></td>
                                    <td><?php echo remove_junk($item['category_name']); ?></td>
                                    <td><?php echo (int)$item['quantity']; ?></td>
                                </tr>
                            <?php endforeach;?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No hay materiales asociados con esta liquidación.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>






