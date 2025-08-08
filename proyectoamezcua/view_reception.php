<?php
$page_title = 'Detalles de Recepción';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

if (isset($_GET['id'])) {
    $reception_id = (int)$_GET['id'];
    $reception = find_by_id('receptions', $reception_id);
    if (!$reception) {
        $session->msg("d", "Recepción no encontrada.");
        redirect('receptions.php');
    }

    // Obtener los productos y cantidades asociados a esta recepción
    $reception_items = find_reception_items($reception_id);
} else {
    $session->msg("d", "ID de recepción no proporcionado.");
    redirect('receptions.php');
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
                    <a href="receptions.php" class="btn btn-primary">Volver a Recepciones</a>
                </div>
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Detalles de la Recepción</span>
                </strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Código de Recepción</th>
                            <td><?php echo remove_junk($reception['reception_code']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha</th>
                            <td><?php echo read_date($reception['date']); ?></td>
                        </tr>
                        <tr>
                            <th>Almacén</th>
                            <td><?php echo remove_junk(find_by_id('warehouses', $reception['warehouse_id'])['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Supervisor</th>
                            <td><?php echo remove_junk(find_by_id('supervisors', $reception['supervisor_id'])['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Observaciones</th>
                            <td><?php echo remove_junk($reception['observations']); ?></td>
                        </tr>
                        <tr>
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
                        <?php if ($reception_items): ?>
                            <?php foreach ($reception_items as $item): ?>
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
                                <td colspan="3">No hay materiales asociados con este retorno.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>

