<style>
  /* Reforzar la visibilidad del dropdown del perfil */
  .profile {
    position: relative;
    z-index: 9999;
  }

  .profile .dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    left: auto;
    z-index: 9999 !important;
    display: none;
  }

  .profile.open .dropdown-menu {
    display: block;
  }

  .panel,
  .panel-heading,
  .table-responsive,
  .container-fluid,
  .page {
    overflow: visible !important;
    z-index: auto;
  }

  /* Imagen circular */
  .img-inline {
    width: 30px;
    height: 30px;
    object-fit: cover;
    border-radius: 50%;
  }
</style>



<?php
$page_title = 'Lista de productos';
require_once('includes/load.php');
page_require_level(2);

// Obtener filtros
$warehouse_id = isset($_GET['warehouse']) ? (int)$_GET['warehouse'] : 0;

// Procesar búsqueda
if (isset($_POST['search'])) {
    $search_term = trim($db->escape($_POST['search_term']));

    if (!empty($search_term)) {
        if ($warehouse_id > 0) {
            // Buscar productos con filtro de almacén
            $products = $db->query("
                SELECT 
                    p.*, 
                    s.name AS supplier, 
                    c.name AS categorie
                FROM products p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN categories c ON p.categorie_id = c.id
                WHERE 
                    p.warehouse_id = {$warehouse_id} AND (
                        p.material_code LIKE '%{$search_term}%' OR
                        p.modelo LIKE '%{$search_term}%' OR
                        p.name LIKE '%{$search_term}%'
                    )
            ");
        } else {
            // Buscar productos en todos los almacenes
            $products = $db->query("
                SELECT 
                    p.*, 
                    s.name AS supplier, 
                    c.name AS categorie
                FROM products p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN categories c ON p.categorie_id = c.id
                WHERE 
                    p.material_code LIKE '%{$search_term}%' OR
                    p.modelo LIKE '%{$search_term}%' OR
                    p.name LIKE '%{$search_term}%'
            ");
        }

        if ($products->num_rows > 0) {
            $products = $products->fetch_all(MYSQLI_ASSOC);
        } else {
            $session->msg("d", "Producto no encontrado.");
            $products = [];
        }
    } else {
        $session->msg("d", "Ingresa un término de búsqueda.");
        $products = [];
    }
} else {
    // Mostrar todo
    if ($warehouse_id > 0) {
        $products = find_products_by_warehouse($warehouse_id);
    } else {
        $products = find_all_products();
    }
}
?>

<?php include_once('layouts/header.php'); ?>

<style>
    thead th {
        background-color: #333;
        color: white;
    }
    .panel-heading {
        position: sticky;
        top: 0;
        z-index: 1000;
        background-color: white;
    }
    .btn-margin-right {
        margin-right: 10px;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <div class="row">
                    <div class="col-md-7">
                        <form action="product.php?warehouse=<?php echo $warehouse_id; ?>" method="post">
                            <div class="input-group">
                                <input type="text" name="search_term" class="form-control" placeholder="Buscar producto">
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="submit" name="search">Buscar</button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="pull-left btn-margin-right">
                    <select id="warehouse-select" class="form-control" onchange="location = this.value;">
                        <option value="product.php">Selecciona un almacén</option>
                        <?php 
                        $all_warehouses = find_all('warehouses');
                        foreach ($all_warehouses as $warehouse): ?>
                            <option value="product.php?warehouse=<?php echo (int)$warehouse['id']; ?>" <?php if ($warehouse_id == (int)$warehouse['id']) echo 'selected'; ?>>
                                <?php echo remove_junk($warehouse['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="pull-left btn-margin-right">
                    <a href="sub_warehouses.php" class="btn btn-primary">Sub-Almacenes</a>
                </div>
                <div class="pull-right">
                    <a href="add_product.php" class="btn btn-primary">Agregar producto</a>
                </div>
            </div>

            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th class="text-center">Código Material</th>
                                <th class="text-center">Modelo</th>
                                <th class="text-center">Descripción</th>
                                <th class="text-center">Proveedor</th>
                                <th class="text-center">Usado Por</th>
                                <th class="text-center" style="width: 100px;">Acciones</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Traspasos</th>
                                <th class="text-center">Devueltos</th>
                                <th class="text-center">Liquidados</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="text-center"><?php echo count_id(); ?></td>
                                <td class="text-center"><?php echo remove_junk($product['material_code']); ?></td>
                                <td class="text-center"><?php echo remove_junk($product['modelo']); ?></td>
                                <td><?php echo remove_junk($product['name']); ?></td>
                                <td class="text-center"><?php echo remove_junk($product['supplier']); ?></td>
                                <td class="text-center"><?php echo remove_junk($product['used_by']); ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="edit_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-info btn-xs" title="Editar" data-toggle="tooltip">
                                            <span class="glyphicon glyphicon-edit"></span>
                                        </a>
                                        <a href="delete_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-danger btn-xs" title="Eliminar" data-toggle="tooltip" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-center"><?php echo remove_junk($product['categorie']); ?></td>
                                <td class="text-center"><?php echo remove_junk($product['quantity']); ?></td>
                                <td class="text-center"><?php echo remove_junk($product['traslados']); ?></td>
                                <td class="text-center"><?php echo remove_junk($product['devoluciones']); ?></td>
                                <td class="text-center"><?php echo remove_junk($product['liquidaciones']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>
