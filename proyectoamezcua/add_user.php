<?php
  $page_title = 'Agregar usuarios';
  require_once('includes/load.php');

  // Solo el nivel 1 (admin) puede agregar usuarios
  page_require_level(1);

  $groups = find_all('user_groups');

  // Función para validar fortaleza de contraseña
  function is_strong_password($password) {
    return preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $password);
  }

  if (isset($_POST['add_user'])) {
    $req_fields = array('full-name','username','password','level');
    validate_fields($req_fields);

    $name      = remove_junk($db->escape($_POST['full-name']));
    $username  = remove_junk($db->escape($_POST['username']));
    $password  = $_POST['password']; // No aplicar remove_junk aquí para no alterar símbolos
    $user_level = (int)$db->escape($_POST['level']);

    // Validar contraseña segura
    if (!is_strong_password($password)) {
      $session->msg('d', 'La contraseña debe tener mínimo 8 caracteres, al menos una mayúscula, un número y un símbolo.');
      redirect('add_user.php', false);
    }

    // Hashear con password_hash (algoritmo moderno y seguro)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Verificar errores antes de insertar
    if (empty($errors)) {
      $query  = "INSERT INTO users (name, username, password, user_level, status) ";
      $query .= "VALUES ('{$name}', '{$username}', '{$hashed_password}', '{$user_level}', '1')";

      if ($db->query($query)) {
        $session->msg('s', "Cuenta de usuario creada correctamente.");
        redirect('add_user.php', false);
      } else {
        $session->msg('d', 'Error al crear la cuenta. Intenta de nuevo.');
        redirect('add_user.php', false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('add_user.php', false);
    }
  }
?>

<?php include_once('layouts/header.php'); ?>
<?php echo display_msg($msg); ?>

<div class="row">
  <div class="panel panel-default">
    <div class="panel-heading">
      <strong><span class="glyphicon glyphicon-th"></span> Agregar usuario</strong>
    </div>
    <div class="panel-body">
      <div class="col-md-6">
        <form method="post" action="add_user.php">
          <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" class="form-control" name="full-name" placeholder="Nombre completo" required>
          </div>
          <div class="form-group">
            <label for="username">Usuario</label>
            <input type="text" class="form-control" name="username" placeholder="Nombre de usuario" required>
          </div>
          <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" class="form-control" name="password" placeholder="Contraseña" 
              pattern="(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}" 
              title="Debe tener al menos 8 caracteres, una mayúscula, un número y un símbolo." required>
          </div>
          <div class="form-group">
            <label for="level">Rol de usuario</label>
            <select class="form-control" name="level" required>
              <?php foreach ($groups as $group): ?>
                <option value="<?php echo $group['group_level']; ?>">
                  <?php echo ucwords($group['group_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group clearfix">
            <button type="submit" name="add_user" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>

