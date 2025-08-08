<?php
$page_title = 'Agregar Liquidación';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

$products = join_product_table();
$technicians = find_all('technicians');
$supervisors = find_all('supervisors');
$warehouses = find_all('warehouses');
$warehouse_id = isset($_POST['warehouse-id']) ? (int)$_POST['warehouse-id'] : 0;

if (isset($_POST['add_liquidation'])) {
    $req_fields = array('supervisor_id', 'date', 'warehouse-id', 'technician_id', 'observations');
    validate_fields($req_fields);

    if (empty($errors)) {
        $l_code = generate_liquidation_code(); // Generar el código automáticamente
        // Obtener los datos del formulario
        $supervisor_id = (int)$_POST['supervisor_id'];
        $l_date  = remove_junk($db->escape($_POST['date']));
        $warehouse_id = (int)$_POST['warehouse-id'];
        $technician_id = (int)$_POST['technician_id'];
        $observations = remove_junk($db->escape($_POST['observations']));

        // Insertar la liquidación en la base de datos
        $query  = "INSERT INTO liquidations (liquidation_code, supervisor_id, date, warehouse_id, technician_id, observations) VALUES (";
        $query .= " '{$l_code}', '{$supervisor_id}', '{$l_date}', '{$warehouse_id}', '{$technician_id}', '{$observations}'";
        $query .= ")";
        if ($db->query($query)) {
            $liquidation_id = $db->insert_id();
        
            // Insertar los productos y cantidades en liquidation_items y actualizar cantidades en products
            foreach ($_POST['product'] as $index => $product_id) {
                $quantity = (int)$_POST['quantity'][$index];
                if ($product_id && $quantity) {
                    // Obtener las transferencias actuales del técnico en el almacén
                    $current_transfers = find_technician_inventory_by_warehouse($technician_id, $warehouse_id, $product_id);
                    $current_transfers = isset($current_transfers['quantity']) ? (int)$current_transfers['quantity'] : 0;
            
                    // Obtener las liquidaciones actuales del producto
                    $product_liquidaciones = find_product_liquidaciones($technician_id, $warehouse_id, $product_id);
                    $product_liquidaciones = is_numeric($product_liquidaciones) ? $product_liquidaciones : 0;
            
                    // Insertar en liquidation_items
                    $query  = "INSERT INTO liquidation_items (liquidation_id, product_id, quantity) VALUES (";
                    $query .= " '{$liquidation_id}', '{$product_id}', '{$quantity}')";
                    $db->query($query);
            
                    // Calcular las nuevas cantidades de traspasos y liquidaciones
                    $new_transfers = $current_transfers - $quantity;
                    $new_liquidaciones = $product_liquidaciones + $quantity;
            
                    // Validar que no haya cantidades negativas en traspasos
                    if ($new_transfers < 0) {
                        $new_transfers = 0;
                    }
            
                    // Actualizar únicamente las cantidades de traspasos y liquidaciones en la tabla 'products'
                    $query  = "UPDATE products SET ";
                    $query .= "traslados = '{$new_transfers}', "; // Restar la cantidad de traspasos
                    $query .= "liquidaciones = '{$new_liquidaciones}' "; // Sumar la cantidad de liquidaciones
                    $query .= "WHERE id = '{$product_id}'";
                    $db->query($query);
                }
            }
        
            $session->msg('s', "Liquidación agregada exitosamente.");
            redirect('liquidations.php', false);
        } else {
            $session->msg('d', 'Lo siento, registro falló.');
            redirect('add_liquidations.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_liquidations.php', false);
    }
}

// Obtener inventario del técnico si el técnico está seleccionado y filtrar por almacén
$technician_inventory = [];
if (isset($_POST['technician_id']) && (int)$_POST['technician_id'] > 0 && isset($_POST['warehouse-id']) && (int)$_POST['warehouse-id'] > 0) {
    $technician_id = (int)$_POST['technician_id'];
    $warehouse_id = (int)$_POST['warehouse-id'];
    $technician_inventory = find_technician_inventory_by_warehouse($technician_id, $warehouse_id);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $technician_id = isset($_POST['technician_id']) ? (int)$_POST['technician_id'] : 0;
    $warehouse_id = isset($_POST['warehouse-id']) ? (int)$_POST['warehouse-id'] : 0;

    if ($technician_id > 0 && $warehouse_id > 0) {
        // Verificar si el técnico ha recibido materiales de este almacén
        $technician_inventory = find_technician_inventory_by_warehouse($technician_id, $warehouse_id);

        if (empty($technician_inventory)) {
            $session->msg('d', 'El técnico seleccionado no ha recibido materiales de este almacén.');
            redirect('add_liquidations.php', false);
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
                    <a href="liquidations.php" class="btn btn-primary">Volver a las Liquidaciones</a>
                </div>
            </div>
            <div class="panel-body">
                <form method="post" action="add_liquidations.php">
                <div class="form-group">
                        <label for="reception-code">Código de Liquidación</label>
                        <input type="text" class="form-control" name="reception-code" value="<?php echo generate_liquidation_code(); ?>" readonly>
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
                        <label for="technician_id">Se realiza la liquidación por parte de</label>
                        <select class="form-control" name="technician_id" required onchange="this.form.submit()">
                            <option value="">Selecciona un técnico</option>
                            <?php foreach ($technicians as $technician): ?>
                                <option value="<?php echo (int)$technician['id']; ?>" <?php if (isset($technician_id) && $technician_id == $technician['id']) echo 'selected'; ?>>
                                    <?php echo remove_junk($technician['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($technician_inventory)): ?>
                        <div id="materials-container">
                            <?php
                            if (isset($_POST['product'])) {
                                foreach ($_POST['product'] as $index => $product_id) {
                                    $quantity = $_POST['quantity'][$index];
                                    ?>
                                    <div class="material-row form-group">
                                        <label for="product">Producto</label>
                                        <select class="form-control select-small" name="product[]" id="product">
                                            <option value="">Selecciona un producto</option>
                                            <?php foreach ($technician_inventory as $item): ?>
                                                <option value="<?php echo $item['product_id']; ?>" <?php if ($item['product_id'] == $product_id) echo 'selected'; ?>>
                                               <?php echo $item['product_name']. '  ' .' - Cantidad disponible: ' . $item['quantity'];?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <br>
                                        <label for="quantity">Cantidad</label>
                                        <br>
                                        <input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required min="0" value="<?php echo $quantity; ?>">
                                        <br>
                                        <button type="button" class="btn btn-danger remove-material">Eliminar</button>
                                    </div>
                                    <?php
                                }
                            } else {
                                ?>
                                <div class="material-row form-group">
                                    <label for="product">Producto</label>
                                    <select class="form-control select-small" name="product[]" id="product">
                                        <option value="">Selecciona un producto</option>
                                        <?php foreach ($technician_inventory as $item): ?>
                                            <option value="<?php echo $item['product_id']; ?>">
                                            <?php echo $item['product_name']. '  ' .' - Cantidad disponible: ' . $item['quantity'];?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <br>
                                    <label for="quantity">Cantidad</label>
                                    <br>
                                    <input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required min="0">
                                    <br>
                                    <button type="button" class="btn btn-danger remove-material">Eliminar</button>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <button type="button" class="btn btn-success" id="add-material">Agregar Material</button>
                    </div>
                    <div class="form-group">
                        <br>
                        <label for="observations">Observaciones</label>
                        <textarea class="form-control" name="observations" placeholder="Observaciones"><?php echo isset($_POST['observations']) ? $_POST['observations'] : ''; ?></textarea>
                    </div>
                    <button type="submit" name="add_liquidation" class="btn btn-primary pull-right">Agregar liquidación</button>
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
                                        '<?php foreach ($technician_inventory as $product): ?>' +
                                            '<option value="<?php echo $product["product_id"]; ?>"><?php echo $item['product_name']. '  ' .' - Cantidad disponible: ' . $item['quantity'];?></option>' +
                                        '<?php endforeach; ?>' +
                                    '</select>' +
                                    '<br><label for="quantity">Cantidad</label>' +
                                    '<br><input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required min="0">' +
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

