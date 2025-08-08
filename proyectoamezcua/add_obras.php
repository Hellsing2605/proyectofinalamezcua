<head>
    <style>
        thead th {
            background-color: #333; /* Fondo negro claro */
            color: white; /* Texto blanco */
        }
    </style>
</head>
<?php
$page_title = 'Lista de Obras';
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario para ver esta página
page_require_level(1);

$all_obras = find_all('obras');
?>
<?php
 if(isset($_POST['add_obra'])){
  $req_fields = array('operacion', 'oei', 'oe', 'central', 'ruta', 'pep');
  validate_fields($req_fields);
  $operacion = remove_junk($db->escape($_POST['operacion']));
  $oei = remove_junk($db->escape($_POST['oei']));
  $oe = remove_junk($db->escape($_POST['oe']));
  $central = remove_junk($db->escape($_POST['central']));
  $ruta = remove_junk($db->escape($_POST['ruta']));
  $pep = remove_junk($db->escape($_POST['pep']));
  $status = 1; // Asumiendo 1 representa 'Abierta'

  if (empty($errors)) {
   $sql  = "INSERT INTO obras (operacion, oei, oe, central, ruta, pep, status)";
   $sql .= " VALUES ('{$operacion}', '{$oei}', '{$oe}', '{$central}', '{$ruta}', '{$pep}', '{$status}')";
   if ($db->query($sql)) {
       $session->msg("s", "Obra agregada exitosamente.");
       redirect('add_obras.php', false);
   } else {
       $session->msg("d", "Lo siento, el registro falló");
       redirect('add_obras.php', false);
   }
 } else {
   $session->msg("d", $errors);
   redirect('add_obras.php', false);
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
          <span>Agregar Obra</span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="add_obras.php">
          <div class="form-group">
            <input type="text" class="form-control" name="operacion" placeholder="Operación" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="oei" placeholder="OEI" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="oe" placeholder="OE" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="central" placeholder="Central" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="ruta" placeholder="Ruta" required>
          </div>
          <div class="form-group">
            <input type="text" class="form-control" name="pep" placeholder="PEP" required>
          </div>
          <button type="submit" name="add_obra" class="btn btn-primary">Agregar Obra</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Lista de Obras</span>
        </strong>
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>Operación</th>
                <th>OEI</th>
                <th>OE</th>
                <th>Central</th>
                <th>Ruta</th>
                <th>PEP</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($all_obras as $obra):?>
                <tr>
                  <td class="text-center"><?php echo count_id();?></td>
                  <td><?php echo remove_junk($obra['operacion']); ?></td>
                  <td><?php echo remove_junk($obra['oei']); ?></td>
                  <td><?php echo remove_junk($obra['oe']); ?></td>
                  <td><?php echo remove_junk($obra['central']); ?></td>
                  <td><?php echo remove_junk($obra['ruta']); ?></td>
                  <td><?php echo remove_junk($obra['pep']); ?></td>
                  <td class="text-center">
                    <div class="btn-group">
                      <a href="edit_obra.php?id=<?php echo (int)$obra['id'];?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Editar">
                        <span class="glyphicon glyphicon-edit"></span>
                      </a>
                      <a href="delete_obra.php?id=<?php echo (int)$obra['id'];?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar esta obra?')">
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
