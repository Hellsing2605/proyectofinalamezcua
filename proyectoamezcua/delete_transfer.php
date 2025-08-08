<?php
require_once('includes/load.php');

// Verifica si el usuario tiene permiso para eliminar un traslado
page_require_level(2);

if(isset($_GET['id'])){
  $transfer_id = (int)$_GET['id'];
  
  // Encuentra el traslado por ID
  $transfer = find_by_id('transfers', $transfer_id);
  
  if(!$transfer){
    $session->msg("d","Traslado no encontrado.");
    redirect('transfers.php');
  }

  // Elimina el traslado
  $delete_id = delete_by_id('transfers', $transfer_id);
  if($delete_id){
      $session->msg("s","Traslado eliminado.");
      redirect('transfers.php');
  } else {
      $session->msg("d","Eliminación falló.");
      redirect('transfers.php');
  }
} else {
  $session->msg("d","ID de traslado no especificado.");
  redirect('transfers.php');
}
?>
