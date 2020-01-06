<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">

	<title>État du service - Locomotion</title>
</head>

<body>
    <ul>
        <li class="status {{ strtolower($database) }}">
            <span>Base de données :</span>
            <span>{{ $database }}</span>
        </li>
    </ul>
</body>
</html>
