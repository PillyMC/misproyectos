<?php
if ($_SERVER["REQUEST_METHOD"]=="POST"){
    $edad=$_POST["edad"];
    if ($edad >= 18){
        echo "Eres mayor de edad";
    }else{
        echo "Eres menor de edad";
    }

}

?>