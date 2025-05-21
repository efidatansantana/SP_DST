
<?php
  session_start();

  include_once 'inc/funciones.php';

  $funciones = new Funciones();

  if (isset($_SESSION['usuario'])) {
      header('Location: dash.php');
      exit();
  }

  // Si se ha enviado el formulario
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $usuario = $_POST['usuario'];
      $pwd = $_POST['pwd'];

      $user = $funciones->Login($usuario, $pwd);
      if ($user) {
          $_SESSION['usuario'] = $usuario;
          header('Location: dash.php');
          exit();
      } else {
          $mensajeError = "Usuario o contraseña incorrectos";
      }
  }
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="icon" type="image/x-icon" href="media/favicon.ico">

<style>
  body {
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: linear-gradient(-45deg, #e73c7e ,#ee7752, #23a6d5, #008080);             
    background-size: 400% 400%;
    animation: gradient 55s ease infinite;
    height: 100vh;
    font-family: 'MS Sans Serif', Geneva, sans-serif;
  }

  body {

  }

  @keyframes gradient {
    0% {
      background-position: 0% 50%;
    }
    50% {
      background-position: 100% 50%;
    }
    100% {
      background-position: 0% 50%;
    }
  }


  .window {
    width: 300px;
    background-color: #c0c0c0;
    border: 2px solid #000;
    padding: 0;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
  }
  .title-bar {
    background-color: #000080;
    color: #fff;
    padding: 4px 8px;
    position: relative;
    font-size: 14px;
    font-weight: bold;
  }
  .title-bar-text {
    margin: 0;
  }
  .close-button {
    position: absolute;
    top: 2px;
    right: 4px;
    font-size: 12px;
    cursor: pointer;
  }
  .login-form {
    margin-top: 20px;
    padding: 16px;
  }
  .input-field {
    width: 100%;
    padding: 6px;
    margin-bottom: 10px;
    border: 1px solid #000;
  }
  .login-button {
    background-color: #c0c0c0;
    color: #000;
    border: 1px solid #000 !important;
    padding: 8px 16px;
    cursor: pointer;
    font-weight: bold;
  }
  .login-button:hover {
    background-color: #BBBBBB;
    color: #000;
  }

  .error-message{
    padding: 4px 8px;
  }
</style>
</head>
<body>
  <div class="window">
    <?php if (isset($mensajeError)) : ?>
      <div class="error-message"><?php echo htmlspecialchars($mensajeError); ?></div>
    <?php endif; ?>
    <div class="title-bar">
      <p class="title-bar-text">EfiFuelTracking Login</p>
      <span class="close-button">x</span>
    </div>
    <form class="login-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
      <input type="text" class="input-field" placeholder="Usuario" name="usuario" required>
      <input type="password" class="input-field" placeholder="Contraseña" name="pwd" required>
      <button type="submit" class="login-button">Iniciar sesión</button>
    </form>
  </div>
</body>
</html>
