<?php
// Aqui se conecta la base de datos de Railway
$host     = getenv("MYSQLHOST");
$usuario  = getenv("MYSQLUSER");
$password = getenv("MYSQLPASSWORD");
$base     = getenv("MYSQLDATABASE");
$port     = (int)getenv("MYSQLPORT");

$conn = new mysqli($host, $usuario, $password, $base, $port);

if ($conn->connect_error) {
  die("No se pudo conectar: " . $conn->connect_error);
}
