<?php
include_once("./inc/data.php");

$offerteCards = "";
foreach ($offerte as $e) {
    $offerteCards .= <<<HTML
        <div class="offerta">
            <div id="image">
                <img src="$e[img]" alt="">
            </div>
            <div id="desc">
                <h1>$e[title]</h1>
                <h2>$e[desc]</h2>
                <a href="$e[link]">vai all offerta!</a>
            </div>
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
    <nav id="index_nav">
        <div id="title">
            <h1>libbbreria</h1>
        </div>
        <div id="sections_btns">
            <a id="selected" href="index.php">home</a>
            <a href="libri.php">libri</a>
            <a href="dischi.php">cd</a>
        </div>
        <div id="user_btns">
            <a href="find.php"><i style="background-image: url(./icon/find.png);"></i></a>
            <a href="userarea.php"><i style="background-image: url(./icon/user.png);"></i></a>
            <a href="carrello.php"><i style="background-image: url(./icon/cart.png);"></i></a>
        </div>
    </nav>
    <main id="index_main">
        <div id="offerte"><?= $offerteCards ?></div>
    </main>
    <footer id="index_footer">
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