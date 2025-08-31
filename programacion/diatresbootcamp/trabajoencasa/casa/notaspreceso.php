<?php
if ($_SERVER["REQUEST_METHOD"]=="POST"){
    $nota=$_POST["nota"];
    if ($nota >= 9){
        echo "Sobresaliente";
    }elseif ($nota >= 7){
    echo "Eres buen estudiante";
    }elseif ($nota >= 6){
    echo "Aprobado";
}elseif ($nota <= 6){
    echo "Reprobaste manco";
}
}


?>