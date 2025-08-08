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

<?php
$page_title = 'Ver Sub-Almacenes';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

$technicians = find_all('technicians');
$cuadrillas = find_all('cuadrillas');
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
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Sub-Almacenes Bajantes</span>
                </strong>
            </div>
            <div class="panel-body">
                <div class="table-container">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-center">Técnico</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($technicians as $technician): ?>
                                <tr>
                                    <td class="text-center"><?php echo remove_junk($technician['name']); ?></td>
                                    <td class="text-center">
                                        <a href="view_sub_warehouses.php?id=<?php echo (int)$technician['id']; ?>&type=technician" class="btn btn-info">Ver Inventario</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-center">Cuadrilla</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cuadrillas as $cuadrilla): ?>
                                <tr>
                                    <td class="text-center"><?php echo remove_junk($cuadrilla['name']); ?></td>
                                    <td class="text-center">
                                        <a href="view_sub_warehouses_c.php?id=<?php echo (int)$cuadrilla['id']; ?>&type=cuadrilla" class="btn btn-info">Ver Inventario</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($_GET['id']) && isset($_GET['type'])): ?>
        <?php
        $id = (int)$_GET['id'];
        $type = $_GET['type'];
        if ($type == 'technician') {
            $inventory = find_technician_inventory($id);
            $entity = find_by_id('technicians', $id);
            $entity_name = 'Inventario de ' . remove_junk($entity['name']);
        } else if ($type == 'cuadrilla') {
            $inventory = find_cuadrilla_inventory($id);
            $entity = find_by_id('cuadrillas', $id);
            $entity_name = 'Inventario de ' . remove_junk($entity['name']);
        }
        ?>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>
                        <span class="glyphicon glyphicon-th"></span>
                        <span><?php echo $entity_name; ?></span>
                    </strong>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory as $item): ?>
                                <tr>
                                    <td><?php echo remove_junk($item['product_name']); ?></td>
                                    <td><?php echo (int)$item['quantity']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($inventory)): ?>
                                <tr>
                                    <td colspan="2">No hay productos en este sub-almacén.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include_once('layouts/footer.php'); ?>






