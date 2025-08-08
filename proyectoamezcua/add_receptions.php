<?php
$page_title = 'Agregar recepción';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

$warehouses = find_all('warehouses');  // Obtener todos los almacenes
$supervisors = find_all('supervisors'); // Obtener todos los supervisores

// Obtener productos según el almacén seleccionado
$warehouse_id = isset($_POST['warehouse-id']) ? (int)$_POST['warehouse-id'] : 0;
$products = ($warehouse_id > 0) ? find_products_by_warehouse($warehouse_id) : [];

if (isset($_POST['add_reception'])) {
    $req_fields = array('date', 'warehouse-id', 'supervisor_id', 'observations');
    validate_fields($req_fields);

    if (empty($errors)) {
        // Generar el código de recepción automáticamente
        $r_code = generate_reception_code();
        
        // Obtener los datos del formulario
        $r_date = remove_junk($db->escape($_POST['date']));
        $warehouse_id = (int)$db->escape($_POST['warehouse-id']);
        $supervisor_id = (int)$db->escape($_POST['supervisor_id']);
        $observations = remove_junk($db->escape($_POST['observations']));

        // Insertar la recepción en la base de datos
        $query  = "INSERT INTO receptions (reception_code, date, warehouse_id, supervisor_id, observations) VALUES (";
        $query .= " '{$r_code}', '{$r_date}', '{$warehouse_id}', '{$supervisor_id}', '{$observations}')";
        
        if ($db->query($query)) {
            $reception_id = $db->insert_id();

            // Insertar los productos y cantidades en reception_items y actualizar inventario
            foreach ($_POST['product'] as $index => $product_id) {
                $quantity = (int)$_POST['quantity'][$index];
                if ($product_id && $quantity) {
                    // Insertar en reception_items
                    $query  = "INSERT INTO reception_items (reception_id, product_id, quantity) VALUES (";
                    $query .= " '{$reception_id}', '{$product_id}', '{$quantity}')";
                    $db->query($query);

                    // Actualizar la cantidad en el inventario del producto
                    $update_query  = "UPDATE products SET quantity = quantity + {$quantity} WHERE id = '{$product_id}'";
                    $db->query($update_query);
                }
            }

            $session->msg('s', "Recepción agregada exitosamente.");
            redirect('receptions.php', false); // Redirigir después de una inserción exitosa
        } else {
            $session->msg('d', 'Lo siento, registro falló.');
            redirect('add_receptions.php', false); // Redirigir en caso de error en la inserción
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_receptions.php', false);
    }
}

?>
<?php include_once('layouts/header.php'); ?>
<style>
    .table thead th {
        background-color: #51aded; /* Fondo azul */
        color: white; /* Texto blanco */
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #dee2e6; /* Borde gris */
    }
    .table td {
        text-align: center; /* Centrar texto */
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
                    <a href="receptions.php" class="btn btn-primary">Volver a las recepciones</a>
                </div>
            </div>
            <div class="panel-body">
                <form method="post" action="add_receptions.php">
                    <div class="form-group">
                        <label for="reception-code">Código de Recepción</label>
                        <input type="text" class="form-control" name="reception-code" value="<?php echo generate_reception_code(); ?>" readonly>
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
                    <div id="materials-container">
                        <div class="material-row form-group">
                            <label for="product">Producto</label>
                            <select class="form-control select-small" name="product[]" id="product">
                                <option value="">Selecciona un producto</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>">
                                        <?php echo $product['material_code'] . ' - ' . $product['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Cantidad</label>
                            <input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required min="0">
                        </div>
                    </div>
                    <button type="button" class="btn btn-success" id="add-material">Agregar Material</button>
                    <div class="form-group">
                        <br>
                        <label for="observations">Observaciones</label>
                        <textarea class="form-control" name="observations" placeholder="Observaciones"></textarea>
                    </div>
                    <button type="submit" name="add_reception" class="btn btn-primary pull-right">Agregar recepción</button>
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
                                            '<option value="<?php echo $product["id"]; ?>"><?php echo $product['material_code'] . ' - ' . $product['name']; ?></option>' +
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

