<style>
thead th {
            background-color: #333; /* Fondo negro claro */
            color: white; /* Texto blanco */
        }
</style>

<?php
$page_title = 'Lista de Devoluciones';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

$returns = join_return_table();

// Inicializar variables
$date_selected = isset($_POST['date']) ? $_POST['date'] : '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

// Definir el número de registros por página
$records_per_page = 10;
$offset = ($current_page - 1) * $records_per_page;

if (!empty($date_selected)) {
    // Filtrar recepciones por fecha seleccionada y aplicar paginación
    $returns = find_return_by_date_with_pagination($date_selected, $offset, $records_per_page);
    // Contar el total de recepciones filtradas por fecha
    $total_returns = count_returns_by_date($date_selected);
} else {
    // Mostrar todas las recepciones con paginación
    $returns = join_return_table_pagination($offset, $records_per_page);
    // Contar el total de recepciones
    $total_returns = count_by_id('returns');
}

// Calcular el número total de páginas
$total_pages = ceil($total_returns['total'] / $records_per_page);
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
            <form method="post" action="returns.php" class="form-inline pull-left">
                    <div class="form-group">
                        <label for="date">Seleccionar Fecha:</label>
                        <input type="date" class="form-control" name="date" value="<?php echo $date_selected; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </form>
            </div>
            <div class="panel-body">
            <div class="col-md-8 col-md-offset-2">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th class="text-center"> Código Devolución </th>
                                <th class="text-center" style="width: 15%;"> Fecha </th>
                                <th class="text-center" style="width: 100px;"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($returns as $return): ?>
                            <tr>
                                <td class="text-center"><?php echo count_id(); ?></td>
                                <td class="text-center"><?php echo remove_junk($return['return_code']); ?></td>
                                <td class="text-center"><?php echo read_date($return['date']); ?></td>
                                <td class="actions-column">
                                    <div class="btn-group">
                                        <a href="view_returns.php?id=<?php echo (int)$return['id']; ?>" class="btn btn-info btn-xs" title="Ver detalles" data-toggle="tooltip">
                                            <span class="glyphicon glyphicon-eye-open"></span>
                                        </a>
                                        <a href="delete_return.php?id=<?php echo (int)$return['id']; ?>" class="btn btn-danger btn-xs" title="Eliminar" data-toggle="tooltip" onclick="return confirm('¿Estás seguro de que quieres eliminar esta devolución?');">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                        <a href="generate_pdf_returns.php?id=<?php echo (int)$return['id']; ?>" class="btn btn-warning btn-xs" title="Imprimir PDF" data-toggle="tooltip" target="_blank" download>
                                            <span class="glyphicon glyphicon-print"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item <?php if($current_page <= 1){ echo 'disabled'; } ?>">
                                    <a class="page-link" href="?page=1">Primero</a>
                                </li>
                                <li class="page-item <?php if($current_page <= 1){ echo 'disabled'; } ?>">
                                    <a class="page-link" href="<?php if($current_page > 1){ echo "?page=" . ($current_page - 1); } ?>">Anterior</a>
                                </li>
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php if($i == $current_page){ echo 'active'; } ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php if($current_page >= $total_pages){ echo 'disabled'; } ?>">
                                    <a class="page-link" href="<?php if($current_page < $total_pages){ echo "?page=" . ($current_page + 1); } ?>">Siguiente</a>
                                </li>
                                <li class="page-item <?php if($current_page >= $total_pages){ echo 'disabled'; } ?>">
                                    <a class="page-link" href="?page=<?php echo $total_pages; ?>">Último</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
            </div>
        </div>
    </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>
