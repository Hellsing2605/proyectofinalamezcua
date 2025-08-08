<?php
$page_title = 'Editar almacén';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

// Comprobar si se recibió el ID del almacén
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $session->msg("d","ID del almacén falta o no es válido.");
    redirect('warehouses.php');
}

// Obtener el almacén de la base de datos utilizando el ID recibido
$warehouse = find_by_id('warehouses', (int)$_GET['id']);

// Verificar si el almacén fue encontrado
if(!$warehouse){
    $session->msg("d","Almacén no encontrado.");
    redirect('warehouses.php');
}

if(isset($_POST['edit_warehouse'])){
    $req_fields = array('warehouse-name', 'city');
    validate_fields($req_fields);
    
    if(empty($errors)){
        $warehouse_name = remove_junk($db->escape($_POST['warehouse-name']));
        $city = remove_junk($db->escape($_POST['city']));
        
        $sql = "UPDATE warehouses SET name='{$warehouse_name}', city='{$city}'";
        $sql .= " WHERE id='{$_GET['id']}'";
        
        $result = $db->query($sql);
        
        if($result) {
            $session->msg("s", "Almacén actualizado con éxito.");
            redirect('warehouses.php');
        } else {
            $session->msg("d", "No se pudo actualizar el almacén.");
            redirect('edit_warehouse.php?id=' . $_GET['id']);
        }
    } else {
        $session->msg("d", $errors);
        redirect('edit_warehouse.php?id=' . $_GET['id']);
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
           <span>Editando <?php echo remove_junk(ucfirst($warehouse['name']));?></span>
        </strong>
       </div>
       <div class="panel-body">
         <form method="post" action="edit_warehouse.php?id=<?php echo $_GET['id'];?>">
           <div class="form-group">
               <input type="text" class="form-control" name="warehouse-name" value="<?php echo remove_junk(ucfirst($warehouse['name']));?>">
           </div>
           <div class="form-group">
               <input type="text" class="form-control" name="city" value="<?php echo remove_junk(ucfirst($warehouse['city']));?>">
           </div>
           <button type="submit" name="edit_warehouse" class="btn btn-primary">Actualizar almacén</button>
       </form>
       </div>
     </div>
   </div>
</div>

<?php include_once('layouts/footer.php'); ?>
