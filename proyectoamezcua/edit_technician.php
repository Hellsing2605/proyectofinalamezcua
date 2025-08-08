<?php
$page_title = 'Editar técnico';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

// Comprobar si se recibió el ID del técnico
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $session->msg("d","ID del técnico falta o no es válido.");
    redirect('technicians.php');
}

// Obtener el técnico de la base de datos utilizando el ID recibido
$technician = find_by_id('technicians', (int)$_GET['id']);

// Verificar si el técnico fue encontrado
if(!$technician){
    $session->msg("d","Técnico no encontrado.");
    redirect('technicians.php');
}

if(isset($_POST['edit_technician'])){
  $req_fields = array('technician-name', 'city', 'email', 'phone');
  validate_fields($req_fields);
  $technician_name = remove_junk($db->escape($_POST['technician-name']));
  $city = remove_junk($db->escape($_POST['city']));
  $email = remove_junk($db->escape($_POST['email']));
  $phone = remove_junk($db->escape($_POST['phone']));
  if(empty($errors)){
        $sql = "UPDATE technicians SET name='{$technician_name}', city='{$city}', email='{$email}', phone='{$phone}'";
       $sql .= " WHERE id='{$_GET['id']}'";
     $result = $db->query($sql);
     if($result && $db->affected_rows() === 1) {
       $session->msg("s", "Técnico actualizado con éxito.");
       redirect('technicians.php',false);
     } else {
       $session->msg("d", "Lo siento, actualización falló.");
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
   <div class="col-md-5">
     <div class="panel panel-default">
       <div class="panel-heading">
         <strong>
           <span class="glyphicon glyphicon-th"></span>
           <span>Editando <?php echo remove_junk(ucfirst($technician['name']));?></span>
        </strong>
       </div>
       <div class="panel-body">
         <form method="post" action="edit_technician.php?id=<?php echo $_GET['id'];?>">
           <div class="form-group">
               <input type="text" class="form-control" name="technician-name" value="<?php echo remove_junk(ucfirst($technician['name']));?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="city" value="<?php echo remove_junk(ucfirst($technician['city']));?>">
           </div>
           <div class="form-group">
               <input type="email" class="form-control" name="email" value="<?php echo remove_junk(ucfirst($technician['email']));?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="phone" value="<?php echo remove_junk(ucfirst($technician['phone']));?>">
           </div>
           <button type="submit" name="edit_technician" class="btn btn-primary">Actualizar técnico</button>
       </form>
       </div>
     </div>
   </div>
</div>

<?php include_once('layouts/footer.php'); ?>
