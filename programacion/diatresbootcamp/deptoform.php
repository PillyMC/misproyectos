<?php
$departamentos = [
    "San Salvador"  => ["Soyapango"],
    "Morazan"       => ["Calcaopera"],
    "San Miguel"    => ["Chirilagua"],
    "Usulutan"      => ["Santa Elena"],
];

foreach ($departamentos as $departamento =>$municipios){
    echo "-$departamento <br>";

    echo "Municipios <br>";
foreach ($municipios as $municipio){
    echo "--$municipio <br> <hr>";
}
}
?>