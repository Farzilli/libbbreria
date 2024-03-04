<?php
session_start();
include_once("./inc/db_config.php");

if (isset($_GET['add'])) {
    isset($_SESSION["cart"][$_GET['add']]) ? $_SESSION["cart"][$_GET['add']]++ : $_SESSION["cart"][$_GET['add']] = 1;
    header("Location: index.php");
    exit();
}

$element = null;

$info = $_GET['info'];

$sql = "SELECT * 
        FROM `Libri` 
        WHERE id = $info;
        ";
$row = $conn->query($sql);

if ($row->num_rows > 0) {
    $e = $row->fetch_assoc();
    $img = base64_encode($e["img"]);
    $element .= <<<HTML
        <div id="info_body">
            <div id="image">
                <img src="data:image/png;base64,$img" alt="">
            </div>
            <div id="desc">
                <div id="text">
                    <h1>$e[title]</h1>
                    <h2>$e[description]</h2>
                    <h3>$e[price]€</h3>
                </div>
                <div id="btns">
                    <a href="?add=$e[id]" id="add_cart"><i></i></a>
                </div>
            </div>
        </div>
    HTML;
}

$elementInfo = $element === null ?
    <<<HTML
        <div id="info_body">
            <h1 id="error_msg">ERROR!</h1>
        </div>
    HTML :
    $element;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libbbreria</title>
    <link rel="stylesheet" href="./style/style.css">
</head>

<body>
    <nav id="libro_nav">
        <div id="title">
            <h1>libbbreria</h1>
        </div>
        <div id="sections_btns">
            <a href="index.php">home</a>
            <a href="libri.php">libri</a>
            <a href="dischi.php">cd</a>
        </div>
        <div id="user_btns">
            <a href="find.php"><i style="background-image: url(./icon/find.png);"></i></a>
            <a href="userarea.php"><i style="background-image: url(./icon/user.png);"></i></a>
            <a href="carrello.php"><i style="background-image: url(./icon/cart.png);"></i></a>
        </div>
    </nav>
    <main id="libro_main"><?= $elementInfo ?></main>
    <footer id="libro_footer">
        <div class="text">
            <p>Libbbreria</p>
        </div>
        <div class="social">
            <a href="https://www.instagram.com/"><img src="./icon/instagram.png" alt=""></a>
            <a href="https://www.twitter.com/"><img src="./icon/twitter.png" alt=""></a>
            <a href="https://www.tiktok.com/"><img src="./icon/tik-tok.png" alt=""></a>
        </div>
    </footer>
</body>

</html>