<?php 
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $dia = $_POST["dia"];
    switch ($dia){
        case "lunes":
            echo "ni levantarme quiero";
            break;
        case "martes":
            echo "apenas abro los ojos";
            break;
        case "miercoles":
            echo "casi me levanto";
            break;
        case "jueves":
            echo "es de amigos";
            break;
        case "viernes":
            echo "y el cuerpo lo sabe";
            break;
        case "sabado":
            echo "sabadito de familia";
            break;
        case "domingo":
            echo "vamos a la iglesia";
            break;
            
    }
}
?>