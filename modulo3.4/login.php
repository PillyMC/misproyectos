<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Gestión Educativa</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');
        body {
            background: url('fondo.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Montserrat', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 370px;
            background: rgba(255,255,255,0.92);
            border-radius: 22px;
            box-shadow:
                0 12px 36px 0 rgba(31, 38, 135, 0.25),
                0 2px 8px 0 rgba(44, 62, 80, 0.10),
                0 0.5px 1.5px 0 rgba(52, 152, 219, 0.10);
            padding: 38px 32px 32px 32px;
            text-align: center;
            backdrop-filter: blur(4px);
            position: relative;
            overflow: hidden;
        }
        .login-container::before {
            content: "";
            position: absolute;
            top: -60px;
            right: -60px;
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, #12b0fa 60%, #2980d9 100%);
            opacity: 0.13;
            border-radius: 50%;
            z-index: 0;
        }
        .login-container::after {
            content: "";
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 110px;
            height: 110px;
            background: linear-gradient(135deg, #2980d9 60%, #12b0fa 100%);
            opacity: 0.10;
            border-radius: 50%;
            z-index: 0;
        }
        .titulo {
            color: #2980d9;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 1px;
            z-index: 1;
            position: relative;
            text-shadow: 0 2px 8px rgba(18,176,250,0.10), 0 1px 0 #fff;
        }
        .logo img {
            width: 90px;
            margin: 12px 0 0 0;
            filter: drop-shadow(0 4px 12px rgba(52,152,219,0.13));
            z-index: 1;
            position: relative;
        }
        .bienvenida {
            color: #222;
            font-size: 18px;
            font-weight: 600;
            margin: 18px 0 22px 0;
            letter-spacing: 1px;
            z-index: 1;
            position: relative;
        }
        .input-text {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 16px;
            border: none;
            border-radius: 6px;
            background: #f0f6ff;
            font-size: 16px;
            color: #333;
            outline: none;
            box-shadow: 0 2px 8px rgba(18,176,250,0.07);
            transition: box-shadow 0.2s, background 0.2s;
            z-index: 1;
            position: relative;
        }
        .input-text:focus {
            background: #e6f0ff;
            box-shadow: 0 4px 16px rgba(18,176,250,0.15);
        }
        .btn-acceder {
            width: 100%;
            background: linear-gradient(90deg, #12b0fa 60%, #2980d9 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 13px 0;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
            box-shadow: 0 4px 16px rgba(18, 176, 250, 0.18);
            transform: translateY(0);
            margin-top: 8px;
            z-index: 1;
            position: relative;
        }
        .btn-acceder:hover {
            background: linear-gradient(90deg, #2980d9 60%, #12b0fa 100%);
            box-shadow: 0 8px 32px rgba(18, 176, 250, 0.28);
            transform: translateY(-2px) scale(1.03);
        }
        .error-msg {
            color: #d63031;
            background: #ffeaea;
            border-radius: 6px;
            padding: 10px 0;
            margin-bottom: 16px;
            font-weight: 600;
            font-size: 15px;
        }
        @media (max-width: 480px) {
            .login-container {
                width: 95vw;
                padding: 24px 8vw 24px 8vw;
            }
        }
    </style>

</head>
<body>
    <div class="login-container">
        <h2 class="titulo">Sistema de Gestión Educativa</h2>
        <div class="logo">
            <img id="logo-img" src="logo.png" alt="Logo" />
        </div>
        <div class="bienvenida">¡Bienvenid@!</div>
        <?php if (isset($_GET['error'])): ?>
            <div class="error-msg">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <form action="procesar_login.php" method="post" autocomplete="off">
            <input type="text" name="usuario" placeholder="Usuario o correo" class="input-text" required>
            <input type="password" name="clave" placeholder="Contraseña" class="input-text" required>
            <button type="submit" class="btn-acceder">ACCEDER</button>
        </form>
    </div>
</body>
</html>