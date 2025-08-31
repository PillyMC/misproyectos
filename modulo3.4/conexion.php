<?php
$host = "localhost";
$port = "5432";
$dbname = "base_de_datos";
$user = "postgres";
$password = "613900";

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";

$conexion = pg_connect($conn_string);

if(!$conexion){
    die("Error al conectar a la base de datos: " . pg_last_error());
} else {
}
?>