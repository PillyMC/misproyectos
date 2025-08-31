
<?php
$productos = [
"Maiz" => "10 dolares",
"Frijoles" => "4 dolares",
"Harina" => "5 coras",
"Plumon 90" => "48 dolares",
];
    foreach($productos as $producto => $precios){
        echo "El precio del $producto es: $precios <br><hr>";
    }
    
?>