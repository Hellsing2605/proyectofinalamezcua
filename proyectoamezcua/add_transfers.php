<?php
$page_title = 'Agregar traslado';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

$warehouses = find_all('warehouses'); // Obtener los almacenes
$technicians = find_all('technicians');
$supervisors = find_all('supervisors'); // Obtener los supervisores

// Obtener el ID del almacén seleccionado
$warehouse_id = isset($_POST['warehouse-id']) ? (int)$_POST['warehouse-id'] : 0;

// Obtener los productos
if ($warehouse_id > 0) {
    // Si se seleccionó un almacén, obtener los productos para bajantes de ese almacén
    $products = find_products_by_warehouse_and_used_by($warehouse_id, 'Bajantes');
} else {
    // Si no se seleccionó un almacén, obtener los productos para bajantes
    $products = find_products_by_used_by('Bajantes');
}

if (isset($_POST['add_transfer'])) {
    $req_fields = array('supervisor_id', 'date', 'warehouse-id', 'technician_id', 'observations');
    validate_fields($req_fields);

    if (empty($errors)) {
        // Generar el código de transferencia automáticamente
        $t_code = generate_transfer_code();

        // Obtener los datos del formulario
        $supervisor_id = (int)$_POST['supervisor_id'];
        $t_date = remove_junk($db->escape($_POST['date']));
        $warehouse_id = (int)$_POST['warehouse-id'];
        $technician_id = (int)$_POST['technician_id'];
        $observations = remove_junk($db->escape($_POST['observations']));

        // Insertar el traslado en la base de datos
        $query  = "INSERT INTO transfers (transfer_code, supervisor_id, date, warehouse_id, technician_id, observations) VALUES (";
        $query .= " '{$t_code}', '{$supervisor_id}', '{$t_date}', '{$warehouse_id}', '{$technician_id}', '{$observations}')";
        
        if ($db->query($query)) {
            $transfer_id = $db->insert_id();
        
            // Insertar los productos y cantidades en transfer_items y actualizar cantidades en products
            foreach ($_POST['product'] as $index => $product_id) {
                $quantity = (int)$_POST['quantity'][$index];
                if ($product_id && $quantity) {
                    $product = find_by_id('products', $product_id);
                    $available_quantity = (int)$product['quantity'];

                    // Validar si la cantidad a transferir no supera la cantidad disponible
                    if ($quantity <= $available_quantity) {
                        $query  = "INSERT INTO transfer_items (transfer_id, product_id, quantity) VALUES (";
                        $query .= " '{$transfer_id}', '{$product_id}', '{$quantity}')";
                        $db->query($query);

                        // Actualizar cantidad y traslados en products
                        $query  = "UPDATE products SET traslados = traslados + '{$quantity}', quantity = quantity - '{$quantity}' WHERE id = '{$product_id}'";
                        $db->query($query);
                    } else {
                        $session->msg('d', "La cantidad a transferir de '{$product['name']}' supera la cantidad disponible.");
                        redirect('add_transfers.php', false);
                    }
                }
            }

            $session->msg('s', "Traspaso agregado exitosamente.");
            redirect('transfers.php', false);
        } else {
            $session->msg('d', 'Lo siento, el registro falló.');
        }
    }
}
?>

<?php include_once('layouts/header.php'); ?>
<style>
    .select-small {
        font-size: 13px;
    }

    .select-small option {
        font-size: 13px;
    }
</style>

<div class="row">
    <div class="col-md-2">
        <?php echo display_msg($msg); ?>
    </div>
    <div class="row justify-content-center">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <div class="pull-right">
                    <a href="transfers.php" class="btn btn-primary">Volver a los traspasos</a>
                </div>
            </div>
            <div class="panel-body">
                <form method="post" action="add_transfers.php">
                    <div class="form-group">
                        <label for="transfer-code">Código de Traspaso</label>
                        <input type="text" class="form-control" name="transfer-code" value="<?php echo generate_transfer_code(); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="supervisor_id">Seleccione Supervisor</label>
                        <select class="form-control" name="supervisor_id" required>
                            <option value="">Selecciona un supervisor</option>
                            <?php foreach ($supervisors as $supervisor): ?>
                                <option value="<?php echo $supervisor['id']; ?>" <?php if(isset($_POST['supervisor_id']) && $_POST['supervisor_id'] == $supervisor['id']) echo 'selected'; ?>>
                                    <?php echo $supervisor['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Fecha</label>
                        <input type="date" class="form-control" name="date" placeholder="Fecha" required value="<?php echo isset($_POST['date']) ? $_POST['date'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="warehouse-id">Almacén</label>
                        <select class="form-control" name="warehouse-id" id="warehouse-id" required onchange="this.form.submit()">
                            <option value="">Selecciona un almacén</option>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <option value="<?php echo (int)$warehouse['id']; ?>" <?php if ($warehouse_id == (int)$warehouse['id']) echo 'selected'; ?>>
                                    <?php echo remove_junk($warehouse['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="technician_id">Se realiza el traspaso a</label>
                        <select class="form-control" name="technician_id" required>
                            <option value="">Selecciona un técnico</option>
                            <?php foreach ($technicians as $technician): ?>
                                <option value="<?php echo $technician['id']; ?>">
                                    <?php echo $technician['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="materials-container">
                        <div class="material-row form-group">
                            <label for="product">Producto</label>
                            <select class="form-control select-small" name="product[]" id="product">
                                <option value="">Selecciona un producto</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>">
                                        <?php echo $product['material_code'] . ' - ' . $product['name'].' - Cantidad disponible: ' . $product['quantity']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Cantidad</label>
                            <input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success" id="add-material">Agregar Material</button>
                    <div class="form-group">
                        <br>
                        <label for="observations">Observaciones</label>
                        <textarea class="form-control" name="observations" placeholder="Observaciones"></textarea>
                    </div>
                    <button type="submit" name="add_transfer" class="btn btn-primary pull-right">Agregar traspaso</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>



<script>
    // Función para agregar una nueva fila de materiales
    $(document).ready(function(){
        $("#add-material").click(function(){
            var newMaterialRow = '<div class="material-row form-group">' +
                                    '<label for="product">Producto</label>' +
                                    '<select class="form-control" name="product[]" id="product">' +
                                        '<option value="">Selecciona un producto</option>' +
                                        '<?php foreach ($products as $product): ?>' +
                                            '<option value="<?php echo $product["id"]; ?>"><?php echo $product['material_code'] . ' - ' . $product['name'].' - Cantidad disponible: ' . $product['quantity']; ?></option>' +
                                        '<?php endforeach; ?>' +
                                    '</select>' +
                                    '<br><label for="quantity">Cantidad</label>' +
                                    '<br><input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required>' +
                                    '<br><button type="button" class="btn btn-danger remove-material">Eliminar</button>' +
                                '</div>';
            $("#materials-container").append(newMaterialRow);
        });

        // Función para eliminar una fila de materiales
        $(document).on('click', '.remove-material', function(){
            $(this).closest('.material-row').remove();
        });
    });
</script>



