<?php
$page_title = 'Agregar Devolución';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);
$products = join_product_table();
$technicians = find_all('technicians');
$supervisors = find_all('supervisors');
$warehouses = find_all('warehouses');
$warehouse_id = isset($_POST['warehouse-id']) ? (int)$_POST['warehouse-id'] : 0;
if (isset($_POST['add_return'])) {
    $req_fields = array('supervisor_id', 'date', 'warehouse-id', 'technician_id', 'observations');
    validate_fields($req_fields);

    if (empty($errors)) {
        $r_code = generate_return_code();  // Generar el código automáticamente
        $supervisor_id = (int)$_POST['supervisor_id'];
        $r_date  = remove_junk($db->escape($_POST['date']));
        $warehouse_id = (int)$_POST['warehouse-id'];
        $technician_id = (int)$_POST['technician_id'];
        $observations = remove_junk($db->escape($_POST['observations']));

        // Insertar los detalles de la devolución en la tabla 'returns'
        $query  = "INSERT INTO returns (return_code, supervisor_id, date, warehouse_id, technician_id, observations) VALUES (";
        $query .= " '{$r_code}', '{$supervisor_id}', '{$r_date}', '{$warehouse_id}', '{$technician_id}', '{$observations}')";
        
        if ($db->query($query)) {
            $return_id = $db->insert_id();  // Obtener el ID de la devolución recién creada

            // Insertar los productos devueltos en 'return_items'
            foreach ($_POST['product'] as $index => $product_id) {
                $quantity = (int)$_POST['quantity'][$index];
                if ($product_id && $quantity) {
                    $current_transfers = find_technician_inventory_by_warehouse($technician_id, $warehouse_id, $product_id);
                    $current_transfers = isset($current_transfers['quantity']) ? (int)$current_transfers['quantity'] : 0;
                    
                    $product_devoluciones = find_product_devoluciones($technician_id, $warehouse_id, $product_id);
                    $product_devoluciones = is_numeric($product_devoluciones) ? $product_devoluciones : 0;

                    // Calcular las nuevas cantidades
                    $new_transfers = $current_transfers - $quantity;
                    $new_returns = $product_devoluciones + $quantity;
                    // Validar que las nuevas cantidades sean correctas
                    if ($new_transfers < 0) {
                        $new_transfers = 0;
                    }
                   
                    // Insertar el producto y la cantidad en 'return_items'
                    $query  = "INSERT INTO return_items (return_id, product_id, quantity) VALUES (";
                    $query .= " '{$return_id}', '{$product_id}', '{$quantity}')";
                    $db->query($query);

                    // Actualizar la tabla 'products' con los nuevos valores
                    $query  = "UPDATE products SET ";
                    $query .= "quantity = quantity + '{$quantity}', "; // Actualizar inventario del almacén
                    $query .= "devoluciones = '{$new_returns}', ";
                    $query .= "traslados = '{$new_transfers}' ";
                    $query .= "WHERE id = '{$product_id}'";
                    $db->query($query);
                }
            }
            $session->msg('s', "Devolución agregada exitosamente.");
            redirect('returns.php', false);
        } else {
            $session->msg('d', 'Lo siento, el registro de devolución falló.');
            redirect('add_returns.php', false);
        }
    }
}
function calculate_available_inventory($technician_id, $warehouse_id, $product_id) {
    // Obtener la cantidad total de transferencias hacia el técnico
    $transfers = find_technician_inventory_by_warehouse($technician_id, $warehouse_id, $product_id);
    $transfers = isset($transfers['quantity']) ? (int)$transfers['quantity'] : 0;
    
    // Obtener la cantidad total de devoluciones realizadas por el técnico
    $returns = find_product_devoluciones($technician_id, $warehouse_id, $product_id);
    $returns = is_numeric($returns) ? (int)$returns : 0;

    // Obtener la cantidad total de liquidaciones
    $liquidaciones = find_product_liquidaciones($technician_id, $warehouse_id, $product_id);
    $liquidaciones = is_numeric($liquidaciones) ? (int)$liquidaciones : 0;

    // Calcular inventario disponible: (traspasos - devoluciones - liquidaciones)
    $available_inventory = $transfers - $returns - $liquidaciones;

    // Asegurarse de que no haya un valor negativo
    return $available_inventory > 0 ? $available_inventory : 0;
}
$technician_inventory = [];
if (isset($_POST['technician_id']) && (int)$_POST['technician_id'] > 0 && isset($_POST['warehouse-id']) && (int)$_POST['warehouse-id'] > 0) {
    $technician_id = (int)$_POST['technician_id'];
    $warehouse_id = (int)$_POST['warehouse-id'];
    
    // Obtener el inventario actual del técnico
    $technician_inventory = find_technician_inventory_by_warehouse($technician_id, $warehouse_id);

    // Si el técnico tiene productos en inventario, recalcular las cantidades disponibles
    if (!empty($technician_inventory)) {
        foreach ($technician_inventory as &$item) {
            $product_id = $item['product_id'];
            
            // Calcular la cantidad disponible utilizando la función
            $item['quantity_available'] = calculate_available_inventory($technician_id, $warehouse_id, $product_id);
        }
    } else {
        // Mensaje si no hay inventario para el técnico en ese almacén
        $session->msg('d', 'El técnico seleccionado no ha recibido materiales de este almacén.');
        redirect('add_returns.php', false);
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
                    <a href="returns.php" class="btn btn-primary">Volver a las Devoluciones</a>
                </div>
            </div>
            <div class="panel-body">
                <form method="post" action="add_returns.php">
                    <div class="form-group">
                        <label for="reception-code">Código de Devolución</label>
                        <input type="text" class="form-control" name="reception-code" value="<?php echo generate_return_code(); ?>" readonly>
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
                        <label for="technician_id">Se realiza la devolución por parte de</label>
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
                            <?php 
                            $available_inventory = calculate_available_inventory($technician_id, $warehouse_id, $item['product_id']);
                            ?>
                            <option value="<?php echo $item['product_id']; ?>" <?php if ($item['product_id'] == $product_id) echo 'selected'; ?>>
                                <?php echo "{$item['product_name']} - Disponibles: {$available_inventory}"; ?>
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
                        <?php 
                        $available_inventory = calculate_available_inventory($technician_id, $warehouse_id, $item['product_id']);
                        ?>
                        <option value="<?php echo $item['product_id']; ?>">
                            <?php echo "{$item['product_name']} - Disponibles: {$available_inventory}"; ?>
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
                    <button type="submit" name="add_return" class="btn btn-primary pull-right">Agregar devolución</button>
                </form>
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
                                            '<option value="<?php echo $product["product_id"]; ?>"><?php echo $item['product_name']?></option>' +
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


