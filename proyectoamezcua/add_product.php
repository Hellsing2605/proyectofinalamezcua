<?php
$page_title = 'Agregar producto';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);

$all_categories = find_all('categories');
$all_photo = find_all('media');
$all_suppliers = find_all('suppliers'); // Obtener todos los proveedores

if (isset($_POST['add_product'])) {
    $req_fields = array('product-title', 'product-categorie', 'product-quantity', 'material-code', 'product-model', 'product-supplier', 'used-by', 'warehouse_id');
    validate_fields($req_fields);

    if (empty($errors)) {
        $p_name = remove_junk($db->escape($_POST['product-title']));
        $p_cat = remove_junk($db->escape($_POST['product-categorie']));
        $p_qty = remove_junk($db->escape($_POST['product-quantity']));
        $p_code = remove_junk($db->escape($_POST['material-code']));
        $p_model = remove_junk($db->escape($_POST['product-model']));
        $supplier_id = remove_junk($db->escape($_POST['product-supplier']));
        $used_by = remove_junk($db->escape($_POST['used-by']));
        $warehouse_id = remove_junk($db->escape($_POST['warehouse_id']));
        $media_id = isset($_POST['product-photo']) && $_POST['product-photo'] !== "" ? remove_junk($db->escape($_POST['product-photo'])) : '0';

        // Verificar si el producto ya existe en el mismo almacén
        $existing_product_query = "SELECT * FROM products WHERE name = '{$p_name}' AND warehouse_id = '{$warehouse_id}' LIMIT 1";
        $result = $db->query($existing_product_query);

        if ($result && $db->num_rows($result) > 0) {
            // Si el producto ya existe en el mismo almacén
            $session->msg('d', "Error: El producto '{$p_name}' ya está registrado en el almacén seleccionado.");
            redirect('add_product.php', false);
        } else {
            // Si el producto no existe en el mismo almacén, proceder a insertarlo
            $query  = "INSERT INTO products (";
            $query .= "name, quantity, categorie_id, media_id, material_code, modelo, supplier_id, used_by, warehouse_id";
            $query .= ") VALUES (";
            $query .= "'{$p_name}', '{$p_qty}', '{$p_cat}', '{$media_id}', '{$p_code}', '{$p_model}', '{$supplier_id}', '{$used_by}', '{$warehouse_id}'";
            $query .= ")";

            if ($db->query($query)) {
                $session->msg('s', "Producto agregado exitosamente.");
                redirect('product.php', false);
            } else {
                $session->msg('d', 'Lo siento, el registro falló.');
                redirect('add_product.php', false);
            }
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_product.php', false);
    }
}


?>

<?php include_once('layouts/header.php'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="pull-right">
                    <a href="product.php" class="btn btn-primary">Volver a inventario</a>
                </div>
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Agregar producto</span>
                </strong>
            </div>
            <div class="panel-body">
                <div class="col-md-12">
                    <form method="post" action="add_product.php" class="clearfix">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-th-large"></i>
                                </span>
                                <input type="text" class="form-control" name="product-title" placeholder="Descripción" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-barcode"></i>
                                </span>
                                <input type="text" class="form-control" name="material-code" placeholder="Código Material" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-tag"></i>
                                </span>
                                <input type="text" class="form-control" name="product-model" placeholder="Modelo" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4">
                                    <select class="form-control" name="product-categorie" required>
                                        <option value="">Selecciona una unidad</option>
                                        <?php foreach ($all_categories as $cat) : ?>
                                            <option value="<?php echo (int)$cat['id'] ?>">
                                                <?php echo $cat['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" name="product-supplier" required>
                                        <option value="">Selecciona un proveedor</option>
                                        <?php foreach ($all_suppliers as $supplier) : ?>
                                            <option value="<?php echo (int)$supplier['id'] ?>">
                                                <?php echo $supplier['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" name="used-by" required>
                                        <option value="">Selecciona el tipo de uso</option>
                                        <option value="Bajantes">Bajantes</option>
                                        <option value="Construcción">Construcción</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-shopping-cart"></i>
                                        </span>
                                        <input type="number" class="form-control" name="product-quantity" placeholder="Cantidad" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <select class="form-control" name="warehouse_id" required>
                                    <option value="">Selecciona almacén al que se agregará el material</option>
                                    <?php 
                                    $all_warehouses = find_all('warehouses');
                                    foreach ($all_warehouses as $warehouse): ?>
                                        <option value="<?php echo (int)$warehouse['id']; ?>">
                                            <?php echo remove_junk($warehouse['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                        <button type="submit" name="add_product" class="btn btn-danger">Agregar material</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>





