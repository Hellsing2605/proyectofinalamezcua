<?php
$page_title = 'Editar proveedor';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

// Comprobar si se recibió el ID del proveedor
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $session->msg("d","ID del proveedor falta o no es válido.");
    redirect('suppliers.php');
}

// Obtener el proveedor de la base de datos utilizando el ID recibido
$supplier = find_by_id('suppliers', (int)$_GET['id']);

// Verificar si el proveedor fue encontrado
if(!$supplier){
    $session->msg("d","Proveedor no encontrado.");
    redirect('suppliers.php');
}

if(isset($_POST['edit_supplier'])){
  $req_fields = array('supplier-name', 'city', 'rfc', 'email', 'phone');
  validate_fields($req_fields);
  $supplier_name = remove_junk($db->escape($_POST['supplier-name']));
  $city = remove_junk($db->escape($_POST['city']));
  $rfc = remove_junk($db->escape($_POST['rfc']));
  $email = remove_junk($db->escape($_POST['email']));
  $phone = remove_junk($db->escape($_POST['phone']));
  if(empty($errors)){
        $sql = "UPDATE suppliers SET name='{$supplier_name}', city='{$city}', rfc='{$rfc}', email='{$email}', phone='{$phone}'";
       $sql .= " WHERE id='{$_GET['id']}'";
     $result = $db->query($sql);
     if($result && $db->affected_rows() === 1) {
       $session->msg("s", "Proveedor actualizado con éxito.");
       redirect('suppliers.php',false);
     } else {
       $session->msg("d", "Lo siento, actualización falló.");
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
   <div class="col-md-5">
     <div class="panel panel-default">
       <div class="panel-heading">
         <strong>
           <span class="glyphicon glyphicon-th"></span>
           <span>Editando <?php echo remove_junk(ucfirst($supplier['name']));?></span>
        </strong>
       </div>
       <div class="panel-body">
         <form method="post" action="edit_supplier.php?id=<?php echo $_GET['id'];?>">
           <div class="form-group">
               <input type="text" class="form-control" name="supplier-name" value="<?php echo remove_junk(ucfirst($supplier['name']));?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="city" value="<?php echo remove_junk(ucfirst($supplier['city']));?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="rfc" value="<?php echo remove_junk(ucfirst($supplier['rfc']));?>">
           </div>
           <div class="form-group">
               <input type="email" class="form-control" name="email" value="<?php echo remove_junk(ucfirst($supplier['email']));?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="phone" value="<?php echo remove_junk(ucfirst($supplier['phone']));?>">
           </div>
           <button type="submit" name="edit_supplier" class="btn btn-primary">Actualizar proveedor</button>
       </form>
       </div>
     </div>
   </div>
</div>

<?php include_once('layouts/footer.php'); ?>
