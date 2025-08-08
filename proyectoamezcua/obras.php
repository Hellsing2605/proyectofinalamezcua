<?php
$page_title = 'Obras';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

$obras = find_all('obras');
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
        .status-abierta {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }
        .status-cerrada {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>

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
                    <span>Obras</span>
                </strong>
            </div>
            <div class="panel-body">
                <div class="table-container">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Operación</th>
                                <th class="text-center">OEI</th>
                                <th class="text-center">OE</th>
                                <th class="text-center">Central</th>
                                <th class="text-center">Ruta</th>
                                <th class="text-center">PEP</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($obras as $obra): ?>
                                <tr>
                                    <td class="text-center"><?php echo (int)$obra['id']; ?></td>
                                    <td class="text-center"><?php echo remove_junk($obra['operacion']); ?></td>
                                    <td class="text-center"><?php echo remove_junk($obra['oei']); ?></td>
                                    <td class="text-center"><?php echo remove_junk($obra['oe']); ?></td>
                                    <td class="text-center"><?php echo remove_junk($obra['central']); ?></td>
                                    <td class="text-center"><?php echo remove_junk($obra['ruta']); ?></td>
                                    <td class="text-center"><?php echo remove_junk($obra['pep']); ?></td>
                                    <td class="text-center">
                                        <?php if ($obra['status'] == 1): ?>
                                            <span class="status-abierta">Abierta</span>
                                        <?php else: ?>
                                            <span class="status-cerrada">Cerrada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="view_obras.php?id=<?php echo (int)$obra['id']; ?>" class="btn btn-info">Ver Detalles</a>
                                        <a href="update_obra_status.php?id=<?php echo (int)$obra['id']; ?>" class="btn btn-warning">Actualizar Estatus</a>
                                        <a href="generate_pdf_obras.php?id=<?php echo (int)$obra['id']; ?>" class="btn btn-warning btn-xs" title="Imprimir PDF" data-toggle="tooltip" target="_blank" download>
                                            <span class="glyphicon glyphicon-print"></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($obras)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No hay obras disponibles.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>










