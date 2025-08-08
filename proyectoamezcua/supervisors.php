<head>
    <style>
thead th {
            background-color: #333; /* Fondo negro claro */
            color: white; /* Texto blanco */
        }
    </style>
    
</head>
<?php
$page_title = 'Lista de supervisores';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

$all_supervisors = find_all('supervisors');
?>
<?php
 if(isset($_POST['add_supervisor'])){
   $req_fields = array('supervisor-name', 'city', 'email', 'phone');
   validate_fields($req_fields);
   $supervisor_name = remove_junk($db->escape($_POST['supervisor-name']));
   $city = remove_junk($db->escape($_POST['city']));
   $email = remove_junk($db->escape($_POST['email']));
   $phone = remove_junk($db->escape($_POST['phone']));
   if(empty($errors)){
      $sql  = "INSERT INTO supervisors (name, city, email, phone)";
      $sql .= " VALUES ('{$supervisor_name}', '{$city}', '{$email}', '{$phone}')";
      if($db->query($sql)){
        $session->msg("s", "Supervisor agregado exitosamente.");
        redirect('supervisors.php',false);
      } else {
        $session->msg("d", "Lo siento, registro falló");
        redirect('supervisors.php',false);
      }
   } else {
     $session->msg("d", $errors);
     redirect('supervisors.php',false);
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
          <span>Agregar supervisor</span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="supervisors.php">
          <div class="form-group">
            <input type="text" class="form-control" name="supervisor-name" placeholder="Nombre del supervisor" required>
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
          <button type="submit" name="add_supervisor" class="btn btn-primary">Agregar supervisor</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Lista de supervisores</span>
        </strong>
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>Nombre del supervisor</th>
                <th>Ciudad</th>
                <th>Correo electrónico</th>
                <th>Número de teléfono</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($all_supervisors ?? [] as $supervisor):?>
                <tr>
                  <td class="text-center"><?php echo count_id();?></td>
                  <td><?php echo remove_junk(ucfirst($supervisor['name'])); ?></td>
                  <td><?php echo remove_junk(ucfirst($supervisor['city'])); ?></td>
                  <td><?php echo remove_junk(ucfirst($supervisor['email'])); ?></td>
                  <td><?php echo remove_junk(ucfirst($supervisor['phone'])); ?></td>
                  <td class="text-center">
                    <div class="btn-group">
                      <a href="edit_supervisor.php?id=<?php echo (int)$supervisor['id'];?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Editar">
                        <span class="glyphicon glyphicon-edit"></span>
                      </a>
                      <a href="delete_supervisor.php?id=<?php echo (int)$supervisor['id'];?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este supervisor?')">
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
