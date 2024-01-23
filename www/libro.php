<?php
session_start();
include_once("./inc/data.php");

function getBook($arr, $id)
{
    foreach ($arr as $e) if ($id == $e["id"]) return $e;
    return null;
}

if (isset($_GET['add'])) {
    isset($_SESSION["cart"]["libri"][$_GET['add']]) ? $_SESSION["cart"]["libri"][$_GET['add']]++ : $_SESSION["cart"]["libri"][$_GET['add']] = 1;
    header("Location: libri.php");
    exit();
}

$libro = getBook($libri, isset($_GET['info']) ? $_GET['info'] : -1);

$libroInfo = $libro === null ?
    <<<HTML
        <div id="info_body">
            <h1 id="error_msg">ERROR!</h1>
        </div>
    HTML
    :
    <<<HTML
        <div id="info_body">
            <div id="image">
                <img src="$libro[img]" alt="">
            </div>
            <div id="desc">
                <div id="text">
                    <h1>$libro[title]</h1>
                    <h2>$libro[desc]</h2>
                    <h3>$libro[price]€</h3>
                </div>
                <div id="btns">
                    <a href="?add=$libro[id]" id="add_cart"><i></i></a>
                </div>
            </div>
        </div>
    HTML;
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
    <main id="libro_main"><?= $libroInfo ?></main>
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