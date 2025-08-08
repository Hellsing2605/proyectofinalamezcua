<?php
require_once('includes/load.php');

// Verifica si el usuario tiene permiso para eliminar un traslado
page_require_level(2);

if(isset($_GET['id'])){
  $request_id = (int)$_GET['id'];
  
  // Encuentra el traslado por ID
  $request = find_by_id('requests', $request_id);
  
  if(!$request){
    $session->msg("d","Petición no encontrada.");
    redirect('requests.php');
  }

  // Elimina el traslado
  $delete_id = delete_by_id('requests', $request_id);
  if($delete_id){
      $session->msg("s","Petición eliminada.");
      redirect('requests.php');
  } else {
      $session->msg("d","Eliminación falló.");
      redirect('requests.php');
  }
} else {
  $session->msg("d","ID de petición no especificado.");
  redirect('requests.php');
}
?>