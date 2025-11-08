<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido</title>
</head>
<body>
    <h1>Hola {{ $nameUser }}</h1>
    <p>¡Bienvenido a AGROVIDA,</p>
    <p>la plataforma que te ayudará a administrar tu finca de forma más organizada, eficiente y conectada!</p>
    <p>Tu cuenta ha sido creada correctamente. Antes de comenzar, necesitamos que confirmes tu registro para garantizar la seguridad de tu información.</p>
    <p>Por favor, haz clic en el siguiente enlace para activar tu cuenta:</p>
    <a href="http://192.168.101.11:4200/login/{{ $link }}">ACTIVA TU CUENTA PARA COMENZAR</a>
    <p>Si no creaste esta cuenta o crees que recibiste este mensaje por error, puedes ignorarlo sin problema.</p>
    <p>Una vez actives tu cuenta, podrás iniciar sesión y acceder a todas las funcionalidades de AGROVIDA, incluyendo la gestión de animales, tareas y personal de tu finca.</p>
    <p>¡Gracias por confiar en nosotros para digitalizar la gestión de tu finca!</p>
    <p>
        Atentamente,
        <br>
        El equipo de AGROVIDA
        <br>
        Simplificando la vida en el campo
    </p>
</body>
</html>