<?php
$edad = 14;
if($edad >=18){
    echo "puedes votar amor";
}elseif ($edad>=13){
    echo "Eres un adolescente, no puede votar";
}elseif ($edad >= 0){
    echo "Eres un bebe";
}else 
    echo "Tu edad no es valida.";

?>