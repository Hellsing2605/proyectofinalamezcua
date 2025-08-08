<?php
$page_title = 'Editar Traslado';
require_once('includes/load.php');
// Check what level user has permission to view this page
page_require_level(2);

// Check if the transfer ID is provided
if (isset($_GET['id'])) {
    $transfer_id = (int)$_GET['id'];
    $transfer = find_by_id('transfers', $transfer_id);
    $transfer_items = find_all('transfer_items WHERE transfer_id = ' . $transfer_id);
    $products = join_product_table(); // Assuming this function gets all products
} else {
    $session->msg('d', 'Falta el ID del traslado.');
    redirect('transfers.php');
}

// Handle form submission
if (isset($_POST['edit_transfer'])) {
    $req_fields = array('transfer-code', 'transfer-name', 'date', 'warehouse-name', 'transfer-to', 'observations');
    validate_fields($req_fields);

    if (empty($errors)) {
        // Obtener los datos del formulario
        $t_code  = remove_junk($db->escape($_POST['transfer-code']));
        $t_name  = remove_junk($db->escape($_POST['transfer-name']));
        $t_date  = remove_junk($db->escape($_POST['date']));
        $warehouse_name = remove_junk($db->escape($_POST['warehouse-name']));
        $transfer_to = remove_junk($db->escape($_POST['transfer-to']));
        $observations = remove_junk($db->escape($_POST['observations']));

        // Actualizar el traslado en la base de datos
        $query  = "UPDATE transfers SET ";
        $query .= "transfer_code='{$t_code}', transfer_name='{$t_name}', date='{$t_date}', warehouse_name='{$warehouse_name}', transfer_to='{$transfer_to}', observations='{$observations}' ";
        $query .= "WHERE id='{$transfer_id}'";
        if ($db->query($query)) {
            // Actualizar los productos y cantidades en transfer_items
            $db->query("DELETE FROM transfer_items WHERE transfer_id='{$transfer_id}'");

            foreach ($_POST['product'] as $index => $product_id) {
                $quantity = (int)$_POST['quantity'][$index];
                if ($product_id && $quantity) {
                    $query  = "INSERT INTO transfer_items (transfer_id, product_id, quantity) VALUES (";
                    $query .= " '{$transfer_id}', '{$product_id}', '{$quantity}'";
                    $query .= ")";
                    $db->query($query);
                }
            }

            $session->msg('s', "Traslado actualizado exitosamente.");
            redirect('transfers.php', false); // Asegúrate de redirigir aquí
        } else {
            $session->msg('d', 'Lo siento, la actualización falló.');
            redirect('edit_transfer.php?id=' . $transfer_id, false); // Redirigir en caso de error
        } } else{
            $session->msg("d", $errors);
            redirect('edit_receptions.php?id='.$reception['id'], false);
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
                    <a href="transfers.php" class="btn btn-primary">Volver a Traslados</a>
                </div>
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Editar Traslado</span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="edit_transfers.php?id=<?php echo (int)$transfer['id']; ?>">
                    <div class="form-group">
                        <label for="transfer-code">Código de Traslado</label>
                        <input type="text" class="form-control" name="transfer-code" value="<?php echo remove_junk($transfer['transfer_code']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="transfer-name">Nombre del Traslado</label>
                        <input type="text" class="form-control" name="transfer-name" value="<?php echo remove_junk($transfer['transfer_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Fecha</label>
                        <input type="date" class="form-control" name="date" value="<?php echo remove_junk($transfer['date']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="warehouse-name">Nombre del almacén</label>
                        <input type="text" class="form-control" name="warehouse-name" value="<?php echo remove_junk($transfer['warehouse_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="transfer-to">Traslado a</label>
                        <input type="text" class="form-control" name="transfer-to" value="<?php echo remove_junk($transfer['transfer_to']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="observations">Observaciones</label>
                        <textarea class="form-control" name="observations"><?php echo remove_junk($transfer['observations']); ?></textarea>
                    </div>
                    <div id="materials-container">
                        <?php if ($transfer_items): ?>
                            <?php foreach ($transfer_items as $index => $item): ?>
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
                    <button type="submit" name="edit_transfer" class="btn btn-primary pull-right">Actualizar Traslado</button>
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
        <br>
        <input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required>
        <br>
        <button type="button" class="btn btn-danger remove-material">Eliminar</button>
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
