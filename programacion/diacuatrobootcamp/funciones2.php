<?php
//Funciones de mi sistema.
$nombre = "Keiry Azucena Berrios Granados";
$nota1 = 9;
$nota2 = 8;
$nota3 = 8;

$resultado = ($nota1 + $nota2 + $nota3)/3;
echo "Fecha: " .date("D/M/Y"),"<br><hr>";
echo "Nombre del estudiante:" .strtoupper($nombre),"<br>";
if (strlen($nombre)>10){
    echo "<b>Tu nombre es demasiado largo. <br></b>";
}
echo "Tu nota es:" .number_format($resultado),"<br>";


?>