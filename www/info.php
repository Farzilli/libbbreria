<?php
session_start();
include_once("./inc/db_config.php");
include_once("./utilities/QueryBuilder.php");

if (!empty($_GET['add'])) {
    if (!empty($_SESSION["user_id"])) {
        $checkCartItemQuery = (new QueryBuilder())
            ->from(
                ["Carrello" => "c"]
            )
            ->select([
                "c.qty" => "",
            ])
            ->where([
                ["c.userId", "=", $_SESSION["user_id"]],
                ["c.prodId", "=", $_GET['add']]
            ]);
        $result = $conn->query($checkCartItemQuery->build());

        if ($result->num_rows > 0) {
            $qty = $result->fetch_assoc()["qty"];
            $updateQuery = (new QueryBuilder())
                ->from(
                    ["Carrello" => "c"]
                )
                ->update(["qty" => $qty + 1])
                ->where([
                    ["c.userId", "=", $_SESSION["user_id"]],
                    ["c.prodId", "=", $_GET['add']]
                ]);
            $result = $conn->query($updateQuery->build());
        } else {
            $insertQuery = (new QueryBuilder())
                ->from("Carrello")
                ->insert(["userId", "prodId", "qty"])
                ->values([$_SESSION["user_id"], $_GET['add'], 1]);
            $result = $conn->query($insertQuery->build());
        }

        header("Location: index.php");
        exit();
    } else {
        $_SESSION["backpage"] = "index.php";
        header("Location: userarea.php");
        exit();
    }
}

$element = null;

$info = $_GET['info'];

$query = (new QueryBuilder())
    ->from([
        "Libri" => "l"
    ])
    ->select([
        "l.id" => "",
        "l.imgurl" => "",
        "l.title" => "",
        "l.description" => "ds",
        "l.price" => ""
    ])->where(
        [["l.id", "=", $info]],
    );
$row = $conn->query($query->build());

if ($row->num_rows > 0) {
    $e = $row->fetch_assoc();
    $element .= <<<HTML
        <div id="info_body">
            <div id="image">
                <img src="$e[imgurl]" alt="">
            </div>
            <div id="desc">
                <div id="text">
                    <h1>$e[title]</h1>
                    <h2>$e[ds]</h2>
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
    <link rel="shortcut icon" href="./icon/icon.png" type="image/x-icon">
</head>

<body>
    <nav id="info_nav">
        <div id="title">
            <h1>libbbreria</h1>
        </div>
        <div id="sections_btns">
            <a href="index.php">home</a>
            <a href="libri.php">libri</a>
            <a href="dischi.php">cd</a>
        </div>
        <div id="user_btns">
            <?= $_SESSION['ruolo'] == 2 ?
                <<<HTML
                        <a href="admin.php"><i style="background-image: url(./icon/admin.png);"></i></a>
                    HTML :
                ""
            ?>
            <a href="find.php"><i style="background-image: url(./icon/find.png);"></i></a>
            <a href="userarea.php"><i style="background-image: url(./icon/user.png);"></i></a>
            <a href="carrello.php"><i style="background-image: url(./icon/cart.png);"></i></a>
        </div>
    </nav>
    <main id="info"><?= $elementInfo ?></main>
    <footer id="info_footer">
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