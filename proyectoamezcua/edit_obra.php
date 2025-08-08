<?php
$page_title = 'Editar Obra';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

// Comprobar si se recibió el ID de la obra
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $session->msg("d","ID de la obra falta o no es válido.");
    redirect('add_obras.php');
}

// Obtener la obra de la base de datos utilizando el ID recibido
$obra = find_by_id('obras', (int)$_GET['id']);

// Verificar si la obra fue encontrada
if(!$obra){
    $session->msg("d","Obra no encontrada.");
    redirect('add_obras.php');
}

if(isset($_POST['edit_obra'])){
  $req_fields = array('operacion', 'oei', 'oe', 'central', 'ruta', 'pep');
  validate_fields($req_fields);
  $operacion = remove_junk($db->escape($_POST['operacion']));
  $oei = remove_junk($db->escape($_POST['oei']));
  $oe = remove_junk($db->escape($_POST['oe']));
  $central = remove_junk($db->escape($_POST['central']));
  $ruta = remove_junk($db->escape($_POST['ruta']));
  $pep = remove_junk($db->escape($_POST['pep']));
  $status = remove_junk($db->escape($_POST['status']));
  if(empty($errors)){
        $sql = "UPDATE obras SET operacion='{$operacion}', oei='{$oei}', oe='{$oe}', central='{$central}', ruta='{$ruta}', pep='{$pep}', status='{$status}'";
       $sql .= " WHERE id='{$_GET['id']}'";
     $result = $db->query($sql);
     if($result && $db->affected_rows() === 1) {
       $session->msg("s", "Obra actualizada con éxito.");
       redirect('obras.php',false);
     } else {
       $session->msg("d", "Lo siento, actualización falló.");
       redirect('add_obras.php',false);
     }
  } else {
    $session->msg("d", $errors);
    redirect('add_obras.php',false);
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
           <span>Editando <?php echo remove_junk(ucfirst($obra['operacion']));?></span>
        </strong>
       </div>
       <div class="panel-body">
         <form method="post" action="edit_obra.php?id=<?php echo $_GET['id'];?>">
           <div class="form-group">
               <input type="text" class="form-control" name="operacion" value="<?php echo remove_junk($obra['operacion']);?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="oei" value="<?php echo remove_junk($obra['oei']);?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="oe" value="<?php echo remove_junk($obra['oe']);?>" required>
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="central" value="<?php echo remove_junk($obra['central']);?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="ruta" value="<?php echo remove_junk($obra['ruta']);?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="pep" value="<?php echo remove_junk($obra['pep']);?>">
           </div>
           <div class="form-group">
               <select class="form-control" name="status">
                   <option value="abierta" <?php if($obra['status'] == 'abierta') echo 'selected'; ?>>Abierta</option>
                   <option value="cerrada" <?php if($obra['status'] == 'cerrada') echo 'selected'; ?>>Cerrada</option>
               </select>
           </div>
           <button type="submit" name="edit_obra" class="btn btn-primary">Actualizar Obra</button>
       </form>
       </div>
     </div>
   </div>
</div>

<?php include_once('layouts/footer.php'); ?>
