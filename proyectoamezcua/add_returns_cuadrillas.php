<?php
$page_title = 'Agregar Devolución';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

$products = join_product_table();
$technicians = find_all('technicians');
$cuadrillas = find_all('cuadrillas');
$supervisors = find_all('supervisors');
$obras = find_all('obras');
$warehouses = find_all('warehouses');
$warehouse_id = isset($_POST['warehouse-id']) ? (int)$_POST['warehouse-id'] : 0;
if (isset($_POST['add_return'])) {
    $req_fields = array('supervisor_id', 'date', 'warehouse-id', 'observations');
    validate_fields($req_fields);

    if (empty($errors)) {
        $r_code = generate_return_code();
        $supervisor_id = (int)$_POST['supervisor_id'];
        $r_date  = remove_junk($db->escape($_POST['date']));
        $warehouse_id = (int)$_POST['warehouse-id'];
        $observations = remove_junk($db->escape($_POST['observations']));
        $technician_id = !empty($_POST['technician_id']) ? (int)$_POST['technician_id'] : null;
        $cuadrilla_id = !empty($_POST['cuadrilla_id']) ? (int)$_POST['cuadrilla_id'] : null;
        $obra_id = !empty($_POST['obra_id']) ? (int)$_POST['obra_id'] : null;

         // Verificar si el producto está en el inventario de la cuadrilla y de la obra
         $cuadrilla_inventory = find_cuadrilla_inventory($cuadrilla_id);
         $obra_inventory = find_obras_inventory($obra_id);
 
         $all_products_valid = true;
 
         foreach ($_POST['product'] as $index => $product_id) {
             $quantity = (int)$_POST['quantity'][$index];
             $product_in_cuadrilla = array_filter($cuadrilla_inventory, function($item) use ($product_id) {
                 return $item['product_id'] == $product_id;
             });
 
             $product_in_obra = array_filter($obra_inventory, function($item) use ($product_id) {
                 return $item['product_id'] == $product_id;
             });
 
             if (empty($product_in_cuadrilla) || empty($product_in_obra)) {
                 $all_products_valid = false;
                 break;
             }
         }
 
         if (!$all_products_valid) {
             $session->msg('d', 'Uno o más productos no están o no hay existencia en el inventario de la cuadrilla y/o de la obra.');
             redirect('add_returns_cuadrillas.php', false);
         }
 

        $query  = "INSERT INTO returns (return_code, supervisor_id, date, warehouse_id, technician_id, cuadrilla_id, obra_id, observations) VALUES (";
        $query .= " '{$r_code}', '{$supervisor_id}', '{$r_date}', '{$warehouse_id}', ";
        $query .= $technician_id ? "'{$technician_id}', " : "NULL, ";
        $query .= $cuadrilla_id ? "'{$cuadrilla_id}', " : "NULL, ";
        $query .= $obra_id ? "'{$obra_id}', " : "NULL, ";
        $query .= "'{$observations}')";

        if ($db->query($query)) {
            $return_id = $db->insert_id();  // Obtener el ID de la devolución recién creada

            // Insertar los productos devueltos en 'return_items'
            foreach ($_POST['product'] as $index => $product_id) {
                $quantity = (int)$_POST['quantity'][$index];
                if ($product_id && $quantity) {
                    $current_transfers = find_cuadrilla_inventory_by_warehouse($cuadrilla_id, $warehouse_id, $product_id);
                    $current_transfers = isset($current_transfers['quantity']) ? (int)$current_transfers['quantity'] : 0;
                    
                    $product_devoluciones = find_product_devoluciones_c($cuadrilla_id, $warehouse_id, $product_id);
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
            $session->msg('d', 'Lo siento, registro falló.');
            redirect('add_returns_cuadrillas.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_returns_cuadrillas.php', false);
    }
}

$cuadrilla_inventory = [];
if (isset($_POST['cuadrilla_id']) && (int)$_POST['cuadrilla_id'] > 0 && isset($_POST['warehouse-id']) && (int)$_POST['warehouse-id'] > 0) {
    $cuadrilla_id = (int)$_POST['cuadrilla_id'];
    $warehouse_id = (int)$_POST['warehouse-id'];
    $cuadrilla_inventory = find_cuadrilla_inventory_by_warehouse($cuadrilla_id, $warehouse_id);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuadrilla_id = isset($_POST['cuadrilla_id']) ? (int)$_POST['cuadrilla_id'] : 0;
    $warehouse_id = isset($_POST['warehouse-id']) ? (int)$_POST['warehouse-id'] : 0;

    if ($cuadrilla_id > 0 && $warehouse_id > 0) {
        // Verificar si la cuadrilla ha recibido materiales de este almacén
        $cuadrilla_inventory = find_cuadrilla_inventory_by_warehouse($cuadrilla_id, $warehouse_id);

        if (empty($cuadrilla_inventory)) {
            $session->msg('d', 'La cuadrilla seleccionada no ha recibido materiales de este almacén.');
            redirect('add_returns_cuadrillas.php', false);
        }
    }
}

$obra_operacion = $obra_oei = $obra_oe = $obra_central = $obra_ruta = $obra_pep = '';

if (isset($_POST['obra_id'])) {
    $obra_id = $_POST['obra_id'];
    $selected_obra = find_by_id('obras', $obra_id);
    if ($selected_obra) {
        $obra_operacion = $selected_obra['operacion'];
        $obra_oei = $selected_obra['oei'];
        $obra_oe = $selected_obra['oe'];
        $obra_central = $selected_obra['central'];
        $obra_ruta = $selected_obra['ruta'];
        $obra_pep = $selected_obra['pep'];
    }
}
$obras = get_open_works(); // Usamos la nueva función para obtener solo obras abiertas

function calculate_obra_status($obra_id) {
    $inventory = find_obras_inventory($obra_id);
    $returns = find_obras_returns($obra_id);
    $liquidations = find_obras_liquidations($obra_id);

    $inventory_with_returns_and_liquidations = [];
    foreach ($inventory as $item) {
        $inventory_with_returns_and_liquidations[$item['product_id']] = [
            'product_name' => $item['product_name'],
            'quantity' => (int)$item['quantity'],
            'returned_quantity' => 0,
            'liquidated_quantity' => 0
        ];
    }
    foreach ($returns as $item) {
        if (isset($inventory_with_returns_and_liquidations[$item['product_id']])) {
            $inventory_with_returns_and_liquidations[$item['product_id']]['returned_quantity'] = (int)$item['quantity'];
            $inventory_with_returns_and_liquidations[$item['product_id']]['quantity'] -= (int)$item['quantity'];
        } else {
            $inventory_with_returns_and_liquidations[$item['product_id']] = [
                'product_name' => $item['product_name'],
                'quantity' => -1 * (int)$item['quantity'],
                'returned_quantity' => (int)$item['quantity'],
                'liquidated_quantity' => 0
            ];
        }
    }
    foreach ($liquidations as $item) {
        if (isset($inventory_with_returns_and_liquidations[$item['product_id']])) {
            $inventory_with_returns_and_liquidations[$item['product_id']]['liquidated_quantity'] = (int)$item['quantity'];
            $inventory_with_returns_and_liquidations[$item['product_id']]['quantity'] -= (int)$item['quantity'];
        } else {
            $inventory_with_returns_and_liquidations[$item['product_id']] = [
                'product_name' => $item['product_name'],
                'quantity' => -1 * (int)$item['quantity'],
                'returned_quantity' => 0,
                'liquidated_quantity' => (int)$item['quantity']
            ];
        }
    }

    $is_obra_closed = true;
    foreach ($inventory_with_returns_and_liquidations as $item) {
        if ($item['quantity'] != 0) {
            $is_obra_closed = false;
            break;
        }
    }

    return $is_obra_closed ? '0' : '1';
}
?>

<?php include_once('layouts/header.php'); ?>
<div class="row">
    <div class="col-md-2">
        <?php echo display_msg($msg); ?>
    </div>
    <div class="row justify-content-center">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <div class="pull-right">
                    <a href="returns.php" class="btn btn-primary">Volver a las devoluciones</a>
                </div>
            </div>
            <div class="panel-body">
                <form method="post" action="add_returns_cuadrillas.php">
                    <div class="form-group">
                        <label for="return-code">Código de Devolución</label>
                        <input type="text" class="form-control" name="return-code" value="<?php echo generate_return_code(); ?>" readonly>
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
                        <label for="cuadrilla_id">Se realiza la devolución por parte de</label>
                        <select class="form-control" name="cuadrilla_id" required onchange="this.form.submit()">
                            <option value="">Selecciona una cuadrilla</option>
                            <?php foreach ($cuadrillas as $cuadrilla): ?>
                                <option value="<?php echo (int)$cuadrilla['id']; ?>" <?php if (isset($cuadrilla_id) && $cuadrilla_id == $cuadrilla['id']) echo 'selected'; ?>>
                                    <?php echo remove_junk($cuadrilla['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="obra_id">Selecciona una obra</label>
                        <select class="form-control" name="obra_id" id="obra_id" onchange="updateObraDetails()">
                            <option value="">Selecciona una obra</option>
                            <?php foreach ($obras as $obra): ?>
                                <option value="<?php echo $obra['id']; ?>">
                                <?php echo $obra['operacion'] . ' - ' . $obra['oei'] . ' - ' . $obra['oe'] . ' - ' . $obra['central'] . ' - ' . $obra['ruta'] . ' - ' . $obra['pep']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($cuadrilla_inventory)): ?>
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
                                            <?php foreach ($cuadrilla_inventory as $item): ?>
                                                <option value="<?php echo $item['product_id']; ?>" <?php if ($item['product_id'] == $product_id) echo 'selected'; ?>>
                                                <?php echo $item['product_name']. '  ' .' - Cantidad disponible: ' . $item['quantity'];?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <br>
                                        <label for="quantity">Cantidad</label>
                                        <br>
                                        <input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required value="<?php echo $quantity; ?>">
                                        <br>
                                        <button type="button" class="btn btn-danger remove-material">Eliminar</button>
                                    </div>
                                    <?php
                                }
                            } else {
                                $product_id = ''; // Inicializa la variable con un valor predeterminado
                                ?>
                                <div class="material-row form-group">
                                    <label for="product">Producto</label>
                                    <select class="form-control select-small" name="product[]" id="product">
                                        <option value="">Selecciona un producto</option>
                                        <?php foreach ($cuadrilla_inventory as $item): ?>
                                            <option value="<?php echo $item['product_id']; ?>" <?php if ($item['product_id'] == $product_id) echo 'selected'; ?>>
                                            <?php echo $item['product_name']. '  ' .' - Cantidad disponible: ' . $item['quantity'];?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <br>
                                    <label for="quantity">Cantidad</label>
                                    <br>
                                    <input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required>
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
                                        '<?php foreach ($cuadrilla_inventory as $product): ?>' +
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
