<?php
if ($_SERVER["REQUEST_METHOD"]=="POST"){
    $edad=$_POST["edad"];
    if ($edad >= 18){
        echo "Puedes votar jjejeje";
    }else{
        echo "No puedes votar estas muy pequeño";
    }

}

?>