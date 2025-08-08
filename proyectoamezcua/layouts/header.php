<?php $user = current_user(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>
    <?php 
      if (!empty($page_title))
        echo remove_junk($page_title);
      elseif (!empty($user))
        echo ucfirst($user['name']);
      else
        echo "Sistema de almacén";
    ?>
  </title>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css" />
  <link rel="stylesheet" href="libs/css/main.css" /> 

  <!-- Fixes para el dropdown de perfil -->
  <style>
    .header-content {
      position: relative;
      z-index: 1040;
    }

    .profile {
      position: relative;
    }

    .profile .dropdown-menu {
      position: absolute;
      top: 100%;
      right: 0;
      left: auto;
      z-index: 9999;
      display: none;
    }

    .profile.open .dropdown-menu {
      display: block;
    }

    .panel,
    .container-fluid,
    .page {
      overflow: visible !important;
    }

    .img-inline {
      width: 30px;
      height: 30px;
      object-fit: cover;
      border-radius: 50%;
    }
  </style>
</head>

<body>
<?php if ($session->isUserLoggedIn(true)): ?>
  <header id="header">
    <div class="logo pull-left">WareSoft</div>
    <div class="header-content">
      <div class="header-date pull-left">
        <?php
          date_default_timezone_set('America/Mexico_City');
          echo "<strong>" . date("d/m/Y  g:i a") . "</strong>";
        ?>
      </div>
      <div class="pull-right clearfix">
        <ul class="info-menu list-inline list-unstyled">
          <li class="profile dropdown">
            <a href="#" class="toggle dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
              <img src="uploads/users/<?php echo !empty($user['image']) ? $user['image'] : 'default.png'; ?>" alt="user-image" class="img-circle img-inline">
              <span><?php echo remove_junk(ucfirst($user['name'])); ?> <i class="caret"></i></span>
            </a>
            <ul class="dropdown-menu">
              <li>
                <a href="profile.php?id=<?php echo (int)$user['id']; ?>">
                  <i class="glyphicon glyphicon-user"></i> Perfil
                </a>
              </li>
              <li>
                <a href="edit_account.php" title="Editar cuenta">
                  <i class="glyphicon glyphicon-cog"></i> Configuración
                </a>
              </li>
              <li class="last">
                <a href="logout.php">
                  <i class="glyphicon glyphicon-off"></i> Salir
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </header>

  <!-- Sidebar según tipo de usuario -->
  <div class="sidebar">
    <?php if ($user['user_level'] === '1'): ?>
      <?php include_once('admin_menu.php'); ?>
    <?php elseif ($user['user_level'] === '2'): ?>
      <?php include_once('special_menu.php'); ?>
    <?php elseif ($user['user_level'] === '3'): ?>
      <?php include_once('user_menu.php'); ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- Contenedor principal -->
<div class="page">
  <div class="container-fluid">

