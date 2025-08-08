<?php
$page_title = 'Editar supervisor';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

// Comprobar si se recibió el ID del supervisor
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $session->msg("d","ID del supervisor falta o no es válido.");
    redirect('supervisors.php');
}

// Obtener el supervisor de la base de datos utilizando el ID recibido
$supervisor = find_by_id('supervisors', (int)$_GET['id']);

// Verificar si el supervisor fue encontrado
if(!$supervisor){
    $session->msg("d","Supervisor no encontrado.");
    redirect('supervisors.php');
}

if(isset($_POST['edit_supervisor'])){
  $req_fields = array('supervisor-name', 'city', 'email', 'phone');
  validate_fields($req_fields);
  $supervisor_name = remove_junk($db->escape($_POST['supervisor-name']));
  $city = remove_junk($db->escape($_POST['city']));
  $email = remove_junk($db->escape($_POST['email']));
  $phone = remove_junk($db->escape($_POST['phone']));
  if(empty($errors)){
        $sql = "UPDATE supervisors SET name='{$supervisor_name}', city='{$city}', email='{$email}', phone='{$phone}'";
       $sql .= " WHERE id='{$_GET['id']}'";
     $result = $db->query($sql);
     if($result && $db->affected_rows() === 1) {
       $session->msg("s", "Supervisor actualizado con éxito.");
       redirect('supervisors.php',false);
     } else {
       $session->msg("d", "Lo siento, actualización falló.");
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
   <div class="col-md-5">
     <div class="panel panel-default">
       <div class="panel-heading">
         <strong>
           <span class="glyphicon glyphicon-th"></span>
           <span>Editando <?php echo remove_junk(ucfirst($supervisor['name']));?></span>
        </strong>
       </div>
       <div class="panel-body">
         <form method="post" action="edit_supervisor.php?id=<?php echo $_GET['id'];?>">
           <div class="form-group">
               <input type="text" class="form-control" name="supervisor-name" value="<?php echo remove_junk(ucfirst($supervisor['name']));?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="city" value="<?php echo remove_junk(ucfirst($supervisor['city']));?>">
           </div>
           <div class="form-group">
               <input type="email" class="form-control" name="email" value="<?php echo remove_junk(ucfirst($supervisor['email']));?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="phone" value="<?php echo remove_junk(ucfirst($supervisor['phone']));?>">
           </div>
           <button type="submit" name="edit_supervisor" class="btn btn-primary">Actualizar supervisor</button>
       </form>
       </div>
     </div>
   </div>
</div>

<?php include_once('layouts/footer.php'); ?>
