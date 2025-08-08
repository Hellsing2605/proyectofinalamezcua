<?php
require_once('includes/load.php');

// Verifica si el usuario tiene permiso para eliminar una recepción
page_require_level(2);

if(isset($_GET['id'])){
  $reception_id = (int)$_GET['id'];
  
  // Encuentra la recepción por ID
  $reception = find_by_id('receptions', $reception_id);
  
  if(!$reception){
    $session->msg("d","Recepción no encontrada.");
    redirect('receptions.php');
  }

  // Elimina la recepción
  $delete_id = delete_by_id('receptions', $reception_id);
  if($delete_id){
      $session->msg("s","Recepción eliminada.");
      redirect('receptions.php');
  } else {
      $session->msg("d","Eliminación falló.");
      redirect('receptions.php');
  }
} else {
  $session->msg("d","ID de recepción no especificado.");
  redirect('receptions.php');
}
?>
