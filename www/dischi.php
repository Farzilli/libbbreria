<?php
session_start();
include_once("./inc/data.php");

if (isset($_GET['add'])) {
    isset($_SESSION["cart"]["dischi"][$_GET['add']]) ? $_SESSION["cart"]["dischi"][$_GET['add']]++ : $_SESSION["cart"]["dischi"][$_GET['add']] = 1;
    header("Location: dischi.php");
    exit();
}

$prodottiCards = "";

foreach ($dischi as $e) {
    $prodottiCards .= <<<HTML
            <div id="disco_card">
                <a href="disco.php?info=$e[id]"><img src="$e[img]" alt=""></a>
                <div id="desc">
                    <h1>$e[title]</h1>
                    <h2>$e[price]€</h2>
                </div>
                <a href="?add=$e[id]" id="add_cart"><i></i></a>
            </div>
    HTML;
}
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
    <nav id="dischi_nav">
        <div id="title">
            <h1>libbbreria</h1>
        </div>
        <div id="sections_btns">
            <a href="index.php">home</a>
            <a href="libri.php">libri</a>
            <a id="selected" href="dischi.php">cd</a>
        </div>
        <div id="user_btns">
            <a href="find.php"><i style="background-image: url(./icon/find.png);"></i></a>
            <a href="userarea.php"><i style="background-image: url(./icon/user.png);"></i></a>
            <a href="carrello.php"><i style="background-image: url(./icon/cart.png);"></i></a>
        </div>
    </nav>
    <main id="dischi_main">
        <div id="dischi"><?= $prodottiCards ?></div>
    </main>
    <footer id="dischi_footer">
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