<?php
session_start();
include_once("./inc/data.php");

function getBook($arr, $id)
{
    foreach ($arr as $e) if ($id == $e["id"]) return $e;
    return null;
}

if (isset($_GET['addlibro'])) {
    isset($_SESSION["cart"]["libri"][$_GET['addlibro']]) ? $_SESSION["cart"]["libri"][$_GET['addlibro']]++ : $_SESSION["cart"]["libri"][$_GET['addlibro']] = 1;
    header("Location: carrello.php");
    exit();
}

if (isset($_GET['rmlibro'])) {
    if (isset($_SESSION["cart"]["libri"][$_GET['rmlibro']]) && $_SESSION["cart"]["libri"][$_GET['rmlibro']] > 1) $_SESSION["cart"]["libri"][$_GET['rmlibro']]--;
    else unset($_SESSION["cart"]["libri"][$_GET['rmlibro']]);
    header("Location: carrello.php");
    exit();
}

if (isset($_GET['dellibro'])) {
    unset($_SESSION["cart"]["libri"][$_GET['dellibro']]);
    header("Location: carrello.php");
    exit();
}

if (isset($_GET['adddisco'])) {
    isset($_SESSION["cart"]["dischi"][$_GET['adddisco']]) ? $_SESSION["cart"]["dischi"][$_GET['adddisco']]++ : $_SESSION["cart"]["dischi"][$_GET['adddisco']] = 1;
    header("Location: carrello.php");
    exit();
}

if (isset($_GET['rmdisco'])) {
    if (isset($_SESSION["cart"]["dischi"][$_GET['rmdisco']]) && $_SESSION["cart"]["dischi"][$_GET['rmdisco']] > 1) $_SESSION["cart"]["dischi"][$_GET['rmdisco']]--;
    else unset($_SESSION["cart"]["dischi"][$_GET['rmdisco']]);
    header("Location: carrello.php");
    exit();
}

if (isset($_GET['deldisco'])) {
    unset($_SESSION["cart"]["dischi"][$_GET['deldisco']]);
    header("Location: carrello.php");
    exit();
}

if (isset($_GET['svuota'])) {
    unset($_SESSION["cart"]);
    header("Location: carrello.php");
    exit();
}

$spesaTot = 0;

$carrello = "";
if (isset($_SESSION["cart"])) {
    foreach ($_SESSION["cart"]["libri"] as $e => $qt) {
        $libro = getBook($libri, $e);
        $finalPrice = $qt * $libro["price"];
        $spesaTot += $finalPrice;

        if ($libro !== null) $carrello .= <<<HTML
            <div class="product">
                <div id="image">
                    <img src="$libro[img]">
                </div>
                <div id="text">
                    <h1>$libro[title]</h1>
                    <h2>quantità: $qt</h2>
                    <h3>prezzo totale: $finalPrice €</h3>
                </div>
                <div id="btns">
                    <a href="?addlibro=$libro[id]" id="add_cart">+</a>
                    <a href="?rmlibro=$libro[id]" id="add_cart">-</a>
                    <a href="?dellibro=$libro[id]" id="add_cart"><i></i></a>
                </div>
            </div>
        HTML;
    }

    foreach ($_SESSION["cart"]["dischi"] as $e => $qt) {
        $disco = getBook($dischi, $e);
        $finalPrice = $qt * $disco["price"];
        $spesaTot += $finalPrice;

        if ($disco !== null) $carrello .= <<<HTML
            <div class="product">
                <div id="image">
                    <img src="$disco[img]">
                </div>
                <div id="text">
                    <h1>$disco[title]</h1>
                    <h2>quantità: $qt</h2>
                    <h3>prezzo totale: $finalPrice €</h3>
                </div>
                <div id="btns">
                    <a href="?adddisco=$disco[id]" id="add_cart">+</a>
                    <a href="?rmdisco=$disco[id]" id="add_cart">-</a>
                    <a href="?deldisco=$disco[id]" id="add_cart"><i></i></a>
                </div>
            </div>
        HTML;
    }
}

if (isset($_GET['ordina'])) {
    if (!isset($_SESSION["email"]) && !isset($_SESSION["password"])) {
        $_SESSION["backpage"] = "carrello.php";
        header("Location: userarea.php");
        exit();
    } else {
        $to = $_SESSION["email"];
        $subject = "acquisto di libri";
        $message = "Grazie per aver ordinato libri/cd per un totale di " . $spesaTot . "€";
        $headers = "From: La_Libbreria\n";
        $headers .= "CC: francesco.arzilli@iticopernico.it\r\n";

        mail($to, $subject, $message, $headers);
        unset($_SESSION["cart"]);
        header("Location: index.php");
        exit();
    }
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
    <nav id="carrello_nav">
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
            <a id="selected" href="carrello.php"><i style="background-image: url(./icon/cart.png);"></i></a>
        </div>
    </nav>
    <main id="carrello_main">
        <?= $spesaTot > 0 ?
            <<<HTML
                <div id="cart_btns">
                    <h1>spesa totale: {$spesaTot}€</h1>
                    <a href="?ordina">ordina</a>
                    <a href="?svuota">svuota carrello</a>
                </div>
            HTML :
            <<<HTML
                <div id="cart_empty">
                    <h1>il tuo carrello è vuoto!</h1>
                </div>
            HTML;
        ?>
        <div id="carrello"><?= $carrello ?></div>
    </main>
    <footer id="carrello_footer">
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