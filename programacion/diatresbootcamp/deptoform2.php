<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departamentos y Municipios</title>
</head>
<body>
<h2>Seleccione un Departamento</h2>    

<?php

$departamentos = [
"San salvador" => ["Soyapango"],
"Morazán" => ["Chirilagua"],
"Usulután" => ["Santa elena", "Usulutan", "Santa Maria", "El espino", "Ereguayquin"],
"La Unión" => ["Intipuca", "Paloros", "San Jose", "Santa Rosa de Lima"]
];
?>

<form method="POST">
    <label for="departamento">Selecciona un departamento mi amor:</label>
    <select name="departamento" id="departamento" required>
    <option value="">-- Seleccione uno --</option>

<?php
    foreach($departamentos as $nombreDepto => $municipios){
        echo "<option value =\"$nombreDepto\">$nombreDepto</option>";
    }
    
?>
    </select>
    <br><br>
    <button type="submit">Ver municipios </button> 


</form>
<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["departamento"])) {
    $deptoSeleccionado = $_POST["departamento"];
    if (array_key_exists($deptoSeleccionado, $departamentos)) {
        echo "<h3>Municipios de $deptoSeleccionado:</h3>";
        echo "<ul>";
        foreach ($departamentos[$deptoSeleccionado] as $municipio) {
            echo "<li>$municipio</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Departamento no válido.</p>";
    }
}
?>

</body>
</html>