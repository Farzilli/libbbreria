<?php
session_start();
include_once "./inc/db_config.php";
include_once "./utilities/QueryBuilder.php";

$spesaTot = 0;
$carrello = "";
$updatedProducts = 0;

//! PRODUCT ADD
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

        header("Location: carrello.php");
        exit();
    } else {
        $_SESSION["backpage"] = "carrello.php";
        header("Location: userarea.php");
        exit();
    }
}

//! PRODUCT RM
if (!empty($_GET['rm'])) {
    if (!empty($_SESSION["user_id"])) {
        $checkCartItemQuery = (new QueryBuilder())
            ->from("Carrello")
            ->select("qty")
            ->where([
                ["userId", "=", $_SESSION["user_id"]],
                ["prodId", "=", $_GET['rm']]
            ]);
        $result = $conn->query($checkCartItemQuery->build());

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $qty = $row["qty"];

            if ($qty > 1) {
                $updateQuery = (new QueryBuilder())
                    ->from("Carrello")
                    ->update(["qty" => $qty - 1])
                    ->where([
                        ["userId", "=", $_SESSION["user_id"]],
                        ["prodId", "=", $_GET['rm']]
                    ]);
                $result = $conn->query($updateQuery->build());
            } else {
                $deleteQuery = (new QueryBuilder())
                    ->from("Carrello")
                    ->where([
                        ["userId", "=", $_SESSION["user_id"]],
                        ["prodId", "=", $_GET['rm']]
                    ]);
                $result = $conn->query($deleteQuery->delete());
            }
        }

        header("Location: carrello.php");
        exit();
    } else {
        $_SESSION["backpage"] = "carrello.php";
        header("Location: userarea.php");
        exit();
    }
}

//! PRODUCT DEL
if (!empty($_GET['del'])) {
    if (!empty($_SESSION["user_id"])) {
        $deleteQuery = (new QueryBuilder())
            ->from("Carrello")
            ->where([
                ["userId", "=", $_SESSION["user_id"]],
                ["prodId", "=", $_GET['del']]
            ]);
        $result = $conn->query($deleteQuery->delete());

        header("Location: carrello.php");
        exit();
    } else {
        $_SESSION["backpage"] = "carrello.php";
        header("Location: userarea.php");
        exit();
    }
}

//! SVUOTA CART
if (isset($_GET['svuota'])) {
    if (!empty($_SESSION["user_id"])) {
        $deleteQuery = (new QueryBuilder())
            ->from("Carrello")
            ->where([
                ["userId", "=", $_SESSION["user_id"]],
            ]);
        $result = $conn->query($deleteQuery->delete());

        header("Location: carrello.php");
        exit();
    } else {
        $_SESSION["backpage"] = "carrello.php";
        header("Location: userarea.php");
        exit();
    }
}

//! ORDINA
if (isset($_GET['ordina'])) {
    if (empty($_SESSION["user_id"])) {
        $_SESSION["backpage"] = "carrello.php";
        header("Location: userarea.php");
        exit();
    } else {
        $getQtysQuery = (new QueryBuilder())
            ->select([
                'Libri.id' => '',
                'Libri.qty' => 'qty',
                'Carrello.qty' => 'cartQty'
            ])
            ->from('Carrello')
            ->join('Libri', 'Libri.id', '=', 'Carrello.prodId')
            ->where([
                ['Carrello.userId', '=', $_SESSION["user_id"]]
            ]);

        $getQtys = $conn->query($getQtysQuery->build());

        $rowsToUpdate = [];
        foreach ($getQtys as $row) {
            if ($row["cartQty"] > $row["qty"]) {
                $row["cartQty"] = $row["qty"];
                $rowsToUpdate[] = $row;
            }
        }

        foreach ($rowsToUpdate as $row) {
            $updateQtyQuery = (new QueryBuilder())
                ->update([
                    'qty' => $row["qty"]
                ])
                ->from('Carrello')
                ->where([
                    ['userId', '=', $_SESSION["user_id"]],
                    ['prodId', '=', $row['id']]
                ]);

            $conn->query($updateQtyQuery->build());
            $updatedProducts++;
        }

        if ($updatedProducts == 0) {
            $cartqtyQuery = (new QueryBuilder())
                ->from('Carrello')
                ->select([
                    'Libri.id' => '',
                    'Libri.price' => '',
                    'Libri.qty' => 'qty',
                    'Carrello.qty' => 'cartQty'
                ])
                ->join('Libri', 'Libri.id', '=', 'Carrello.prodId')
                ->where([
                    ['Carrello.userId', '=', $_SESSION["user_id"]]
                ]);
            $result = $conn->query($cartqtyQuery->build());

            foreach ($result as $row) {
                $spesaTot += $row["price"] * $row["cartQty"];

                $updateQuery = (new QueryBuilder())
                    ->from("Libri")
                    ->update(["qty" => $row["qty"] - $row["cartQty"]])
                    ->where([
                        ["id", "=", $row["id"]]
                    ]);
                $result = $conn->query($updateQuery->build());
            }

            $deleteQuery = (new QueryBuilder())
                ->from("Carrello")
                ->where([
                    ["userId", "=", $_SESSION["user_id"]],
                ]);
            $result = $conn->query($deleteQuery->delete());

            $to = $_SESSION["user_id"];
            $subject = "acquisto di libri";
            $message = "Grazie per aver ordinato libri/cd per un totale di {$spesaTot}€";
            $headers = "From: La_Libbbreria\n";
            $headers .= "CC: francesco.arzilli@iticopernico.it\r\n";

            mail($to, $subject, $message, $headers);
            header("Location: index.php");
            exit();
        }
    }
}

//! PRODUCT PRINT
if (!empty($_SESSION["user_id"])) {
    $query = (new QueryBuilder())
        ->from([
            "Carrello" => "c"
        ])
        ->select([
            "Libri.id" => "",
            "Libri.imgurl" => "",
            "Libri.title" => "",
            "Libri.price" => "",
            "c.qty" => ""
        ])->where(
            [["c.userId", "=", $_SESSION["user_id"]]],
        )
        ->join("Libri", "Libri.id", "=", "c.prodId");
    $result = $conn->query($query->build());

    foreach ($result as $row) {
        $finalPrice = $row["qty"] * $row["price"];
        $spesaTot += $finalPrice;

        $carrello .= <<<HTML
            <div class="product">
                <div id="image">
                    <img src="$row[imgurl]">
                </div>
                <div id="text">
                    <h1>$row[title]</h1>
                    <h2>quantità: $row[qty]</h2>
                    <h3>prezzo totale: $finalPrice €</h3>
                </div>
                <div id="btns">
                    <a href="?add=$row[id]" id="add_cart">+</a>
                    <a href="?rm=$row[id]" id="add_cart">-</a>
                    <a href="?del=$row[id]" id="add_cart"><i></i></a>
                </div>
            </div>
    HTML;
    }
} else {
    $_SESSION["backpage"] = "carrello.php";
    header("Location: userarea.php");
    exit();
}
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
            <?= $_SESSION['ruolo'] == 2 ?
                <<<HTML
                        <a href="admin.php"><i style="background-image: url(./icon/admin.png);"></i></a>
                    HTML :
                ""
            ?>
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

        <h1 id="productUpdateMsg"><?= $updatedProducts == 0 ? "" : ("la quantità di {$updatedProducts} prodotti è stata aggiornata!") ?></h1>

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