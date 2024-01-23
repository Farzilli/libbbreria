<?php
include_once("./inc/data.php");

$str = "";

if (isset($_GET['find'])) {
    $str = strtolower(trim($_GET['str']));
    setcookie("lastFind", $str, time() + (86400 * 30), "/");
}


$prodottiCards = "";
foreach ($libri as $e) {
    if ($str === "" || $str === "libri" || $str === "libro" || stripos($e["title"], $str) !== false || stripos($e["desc"], $str) !== false)
        $prodottiCards .= <<<HTML
            <div id="card">
                <a href="libro.php?info=$e[id]"><img src="$e[img]" alt=""></a>
                <div id="desc">
                    <h1>$e[title]</h1>
                    <h2>$e[price]€</h2>
                </div>
                <a href="libro.php?info=$e[id]" id="add_cart"><i></i></a>
            </div>
        HTML;
}

foreach ($dischi as $e) {
    if ($str === "" || $str === "cd" || stripos($e["title"], $str) !== false || stripos($e["desc"], $str) !== false)
        $prodottiCards .= <<<HTML
            <div id="card">
                <a href="disco.php?info=$e[id]"><img src="$e[img]" alt=""></a>
                <div id="desc">
                    <h1>$e[title]</h1>
                    <h2>$e[price]€</h2>
                </div>
                <a href="disco.php?info=$e[id]" id="add_cart"><i></i></a>
            </div>
        HTML;
}

if ($prodottiCards === "") {
    $prodottiCards .= <<<HTML
            <h1>"$str" non ha portato a risultati!</h1>
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
    <nav id="find_nav">
        <div id="title">
            <h1>libbbreria</h1>
        </div>
        <div id="sections_btns">
            <a href="index.php">home</a>
            <a href="libri.php">libri</a>
            <a href="dischi.php">cd</a>
        </div>
        <div id="user_btns">
            <a id="selected" href="find.php"><i style="background-image: url(./icon/find.png);"></i></a>
            <a href="userarea.php"><i style="background-image: url(./icon/user.png);"></i></a>
            <a href="carrello.php"><i style="background-image: url(./icon/cart.png);"></i></a>
        </div>
    </nav>
    <main id="find_main">
        <form action="" method="get">
            <input type="text" name="str" id="str" value="<?= isset($_COOKIE["lastFind"]) ? $_COOKIE["lastFind"] : "" ?>" required placeholder="cerca qualcosa">
            <input type="submit" value="find" name="find">
        </form>
        <div id="prodotti_list">
            <?= $prodottiCards ?>
        </div>
    </main>
    <footer id="find_footer">
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

<script src="./script/index.js"></script>

</html>