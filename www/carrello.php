<?php
session_start();
include_once("./inc/db_config.php");

if (isset($_GET['add'])) {
    isset($_SESSION["cart"][$_GET['add']]) ? $_SESSION["cart"][$_GET['add']]++ : $_SESSION["cart"][$_GET['add']] = 1;
    header("Location: carrello.php");
    exit();
}

if (isset($_GET['rm'])) {
    if (isset($_SESSION["cart"][$_GET['rm']]) && $_SESSION["cart"][$_GET['rm']] > 1) $_SESSION["cart"][$_GET['rm']]--;
    else unset($_SESSION["cart"][$_GET['rm']]);
    header("Location: carrello.php");
    exit();
}

if (isset($_GET['del'])) {
    unset($_SESSION["cart"][$_GET['del']]);
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
    foreach ($_SESSION["cart"] as $e => $qt) {
        $sql = "SELECT * 
        FROM `Libri` 
        WHERE id = $e;
        ";
        $row = $conn->query($sql);

    if ($row->num_rows > 0) {
        $element = $row->fetch_assoc();
        $img = base64_encode($element["img"]);
        $finalPrice = $qt * $element["price"];
        $spesaTot += $finalPrice;

        if ($element !== null) $carrello .= <<<HTML
            <div class="product">
                <div id="image">
                    <img src="data:image/png;base64,$img">
                </div>
                <div id="text">
                    <h1>$element[title]</h1>
                    <h2>quantità: $qt</h2>
                    <h3>prezzo totale: $finalPrice €</h3>
                </div>
                <div id="btns">
                    <a href="?add=$element[id]" id="add_cart">+</a>
                    <a href="?rm=$element[id]" id="add_cart">-</a>
                    <a href="?del=$element[id]" id="add_cart"><i></i></a>
                </div>
            </div>
        HTML;
    }}
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