<head>
    <style>
thead th {
            background-color: #333; /* Fondo negro claro */
            color: white; /* Texto blanco */
        }
    </style>
    
</head>
<?php
  $page_title = 'Lista de almacenes';
  require_once('includes/load.php');
  // Verificar el nivel de permiso del usuario para ver esta página
  page_require_level(1);
  
  $all_warehouses = find_all('warehouses');
?>
<?php
 if(isset($_POST['add_warehouse'])){
   $req_fields = array('warehouse-name', 'city');
   validate_fields($req_fields);
   $warehouse_name = remove_junk($db->escape($_POST['warehouse-name']));
   $city = remove_junk($db->escape($_POST['city']));
   if(empty($errors)){
      $sql  = "INSERT INTO warehouses (name, city)";
      $sql .= " VALUES ('{$warehouse_name}', '{$city}')";
      if($db->query($sql)){
        $session->msg("s", "Almacén agregado exitosamente.");
        redirect('warehouses.php',false);
      } else {
        $session->msg("d", "Lo siento, registro falló");
        redirect('warehouses.php',false);
      }
   } else {
     $session->msg("d", $errors);
     redirect('warehouses.php',false);
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
          <span>Agregar almacén</span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="warehouses.php">
          <div class="form-group">
            <input type="text" class="form-control" name="warehouse-name" placeholder="Nombre del almacén" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="city" placeholder="Ciudad" required>
          </div>
          <button type="submit" name="add_warehouse" class="btn btn-primary">Agregar almacén</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Lista de almacenes</span>
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-bordered table-striped table-hover">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th>Nombre del almacén</th>
              <th>Ciudad</th>
              <th class="text-center" style="width: 100px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($all_warehouses ?? [] as $warehouse):?>
              <tr>
                <td class="text-center"><?php echo count_id();?></td>
                <td><?php echo remove_junk(ucfirst($warehouse['name'])); ?></td>
                <td><?php echo remove_junk(ucfirst($warehouse['city'])); ?></td>
                <td class="text-center">
                  <div class="btn-group">
                  
    <div class="btn-group">
        <a href="edit_warehouse.php?id=<?php echo (int)$warehouse['id'];?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Editar">
            <span class="glyphicon glyphicon-edit"></span>
        </a>
        <a href="delete_warehouse.php?id=<?php echo (int)$warehouse['id'];?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este almacén?')">
            <span class="glyphicon glyphicon-trash"></span>
        </a>
    </div>
</td>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>
