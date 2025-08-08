<?php
$page_title = 'Detalles de Traspaso';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

if (isset($_GET['id'])) {
    $transfer_id = (int)$_GET['id'];
    $transfer = find_transfer_by_id($transfer_id);
    if (!$transfer) {
        $session->msg("d", "Traspaso no encontrado.");
        redirect('transfers.php');
    }

    // Obtener los productos y cantidades asociados a este traslado
    $transfer_items = find_transfer_items($transfer_id);
} else {
    $session->msg("d", "ID de traspaso no proporcionado.");
    redirect('transfers.php');
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
                    <a href="transfers.php" class="btn btn-primary">Volver a Traspasos</a>
                </div>
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Detalles del Traspaso</span>
                </strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Código de Traspaso</th>
                            <td><?php echo remove_junk($transfer['transfer_code']); ?></td>
                        </tr>
                        <tr>
                            <th>Supervisor</th>
                            <td><?php echo remove_junk($transfer['supervisor_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha</th>
                            <td><?php echo read_date($transfer['date']); ?></td>
                        </tr>
                        <tr>
                            <th>Almacén</th>
                            <td><?php echo remove_junk(find_by_id('warehouses', $transfer['warehouse_id'])['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Traspaso a</th>
                            <td>
                                <?php 
                                if (!empty($transfer['technician_name'])) {
                                    echo remove_junk($transfer['technician_name']);
                                } elseif (!empty($transfer['cuadrilla_name'])) {
                                    echo remove_junk($transfer['cuadrilla_name']);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php if (!empty($transfer['obra_name'])): ?>
                        <tr>
                            <th>Operación</th>
                            <td><?php echo remove_junk($transfer['operacion']); ?></td>
                        </tr>
                        <tr>
                            <th>OEI</th>
                            <td><?php echo remove_junk($transfer['oei']); ?></td>
                        </tr>
                        <tr>
                            <th>OE</th>
                            <td><?php echo remove_junk($transfer['obra_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Central</th>
                            <td><?php echo remove_junk($transfer['central']); ?></td>
                        </tr>
                        <tr>
                            <th>Ruta</th>
                            <td><?php echo remove_junk($transfer['ruta']); ?></td>
                        </tr>
                        <tr>
                            <th>PEP</th>
                            <td><?php echo remove_junk($transfer['pep']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Observaciones</th>
                            <td><?php echo remove_junk($transfer['observations']); ?></td>
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
                        <?php if ($transfer_items): ?>
                            <?php foreach ($transfer_items as $item): ?>
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
                                <td colspan="3">No hay materiales asociados con este traspaso.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>








