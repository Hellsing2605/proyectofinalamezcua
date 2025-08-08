<head>
    <style>
        .custom-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: #6a0dad; /* Color de fondo morado */
            color: white;
            padding: 10px 20px; /* Espaciado interno */
            border: none;
            border-radius: 30px; /* Borde redondeado */
            font-size: 16px; /* Tamaño de fuente */
            width: 100%; /* Ancho completo */
            display: inline-block;
            position: relative;
            cursor: pointer;
            outline: none;
            text-align: center;
            transition: background-color 0.3s ease;
            line-height: 1.5; /* Altura de línea */
        }

        .custom-select.accepted {
            background-color: #28a745; /* Color verde bonito para aceptado */
        }

        .custom-select:focus {
            background-color: #5a0aab; /* Cambio de color al hacer foco */
        }

        .custom-select option {
            color: black; /* Color de texto de las opciones */
        }

        .btn-group {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        td.actions-column {
            text-align: center;
        }

        .btn-group .btn {
            margin: 0 2px; /* Espacio entre botones */
        }
        thead th {
            background-color: #333; /* Fondo negro claro */
            color: white; /* Texto blanco */
        }
    </style>
    <script>
        function updateSelectBackground(selectElement) {
            if (selectElement.value === 'aceptado') {
                selectElement.classList.add('accepted');
            } else {
                selectElement.classList.remove('accepted');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var selectElements = document.querySelectorAll('select[name="status"]');
            selectElements.forEach(function(selectElement) {
                updateSelectBackground(selectElement);
                selectElement.addEventListener('change', function() {
                    updateSelectBackground(selectElement);
                });
            });
        });
    </script>
</head>
<?php
$page_title = 'Lista de Requests';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

$requests = join_request_table();

if (isset($_POST['update_status'])) {
    $request_id = (int)$_POST['request_id'];
    $new_status = $db->escape($_POST['status']);
    $query = "UPDATE requests SET status='{$new_status}' WHERE id='{$request_id}'";
    if ($db->query($query)) {
        $session->msg('s', "Estatus actualizado.");
    } else {
        $session->msg('d', 'Lo siento, actualización falló.');
    }
    redirect('requests.php');
}

?>
<?php include_once('layouts/header.php'); ?>
<div class="row justify-content-center">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="panel panel-default table-centered">
            <div class="panel-heading clearfix">
                <div class="pull-right">
                    <a href="add_request.php" class="btn btn-primary">Agregar Petición</a>
                </div>
            </div>
            <div class="panel-body">
            <div class="col-md-8 col-md-offset-2">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th class="text-center"> Código Petición </th>
                                <th class="text-center"> Nombre de la petición </th>
                                <th class="text-center" style="width: 15%;"> Fecha </th>
                                <th class="text-center" style="width: 19%;"> Estatus </th>
                                <th class="text-center" style="width: 100px;"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                            <tr>
                                <td class="text-center"><?php echo count_id(); ?></td>
                                <td class="text-center"><?php echo remove_junk($request['request_code']); ?></td>
                                <td class="text-center"><?php echo remove_junk($request['request_name']); ?></td>
                                <td class="text-center"><?php echo read_date($request['date']); ?></td>
                                <td>
                                    <form method="post" action="requests.php">
                                        <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                        <select name="status" class="form-control custom-select" onchange="this.form.submit(); updateSelectBackground(this);">
                                            <option value="en proceso" <?php if($request['status'] == 'en proceso') echo 'selected'; ?>>En proceso</option>
                                            <option value="aceptado" <?php if($request['status'] == 'aceptado') echo 'selected'; ?>>Aceptado</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>

                                <td class="actions-column">
                                    <div class="btn-group">
                                        <a href="view_requests.php?id=<?php echo (int)$request['id']; ?>" class="btn btn-info btn-xs" title="Ver detalles" data-toggle="tooltip">
                                            <span class="glyphicon glyphicon-eye-open"></span>
                                        </a>
                                        <a href="delete_request.php?id=<?php echo (int)$request['id']; ?>" class="btn btn-danger btn-xs" title="Eliminar" data-toggle="tooltip" onclick="return confirm('¿Estás seguro de que quieres eliminar este request?');">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>





