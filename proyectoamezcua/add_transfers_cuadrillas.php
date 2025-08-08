<?php
$page_title = 'Agregar traslado';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(2);
$products = find_products_by_used_by('Construccion');
$technicians = find_all('technicians');
$cuadrillas = find_all('cuadrillas');
$supervisors = find_all('supervisors');
$warehouses = find_all('warehouses');
$obras = get_open_works(); // Usamos la nueva función para obtener solo obras abiertas
// Obtener productos según el almacén seleccionado
$warehouse_id = isset($_POST['warehouse-id']) ? (int)$_POST['warehouse-id'] : 0;
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

if (isset($_POST['add_transfer'])) {
    $req_fields = array('supervisor_id', 'date', 'warehouse-id', 'observations');
    validate_fields($req_fields);

    if (empty($errors)) {
        $t_code = generate_transfer_code();

        $supervisor_id = (int)$_POST['supervisor_id'];
        $t_date = remove_junk($db->escape($_POST['date']));
        $warehouse_id = (int)$_POST['warehouse-id'];
        $observations = remove_junk($db->escape($_POST['observations']));
        $technician_id = !empty($_POST['technician_id']) ? (int)$_POST['technician_id'] : null;
        $cuadrilla_id = !empty($_POST['cuadrilla_id']) ? (int)$_POST['cuadrilla_id'] : null;
        $obra_id = !empty($_POST['obra_id']) ? (int)$_POST['obra_id'] : null;

        // Validar si la obra está cerrada
        if ($obra_id && calculate_obra_status($obra_id) == 'Cerrada') {
            $session->msg('d', "La obra que desea seleccionar está cerrada.");
            redirect('add_transfers_cuadrillas.php', false);
        }

        $query  = "INSERT INTO transfers (transfer_code, supervisor_id, date, warehouse_id, technician_id, cuadrilla_id, obra_id, observations) VALUES (";
        $query .= " '{$t_code}', '{$supervisor_id}', '{$t_date}', '{$warehouse_id}', ";
        $query .= $technician_id ? "'{$technician_id}', " : "NULL, ";
        $query .= $cuadrilla_id ? "'{$cuadrilla_id}', " : "NULL, ";
        $query .= $obra_id ? "'{$obra_id}', " : "NULL, ";
        $query .= "'{$observations}')";

        if ($db->query($query)) {
            $transfer_id = $db->insert_id();

            foreach ($_POST['product'] as $index => $product_id) {
                $quantity = (int)$_POST['quantity'][$index];
                if ($product_id && $quantity) {
                    $product = find_by_id('products', $product_id);
                    $available_quantity = (int)$product['quantity'];

                    if ($quantity <= $available_quantity) {
                        $query  = "INSERT INTO transfer_items (transfer_id, product_id, quantity) VALUES (";
                        $query .= " '{$transfer_id}', '{$product_id}', '{$quantity}')";
                        $db->query($query);

                        $query  = "UPDATE products SET traslados = traslados + '{$quantity}', quantity = quantity - '{$quantity}' WHERE id = '{$product_id}'";
                        $db->query($query);
                    } else {
                        $session->msg('d', "La cantidad a traspasar de '{$product['name']}' supera la cantidad disponible.");
                        redirect('add_transfers_cuadrillas.php', false);
                    }
                }
            }

            $session->msg('s', "Transpaso agregado exitosamente.");
            redirect('transfers.php', false);
        } else {
            $session->msg('d', 'Lo siento, el registro falló.');
            redirect('add_transfers_cuadrillas.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_transfers_cuadrillas.php', false);
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
                <form method="post" action="add_transfers_cuadrillas.php">
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
                        <label for="cuadrilla_id">Se realiza el traspaso a</label>
                        <select class="form-control" name="cuadrilla_id" required>
                            <option value="">Selecciona una cuadrilla</option>
                            <?php foreach ($cuadrillas as $cuadrilla): ?>
                                <option value="<?php echo $cuadrilla['id']; ?>">
                                    <?php echo $cuadrilla['name']; ?>
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
                    <div class="form-group">
                        <label for="observations">Observaciones</label>
                        <textarea class="form-control" name="observations" placeholder="Observaciones"></textarea>
                    </div>
                    <div id="product-fields">
                        <div class="form-group product-group">
                            <label for="product">Material</label>
                            <select class="form-control select-small" name="product[]" required>
                                <option value="">Selecciona un producto</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>">
                                    <?php echo $product['material_code'] . ' - ' . $product['name'].' - Cantidad disponible: ' . $product['quantity']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <br>
                            <label for="quantity">Cantidad</label>
                            <input type="number" class="form-control select-small" name="quantity[]" min="1" placeholder="Cantidad" required>
                            <br><button type="button" class="btn btn-danger btn-remove-product">Eliminar fila de material</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success" id="add-product">Agregar Material</button>
                    <button type="submit" name="add_transfer" class="btn btn-primary">Agregar Traspaso</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('add-product').addEventListener('click', function () {
        var productFields = document.getElementById('product-fields');
        var newField = document.createElement('div');
        newField.className = 'form-group product-group';
        newField.innerHTML = `
            <label for="product">Material</label>
            <select class="form-control select-small" name="product[]" required>
                <option value="">Selecciona un producto</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>">
                    <?php echo $product['material_code'] . ' - ' . $product['name'].' - Cantidad disponible: ' . $product['quantity']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="quantity">Cantidad</label>
            <br><input type="number" class="form-control select-small" name="quantity[]" min="1" placeholder="Cantidad" required>
            <br><br><button type="button" class="btn btn-danger btn-remove-product">Eliminar fila de material</button>
        `;
        productFields.appendChild(newField);

        // Agregar evento al botón de eliminar
        var removeButtons = document.querySelectorAll('.btn-remove-product');
        removeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                button.parentElement.remove();
            });
        });
    });

    // Agregar evento a los botones de eliminar ya existentes
    var removeButtons = document.querySelectorAll('.btn-remove-product');
    removeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            button.parentElement.remove();
        });
    });
});
</script>

<?php include_once('layouts/footer.php'); ?>

