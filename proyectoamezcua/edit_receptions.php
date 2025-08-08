<?php
  $page_title = 'Editar Recepción';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(2);

  // Get reception data
  $reception = find_by_id('receptions', (int)$_GET['id']);
  if(!$reception){
    $session->msg("d","Falta el ID de recepción.");
    redirect('receptions.php');
  }
?>
<?php
 if(isset($_POST['update_reception'])){
    $req_fields = array('reception-code','reception-name','reception-date');
    validate_fields($req_fields);

    if(empty($errors)){
        $r_code  = remove_junk($db->escape($_POST['reception-code']));
        $r_name  = remove_junk($db->escape($_POST['reception-name']));
        $r_date  = remove_junk($db->escape($_POST['reception-date']));

        $query   = "UPDATE receptions SET";
        $query  .=" reception_code ='{$r_code}', reception_name ='{$r_name}', date ='{$r_date}'";
        $query  .=" WHERE id ='{$reception['id']}'";
        $result = $db->query($query);
        if($result && $db->affected_rows() === 1){
            $session->msg('s',"Recepción actualizada.");
            redirect('receptions.php', false);
        } else {
            $session->msg('d',' Lo siento, actualización falló.');
            redirect('edit_receptions.php?id='.$reception['id'], false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('edit_receptions.php?id='.$reception['id'], false);
    }
 }
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
  <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading">
          <strong>
            <span class="glyphicon glyphicon-th"></span>
            <span>Editar Recepción</span>
         </strong>
        </div>
        <div class="panel-body">
          <form method="post" action="edit_receptions.php?id=<?php echo (int)$reception['id'] ?>">
            <div class="form-group">
              <label for="reception-code">Código Recepción</label>
              <input type="text" class="form-control" name="reception-code" value="<?php echo remove_junk($reception['reception_code']); ?>">
            </div>
            <div class="form-group">
              <label for="reception-name">Nombre de la Recepción</label>
              <input type="text" class="form-control" name="reception-name" value="<?php echo remove_junk($reception['reception_name']); ?>">
            </div>
            <div class="form-group">
              <label for="reception-date">Fecha</label>
              <input type="date" class="form-control" name="reception-date" value="<?php echo remove_junk($reception['date']); ?>">
            </div>
            <button a href="receptions.php" type="submit" name="update_reception" class="btn btn-primary">Actualizar</button>
          </form>
        </div>
      </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>

