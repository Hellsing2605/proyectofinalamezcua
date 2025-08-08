<head>
    <style>
thead th {
            background-color: #333; /* Fondo negro claro */
            color: white; /* Texto blanco */
        }
    </style>
    
</head>
<?php
$page_title = 'Lista de proveedores';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

$all_suppliers = find_all('suppliers');
?>
<?php
 if(isset($_POST['add_supplier'])){
   $req_fields = array('supplier-name', 'city', 'rfc', 'email', 'phone');
   validate_fields($req_fields);
   $supplier_name = remove_junk($db->escape($_POST['supplier-name']));
   $city = remove_junk($db->escape($_POST['city']));
   $rfc = remove_junk($db->escape($_POST['rfc']));
   $email = remove_junk($db->escape($_POST['email']));
   $phone = remove_junk($db->escape($_POST['phone']));
   if(empty($errors)){
      $sql  = "INSERT INTO suppliers (name, city, rfc, email, phone)";
      $sql .= " VALUES ('{$supplier_name}', '{$city}', '{$rfc}', '{$email}', '{$phone}')";
      if($db->query($sql)){
        $session->msg("s", "Proveedor agregado exitosamente.");
        redirect('suppliers.php',false);
      } else {
        $session->msg("d", "Lo siento, registro falló");
        redirect('suppliers.php',false);
      }
   } else {
     $session->msg("d", $errors);
     redirect('suppliers.php',false);
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
  <div class="col-md-5">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Agregar proveedor</span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="suppliers.php">
          <div class="form-group">
            <input type="text" class="form-control" name="supplier-name" placeholder="Nombre del proveedor" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="city" placeholder="Ciudad" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="rfc" placeholder="RFC" required>
          </div>
          <div class="form-group">
            <input type="email" class="form-control" name="email" placeholder="Correo electrónico" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="phone" placeholder="Número de teléfono" required>
          </div>
          <button type="submit" name="add_supplier" class="btn btn-primary">Agregar proveedor</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Lista de proveedores</span>
        </strong>
      </div>
      <div class="panel-body">
        <div class="table-responsive"> <!-- Envuelve la tabla para hacerla sensible -->
          <table class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>Nombre del proveedor</th>
                <th>Ciudad</th>
                <th>RFC</th>
                <th>Correo electrónico</th>
                <th>Número de teléfono</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
          <tbody>
            <?php foreach ($all_suppliers ?? [] as $supplier):?>
              <tr>
                <td class="text-center"><?php echo count_id();?></td>
                <td><?php echo remove_junk(ucfirst($supplier['name'])); ?></td>
                <td><?php echo remove_junk(ucfirst($supplier['city'])); ?></td>
                <td><?php echo remove_junk(ucfirst($supplier['rfc'])); ?></td>
                <td><?php echo remove_junk(ucfirst($supplier['email'])); ?></td>
                <td><?php echo remove_junk(ucfirst($supplier['phone'])); ?></td>
                <td class="text-center">
                  <div class="btn-group">
                    <a href="edit_supplier.php?id=<?php echo (int)$supplier['id'];?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Editar">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a>
                    <a href="delete_supplier.php?id=<?php echo (int)$supplier['id'];?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este proveedor?')">
                      <span class="glyphicon glyphicon-trash"></span>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>
