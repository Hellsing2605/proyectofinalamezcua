<?php
  ob_start();
  require_once('includes/load.php');
  if ($session->isUserLoggedIn(true)) { 
    redirect('home.php', false);
  }
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>@import url("https://fonts.googleapis.com/css2?family=Open+Sans:wght@200;300;400;500;600;700&display=swap");

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Open Sans", sans-serif;
}

.alert {
  padding: 15px;
  border-radius: 5px;
  margin-bottom: 20px;
  text-align: center;
  font-size: 16px;
  color: #fff; /* Color del texto en blanco */
}

.alert-danger {
  background-color: rgba(255, 0, 0, 0.8); /* Fondo rojo translúcido para mensajes de error */
}

.alert-success {
  background-color: rgba(0, 128, 0, 0.8); /* Fondo verde translúcido para mensajes de éxito */
}

.alert a.close {
  color: #fff; /* Color de la "X" para cerrar en blanco */
}


body {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  width: 100%;
  padding: 0 10px;
  position: relative;
}

body::before {
  content: "";
  position: absolute;
  width: 100%;
  height: 100%;
  background: url("fo.jpg") center/cover no-repeat;
  background-position: center;
  background-size: cover;
  z-index: -1;
}

.wrapper {
  width: 400px;
  margin: 0 auto; /* Centrar horizontalmente */
  border-radius: 8px;
  padding: 30px;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  position: relative; /* Cambiar a relative para evitar problemas de posicionamiento */
  z-index: 1;
}

form {
  display: flex;
  flex-direction: column;
}

h2 {
  font-size: 2rem;
  margin-bottom: 20px;
  color: #fff;
}

.input-field {
  position: relative;
  border-bottom: 2px solid #ccc;
  margin: 15px 0;
}

.input-field label {
  position: absolute;
  top: 50%;
  left: 0;
  transform: translateY(-50%);
  color: #fff;
  font-size: 16px;
  pointer-events: none;
  transition: 0.15s ease;
}

.input-field input {
  width: 100%;
  height: 40px;
  background: transparent;
  border: none;
  outline: none;
  font-size: 16px;
  color: #fff;
}

.input-field input:focus~label,
.input-field input:valid~label {
  font-size: 0.8rem;
  top: 10px;
  transform: translateY(-120%);
}

.forget {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: 25px 0 35px 0;
  color: #fff;
}

#remember {
  accent-color: #fff;
}

.forget label {
  display: flex;
  align-items: center;
}

.forget label p {
  margin-left: 8px;
}

.wrapper a {
  color: #efefef;
  text-decoration: none;
}

.wrapper a:hover {
  text-decoration: underline;
}

button {
  background: #fff;
  color: #000;
  font-weight: 600;
  border: none;
  padding: 12px 20px;
  cursor: pointer;
  border-radius: 3px;
  font-size: 16px;
  border: 2px solid transparent;
  transition: 0.3s ease;
}

button:hover {
  color: #fff;
  border-color: #fff;
  background: rgba(255, 255, 255, 0.15);
}

.register {
  text-align: center;
  margin-top: 30px;
  color: #fff;
}</style>
</head>
<body>
  <div class="wrapper">
  <?php echo display_msg($msg); ?> <!-- Aquí se mostrará el mensaje -->
  <form action="auth.php" method="post">
    <h2>Login</h2>
    <div class="input-field">
      <input type="text" name="username" required>
      <label>Usuario</label>
    </div>
    <div class="input-field">
      <input type="password" name="password" required>
      <label>Contraseña</label>
    </div>
    <button type="submit">Ingresar</button>
    <div class="register">
      <p>Inventarios</p>
    </div>
  </form>
</div>

</body>
<?php include_once('layouts/footer.php'); ?>
</html>
