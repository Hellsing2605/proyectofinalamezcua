<?php
$page_title = 'Detalles de Traslado';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

if (isset($_GET['id'])) {
    $request_id = (int)$_GET['id'];
    $request = find_by_id('requests', $request_id);
    if (!$request) {
        $session->msg("d", "Petición no encontrado.");
        redirect('requests.php');
    }

    // Obtener los productos y cantidades asociados a este traslado
    $request_items = find_request_items($request_id);
} else {
    $session->msg("d", "ID de petición no proporcionado.");
    redirect('requests.php');
}
?>

<?php include_once('layouts/header.php'); ?>

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
                    <a href="requests.php" class="btn btn-primary">Volver a Peticiones</a>
                </div>
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Detalles de la Petición</span>
                </strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Código de la Petición</th>
                            <td><?php echo remove_junk($request['request_code']); ?></td>
                        </tr>
                        <tr>
                            <th>Nombre de la petición</th>
                            <td><?php echo remove_junk($request['request_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha</th>
                            <td><?php echo read_date($request['date']); ?></td>
                        </tr>
                        <tr>
                            <th>Nombre del almacén</th>
                            <td><?php echo remove_junk($request['warehouse_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Nombre de quien realizó la petición: </th>
                            <td><?php echo remove_junk($request['request_to']); ?></td>
                        </tr>
                        <tr>
                            <th>Observaciones</th>
                            <td><?php echo remove_junk($request['observations']); ?></td>
                        </tr>
                        <tr>
                            <th>Materiales</th>
                            <td>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Material</th>
                                            <th class="text-center">Unidad</th>
                                            <th>Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($request_items): ?>
                                            <?php foreach ($request_items as $item): ?>
                                                <tr>
                                                    <td><?php echo remove_junk(find_by_id('products', $item['product_id'])['name']); ?></td>
                                                    <td><?php echo remove_junk($item['category_name']); ?></td>
                                                    <td><?php echo (int)$item['quantity']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2">No hay materiales asociados con esta petición.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>
