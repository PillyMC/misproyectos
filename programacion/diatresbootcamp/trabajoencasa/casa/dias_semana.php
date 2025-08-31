<?php
if ($_SERVER["REQUEST_METHOD"]== "POST"){
    $dia = $_POST["dia"];
switch ($dia) {
    case "1":
        echo "Feliz lunes";
        break;
    case "2":
        echo "Feliz martes";
        break;
    case "3":
        echo "Feliz miercoles";
        break;
    case "4":
        echo "Feliz jueves";
        break;
    case "5":
        echo "Apenas es vierneeeeeeeeees";
        break;
    case "6":
        echo "Sabado de scouts";
        break;
    case "7":
        echo "Dia de descanso";
        break;
    default:
        echo "Ya no hay dias";
    }
}
?>
