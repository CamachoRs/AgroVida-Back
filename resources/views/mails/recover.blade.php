<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido</title>
</head>
<body>
    <h1>Hola {{ $nameUser }}</h1>
    <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en AGROVIDA.</p>
    <p>Tu nueva contraseña temporal es:</p>
    <p>{{ $password }}</p>
    <p>Por motivos de seguridad, te recomendamos que cambies esta contraseña tan pronto inicies sesión. Puedes hacerlo desde la sección "Mi perfil" > "Cambiar contraseña" dentro de la plataforma.</p>
    <p>Si tú no solicitaste este cambio, por favor ignora este mensaje. Nadie podrá acceder a tu cuenta sin esta contraseña.</p>
    <p>
        Atentamente,
        <br>
        El equipo de AGROVIDA
        <br>
        Simplificando la vida en el campo
    </p>
</body>
</html>