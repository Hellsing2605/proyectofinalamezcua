<?php
require_once('includes/load.php');

// Verifica si el usuario tiene permiso para eliminar un traslado
page_require_level(2);

if(isset($_GET['id'])){
  $liquidation_id = (int)$_GET['id'];
  
  // Encuentra la liquidación por ID
  $liquidation = find_by_id('liquidations', $liquidation_id);
  
  if(!$liquidation){
    $session->msg("d","Liquidación no encontrada.");
    redirect('liquidations.php');
  }

  // Elimina la liquidación
  $delete_id = delete_by_id('liquidations', $liquidation_id);
  if($delete_id){
      $session->msg("s","Liquidación eliminada.");
      redirect('liquidations.php');
  } else {
      $session->msg("d","Eliminación falló.");
      redirect('liquidations.php');
  }
} else {
  $session->msg("d","ID de liquidación no especificado.");
  redirect('liquidations.php');
}
?>
