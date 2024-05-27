<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        * {
            border: 0;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body{
            max-width: 100vw;
            max-height: 100vh;
            overflow: hidden;
        }

        img {
            width: 100vw;
            height: 100vh;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <img src="<?= $_GET["url"] ?>" alt="">
</body>

</html>