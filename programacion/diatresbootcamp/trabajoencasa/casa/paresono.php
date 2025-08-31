 <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["numero"])) {
        $numero = intval($_POST["numero"]);

        if ($numero % 2 == 0) {
            echo "<p>El número $numero es par";
        } else {
            echo "<p>El número $numero es inpar";
        }
    }
    ?>