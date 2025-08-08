<?php
// mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
include_once('includes/load.php');
?>

<?php
$req_fields = array('username', 'password');

validate_fields($req_fields);

$username = remove_junk($_POST['username']);
$password = remove_junk($_POST['password']);

if (empty($errors)) {
  $user_id = authenticate($username, $password);
  
  if ($user_id) {
    $session->login($user_id);
    
    updateLastLogIn($user_id);
    
    $session->msg("s", "Bienvenido a KAIZEN.");
    
    redirect('home.php', false);
  } else {
    $session->msg("d", "Nombre de usuario y/o contraseña incorrecto.");
    
    redirect('index.php', false);
  }
} else {
  $session->msg("d", $errors);
  
  redirect('index.php', false);
}
?>
