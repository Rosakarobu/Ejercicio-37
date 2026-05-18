<?php
// Inicia la sesion
session_start();
require_once "db.php";

// Se crea la tabla automaticamente si no existe
$conn->query("CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL
)");

$error   = "";
$mensaje = "";

// Registra un usuario nuevo
if (isset($_POST["accion"]) && $_POST["accion"] === "registrar") {
  $nombres    = trim($_POST["nombres"]);
  $apellidos  = trim($_POST["apellidos"]);
  $correo     = trim($_POST["correo"]);
  $contrasena = password_hash(trim($_POST["contrasena"]), PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO usuarios (nombres, apellidos, correo, contrasena) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $nombres, $apellidos, $correo, $contrasena);
  if ($stmt->execute()) {
    $mensaje = "Usuario registrado. Ya puedes iniciar sesion.";
  } else {
    $error = "Ese correo ya esta registrado.";
  }
}

// Se procesa el login
if (isset($_POST["accion"]) && $_POST["accion"] === "login") {
  $correo     = trim($_POST["correo"]);
  $contrasena = trim($_POST["contrasena"]);

  $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
  $stmt->bind_param("s", $correo);
  $stmt->execute();
  $res     = $stmt->get_result();
  $usuario = $res->fetch_assoc();

  if ($usuario && password_verify($contrasena, $usuario["contrasena"])) {
    $_SESSION["usuario_id"]     = $usuario["id"];
    $_SESSION["usuario_nombre"] = $usuario["nombres"];
  } else {
    $error = "Correo o contrasena incorrectos.";
  }
}

// Cerrar la sesion
if (isset($_GET["logout"])) {
  session_destroy();
  header("Location: index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ejercicio 37 - Rosa Karina Rosas Burgueño</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Georgia, serif; background-color: #fff0f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 2rem; }
    .tarjeta { background: #fbeaf0; border: 1px solid #f4c0d1; border-radius: 16px; padding: 2.5rem 2rem; width: 100%; max-width: 440px; }
    h1 { font-size: 22px; color: #72243e; margin-bottom: 0.25rem; }
    p.subtitulo { font-size: 13px; color: #993556; margin-bottom: 1.75rem; }
    h2 { font-size: 16px; color: #72243e; margin-bottom: 1rem; }
    label { display: block; font-size: 13px; font-weight: bold; color: #72243e; margin-bottom: 6px; }
    input { width: 100%; padding: 10px 14px; font-size: 15px; border: 1px solid #f4c0d1; border-radius: 8px; background: #fff6f9; color: #4b1528; outline: none; margin-bottom: 1rem; }
    input:focus { border-color: #d4537e; }
    button { width: 100%; padding: 11px; font-size: 15px; font-weight: bold; background: #f4c0d1; color: #4b1528; border: none; border-radius: 8px; cursor: pointer; margin-bottom: 0.75rem; }
    button:hover { background: #ed93b1; }
    .error { font-size: 13px; color: #72243e; background: #ffd6e0; border: 1px solid #f4c0d1; border-radius: 8px; padding: 0.6rem 1rem; margin-bottom: 1rem; }
    .mensaje { font-size: 13px; color: #72243e; background: #fff0f5; border: 1px solid #f4c0d1; border-radius: 8px; padding: 0.6rem 1rem; margin-bottom: 1rem; }
    .separador { text-align: center; font-size: 12px; color: #993556; margin: 1rem 0; }
    .seccion-exclusiva { background: #ffd6e0; border: 1px solid #f4c0d1; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
    .seccion-exclusiva p { font-size: 14px; color: #72243e; }
  </style>
</head>
<body>
  <div class="tarjeta">

    <?php if (isset($_SESSION["usuario_id"])): ?>

      <!-- Exclusiva solo si hay sesion activa -->
      <h1>Hola, <?= htmlspecialchars($_SESSION["usuario_nombre"]) ?></h1>
      <p class="subtitulo">Has iniciado sesion correctamente</p>

      <div class="seccion-exclusiva">
        <p>Esta seccion es exclusiva para usuarios que han iniciado sesion.</p>
        <p style="margin-top:0.5rem;">Solo puedes verla si tu correo y contrasena coinciden con los de la base de datos.</p>
      </div>

      <a href="?logout=1"><button type="button">Cerrar sesion</button></a>

    <?php else: ?>

      <h1>Ejercicio 37 — Sesiones</h1>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
      <?php endif; ?>

      <!-- Formulario de login -->
      <h2>Iniciar sesion</h2>
      <form method="POST" action="">
        <input type="hidden" name="accion" value="login" />
        <label>Correo electronico</label>
        <input type="email" name="correo" placeholder="tu@correo.com" required />
        <label>Contrasena</label>
        <input type="password" name="contrasena" required />
        <button type="submit">Entrar</button>
      </form>

      <div class="separador">— o registrate —</div>

      <!-- Formulario de registro -->
      <h2>Registrarse</h2>
      <form method="POST" action="">
        <input type="hidden" name="accion" value="registrar" />
        <label>Nombre(s)</label>
        <input type="text" name="nombres" placeholder="Ej: Maria" required />
        <label>Apellido(s)</label>
        <input type="text" name="apellidos" placeholder="Ej: Lopez" required />
        <label>Correo electronico</label>
        <input type="email" name="correo" placeholder="tu@correo.com" required />
        <label>Contrasena</label>
        <input type="password" name="contrasena" required />
        <button type="submit">Registrarse</button>
      </form>

    <?php endif; ?>

  </div>
</body>
</html>
