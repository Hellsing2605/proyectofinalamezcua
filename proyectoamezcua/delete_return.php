<?php
require_once('includes/load.php');

// Verifica si el usuario tiene permiso para eliminar un traslado
page_require_level(2);

if(isset($_GET['id'])){
  $return_id = (int)$_GET['id'];
  
  // Encuentra el traslado por ID
  $return = find_by_id('returns', $return_id);
  
  if(!$return){
    $session->msg("d","Devolución no encontrada.");
    redirect('returns.php');
  }

  // Elimina el traslado
  $delete_id = delete_by_id('returns', $return_id);
  if($delete_id){
      $session->msg("s","Devolución eliminada.");
      redirect('returns.php');
  } else {
      $session->msg("d","Eliminación falló.");
      redirect('returns.php');
  }
} else {
  $session->msg("d","ID de devolución no especificado.");
  redirect('returns.php');
}
?>