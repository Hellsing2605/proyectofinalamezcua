<?php
$page_title = 'Detalles de Retorno';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

if (isset($_GET['id'])) {
    $return_id = (int)$_GET['id'];
    $return = find_return_by_id($return_id);
    if (!$return) {
        $session->msg("d", "Retorno no encontrado.");
        redirect('returns.php');
    }

    // Obtener los productos y cantidades asociados a este retorno
    $return_items = find_return_items($return_id);
} else {
    $session->msg("d", "ID de retorno no proporcionado.");
    redirect('returns.php');
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
                    <a href="returns.php" class="btn btn-primary">Volver a Devoluciones</a>
                </div>
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Detalles de la Devolución</span>
                </strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Código de la Devolución</th>
                            <td><?php echo remove_junk($return['return_code']); ?></td>
                        </tr>
                        <tr>
                            <th>Supervisor</th>
                            <td><?php echo remove_junk($return['supervisor_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha</th>
                            <td><?php echo read_date($return['date']); ?></td>
                        </tr>
                        <tr>
                            <th>Almacén</th>
                            <td><?php echo remove_junk(find_by_id('warehouses', $return['warehouse_id'])['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Devolución por parte de</th>
                            <td>
                                <?php 
                                if (!empty($return['technician_name'])) {
                                    echo remove_junk($return['technician_name']);
                                } elseif (!empty($return['cuadrilla_name'])) {
                                    echo remove_junk($return['cuadrilla_name']);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php if (!empty($return['obra_name'])): ?>
                        <tr>
                            <th>Operación</th>
                            <td><?php echo remove_junk($return['operacion']); ?></td>
                        </tr>
                        <tr>
                            <th>OEI</th>
                            <td><?php echo remove_junk($return['oei']); ?></td>
                        </tr>
                        <tr>
                            <th>OE</th>
                            <td><?php echo remove_junk($return['obra_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Central</th>
                            <td><?php echo remove_junk($return['central']); ?></td>
                        </tr>
                        <tr>
                            <th>Ruta</th>
                            <td><?php echo remove_junk($return['ruta']); ?></td>
                        </tr>
                        <tr>
                            <th>PEP</th>
                            <td><?php echo remove_junk($return['pep']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Observaciones</th>
                            <td><?php echo remove_junk($return['observations']); ?></td>
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
                        <?php if ($return_items): ?>
                            <?php foreach ($return_items as $item): ?>
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
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>




