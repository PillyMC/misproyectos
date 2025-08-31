<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operaciones</title>
</head>
<body>
    <center><h2>Insertar Números</h2></center>
    <center>
    <form action="" method="POST">
       Numero 1: <input type="number" name="n1" id="n1">
       Numero 2: <input type="number" name="n2" id="n2">
            <br><br>
       operacion
       <select name="operacion">
        <option value="">Elige una operacion matematica</option>
        <option value="1">Suma</option>
        <option value="2">Resta</option>
        <option value="3">Multiplicación</option>
        <option value="4">División</option>
       </select>
        <input type="submit" value= "Calcular">
    </form>
    </center>
</body>
</html>
<?php
    function operacionesBasicas($n1, $n2, $operacion){
        $resultado=0;
        if($operacion==1){
            $resultado=$n1+$n2;
        }
        if($operacion==2){
            $resultado=$n1-$n2;
        }
        if($operacion==3){
            $resultado=$n1*$n2;
        }
         if($operacion==4){
            if($n2==0)
            return "No se puede dividir por cero";
            $resultado=$n1/$n2;
        }
        
      return $resultado;
    }
    if($_SERVER['REQUEST_METHOD']== "POST"){
        $numero1=$_POST['n1'];
        $numero2=$_POST['n2'];
        $operacion=$_POST['operacion'];
 echo  operacionesBasicas($numero1,$numero2,$operacion);
    }


 

?>