<?php
$page_title = 'Editar Solicitud';
require_once('includes/load.php');
// Check what level user has permission to view this page
page_require_level(2);

// Check if the request ID is provided
if (isset($_GET['id'])) {
    $request_id = (int)$_GET['id'];
    $request = find_by_id('requests', $request_id);
    $request_items = find_all('request_items WHERE request_id = ' . $request_id);
    $products = join_product_table(); // Assuming this function gets all products
} else {
    $session->msg('d', 'Falta el ID de la solicitud.');
    redirect('requests.php');
}

// Handle form submission
if (isset($_POST['edit_request'])) {
    $req_fields = array('request-code', 'request-name', 'date', 'warehouse-name', 'request-to', 'observations');
    validate_fields($req_fields);

    if (empty($errors)) {
        // Obtener los datos del formulario
        $r_code  = remove_junk($db->escape($_POST['request-code']));
        $r_name  = remove_junk($db->escape($_POST['request-name']));
        $r_date  = remove_junk($db->escape($_POST['date']));
        $warehouse_name = remove_junk($db->escape($_POST['warehouse-name']));
        $request_to = remove_junk($db->escape($_POST['request-to']));
        $observations = remove_junk($db->escape($_POST['observations']));

        // Actualizar la solicitud en la base de datos
        $query  = "UPDATE requests SET ";
        $query .= "request_code='{$r_code}', request_name='{$r_name}', date='{$r_date}', warehouse_name='{$warehouse_name}', request_to='{$request_to}', observations='{$observations}' ";
        $query .= "WHERE id='{$request_id}'";
        if ($db->query($query)) {
            // Actualizar los productos y cantidades en request_items
            $db->query("DELETE FROM request_items WHERE request_id='{$request_id}'");

            foreach ($_POST['product'] as $index => $product_id) {
                $quantity = (int)$_POST['quantity'][$index];
                if ($product_id && $quantity) {
                    $query  = "INSERT INTO request_items (request_id, product_id, quantity) VALUES (";
                    $query .= " '{$request_id}', '{$product_id}', '{$quantity}'";
                    $query .= ")";
                    $db->query($query);
                }
            }

            $session->msg('s', "Solicitud actualizada exitosamente.");
            redirect('requests.php', false); // Asegúrate de redirigir aquí
        } else {
            $session->msg('d', 'Lo siento, la actualización falló.');
            redirect('edit_request.php?id=' . $request_id, false); // Redirigir en caso de error
        } } else{
            $session->msg("d", $errors);
            redirect('edit_request.php?id='.$request_id, false);
        }
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
                    <span>Editar Solicitud</span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="edit_request.php?id=<?php echo (int)$request['id']; ?>">
                    <div class="form-group">
                        <label for="request-code">Código de Petición</label>
                        <input type="text" class="form-control" name="request-code" value="<?php echo remove_junk($request['request_code']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="request-name">Nombre de la Petición</label>
                        <input type="text" class="form-control" name="request-name" value="<?php echo remove_junk($request['request_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Fecha</label>
                        <input type="date" class="form-control" name="date" value="<?php echo remove_junk($request['date']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="warehouse-name">Nombre del almacén</label>
                        <input type="text" class="form-control" name="warehouse-name" value="<?php echo remove_junk($request['warehouse_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="request-to">Nombre de quien hace la petición</label>
                        <input type="text" class="form-control" name="request-to" value="<?php echo remove_junk($request['request_to']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="observations">Observaciones</label>
                        <textarea class="form-control" name="observations"><?php echo remove_junk($request['observations']); ?></textarea>
                    </div>
                    <div id="materials-container">
                        <?php if ($request_items): ?>
                            <?php foreach ($request_items as $index => $item): ?>
                                <div class="material-row form-group">
                                    <label for="product">Producto</label>
                                    <select class="form-control" name="product[]">
                                        <option value="">Selecciona un producto</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?php echo $product['id']; ?>" <?php if($product['id'] == $item['product_id']) echo 'selected'; ?>>
                                                <?php echo $product['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="quantity">Cantidad</label>
                                    <input type="number" class="form-control" name="quantity[]" value="<?php echo (int)$item['quantity']; ?>" required>
                                    <button type="button" class="btn btn-danger remove-material">Eliminar</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-success" id="add-material">Agregar Material</button>
                    <button type="submit" name="edit_request" class="btn btn-primary pull-right">Actualizar Petición</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>

<script>
// Agregar nuevo material
document.getElementById('add-material').addEventListener('click', function() {
    const container = document.getElementById('materials-container');
    const newRow = document.createElement('div');
    newRow.classList.add('material-row', 'form-group');
    newRow.innerHTML = `
        <label for="product">Producto</label>
        <select class="form-control" name="product[]">
            <option value="">Selecciona un producto</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="quantity">Cantidad</label>
        <br><input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required>
        <br><button type="button" class="btn btn-danger remove-material">Eliminar</button>
    `;
    container.appendChild(newRow);
});

// Eliminar material
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('remove-material')) {
        event.target.closest('.material-row').remove();
    }
});
</script>
