<?php
$page_title = 'Agregar Request';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

$products = join_product_table();

if (isset($_POST['add_request'])) {
    $req_fields = array('request-name', 'date', 'warehouse-name', 'request-to', 'observations');
    validate_fields($req_fields);

    if (empty($errors)) {
        // Obtener los datos del formulario
        $r_code = generate_request_code();
        $r_name  = remove_junk($db->escape($_POST['request-name']));
        $r_date  = remove_junk($db->escape($_POST['date']));
        $warehouse_name = remove_junk($db->escape($_POST['warehouse-name']));
        $request_to = remove_junk($db->escape($_POST['request-to']));
        $observations = remove_junk($db->escape($_POST['observations']));

        // Insertar la petición en la base de datos
        $query  = "INSERT INTO requests (request_code, request_name, date, warehouse_name, request_to, observations) VALUES (";
        $query .= " '{$r_code}', '{$r_name}', '{$r_date}', '{$warehouse_name}', '{$request_to}', '{$observations}'";
        $query .= ")";
        if ($db->query($query)) {
            $request_id = $db->insert_id();

            // Insertar los productos y cantidades en request_items y actualizar cantidades en products
            foreach ($_POST['product'] as $index => $product_id) {
                $quantity = (int)$_POST['quantity'][$index];
                if ($product_id && $quantity) {
                    // Insertar en request_items
                    $query  = "INSERT INTO request_items (request_id, product_id, quantity) VALUES (";
                    $query .= " '{$request_id}', '{$product_id}', '{$quantity}'";
                    $query .= ")";
                    $db->query($query);

                    // Actualizar cantidad en products
                    $query  = "UPDATE products SET quantity = quantity - '{$quantity}' WHERE id = '{$product_id}'";
                    $db->query($query);
                }
            }

            $session->msg('s', "Petición agregada exitosamente.");
            redirect('requests.php', false); // Redirigir después de una inserción exitosa
        } else {
            $session->msg('d', 'Lo siento, registro falló.');
            redirect('add_request.php', false); // Redirigir en caso de error en la inserción
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
                    <a href="requests.php" class="btn btn-primary">Volver a las peticiones</a>
                </div>
            </div>
            <div class="panel-body">
                <form method="post" action="add_request.php">
                    <div class="form-group">
                        <label for="transfer-code">Código de Petición</label>
                        <input type="text" class="form-control" name="transfer-code" value="<?php echo generate_request_code(); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="request-name">Nombre de la petición</label>
                        <input type="text" class="form-control" name="request-name" placeholder="Nombre de la petición" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Fecha</label>
                        <input type="date" class="form-control" name="date" placeholder="Fecha" required>
                    </div>
                    <div class="form-group">
                        <label for="warehouse-name">Nombre del almacén</label>
                        <input type="text" class="form-control" name="warehouse-name" placeholder="Nombre del almacén" required>
                    </div>
                    <div class="form-group">
                        <label for="request-to">Nombre de quien realiza la petición</label>
                        <input type="text" class="form-control" name="request-to" placeholder="Nombre" required>
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
                            <input type="number" class="form-control" name="quantity[]" placeholder="Cantidad" required>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success" id="add-material">Agregar Material</button>
                    <div class="form-group">
                        <br>
                        <label for="observations">Observaciones</label>
                        <textarea class="form-control" name="observations" placeholder="Observaciones"></textarea>
                    </div>
                    <button type="submit" name="add_request" class="btn btn-primary pull-right">Agregar Petición</button>
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
                                            '<option value="<?php echo $product["id"]; ?>"><?php echo $product["name"]; ?></option>' +
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



