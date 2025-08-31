<?php 

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $trabajo = $_POST["trabajo"];
    switch ($trabajo){
        case "Administrador":
            echo "Bienvenido señor admin";
            break;
        case "Editor":
            echo "Solo estas autorizado a editar algunos datos";
            break;
        case "Lector":
            echo "Solo puedes leer los datos";
            break;
    
    }
}
?>