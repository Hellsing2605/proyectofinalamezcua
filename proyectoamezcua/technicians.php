<head>
    <style>
thead th {
            background-color: #333; /* Fondo negro claro */
            color: white; /* Texto blanco */
        }
    </style>
    
</head>
<?php
$page_title = 'Lista de técnicos';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

$all_technicians = find_all('technicians');
?>
<?php
 if(isset($_POST['add_technician'])){
   $req_fields = array('technician-name', 'city', 'email', 'phone');
   validate_fields($req_fields);
   $technician_name = remove_junk($db->escape($_POST['technician-name']));
   $city = remove_junk($db->escape($_POST['city']));
   $email = remove_junk($db->escape($_POST['email']));
   $phone = remove_junk($db->escape($_POST['phone']));
   if(empty($errors)){
      $sql  = "INSERT INTO technicians (name, city, email, phone)";
      $sql .= " VALUES ('{$technician_name}', '{$city}', '{$email}', '{$phone}')";
      if($db->query($sql)){
        $session->msg("s", "Técnico agregado exitosamente.");
        redirect('technicians.php',false);
      } else {
        $session->msg("d", "Lo siento, registro falló");
        redirect('technicians.php',false);
      }
   } else {
     $session->msg("d", $errors);
     redirect('technicians.php',false);
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
          <span>Agregar técnico</span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="technicians.php">
          <div class="form-group">
            <input type="text" class="form-control" name="technician-name" placeholder="Nombre del técnico" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="city" placeholder="Ciudad" required>
          </div>
          <div class="form-group">
            <input type="email" class="form-control" name="email" placeholder="Correo electrónico" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="phone" placeholder="Número de teléfono" required>
          </div>
          <button type="submit" name="add_technician" class="btn btn-primary">Agregar técnico</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Lista de técnicos</span>
        </strong>
      </div>
      <div class="panel-body">
        <div class="table-responsive"> <!-- Envuelve la tabla para hacerla sensible -->
          <table class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>Nombre del técnico</th>
                <th>Ciudad</th>
                <th>Correo electrónico</th>
                <th>Número de teléfono</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($all_technicians ?? [] as $technician):?>
                <tr>
                  <td class="text-center"><?php echo count_id();?></td>
                  <td><?php echo remove_junk(ucfirst($technician['name'])); ?></td>
                  <td><?php echo remove_junk(ucfirst($technician['city'])); ?></td>
                  <td><?php echo remove_junk(ucfirst($technician['email'])); ?></td>
                  <td><?php echo remove_junk(ucfirst($technician['phone'])); ?></td>
                  <td class="text-center">
                    <div class="btn-group">
                      <a href="edit_technician.php?id=<?php echo (int)$technician['id'];?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Editar">
                        <span class="glyphicon glyphicon-edit"></span>
                      </a>
                      <a href="delete_technician.php?id=<?php echo (int)$technician['id'];?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este técnico?')">
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
</div>
<?php include_once('layouts/footer.php'); ?>
