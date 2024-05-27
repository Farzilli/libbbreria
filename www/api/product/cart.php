<?php
include_once "../../inc/db_config.php";
include_once "../../utilities/QueryBuilder.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //! PRODUCT ADD
    if (!empty($_GET['add']) && !empty($_POST['session_id'])) {
        session_id($_POST['session_id']);
        session_start();

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

            $response = ["status" => "succes", "message" => "Product added"];
        } else {
            $response = ["status" => "error", "message" => "Logged user required"];
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    //! PRODUCT RM
    if (!empty($_GET['rm']) && !empty($_POST['session_id'])) {
        session_id($_POST['session_id']);
        session_start();
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

            $response = ["status" => "succes", "message" => "Product removed"];
        } else {
            $response = ["status" => "error", "message" => "Logged user required"];
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    //! PRODUCT DEL
    if (!empty($_GET['del']) && !empty($_POST['session_id'])) {
        session_id($_POST['session_id']);
        session_start();
        if (!empty($_SESSION["user_id"])) {
            $deleteQuery = (new QueryBuilder())
                ->from("Carrello")
                ->where([
                    ["userId", "=", $_SESSION["user_id"]],
                    ["prodId", "=", $_GET['del']]
                ]);
            $result = $conn->query($deleteQuery->delete());

            $response = ["status" => "succes", "message" => "Product delated from cart"];
        } else {
            $response = ["status" => "error", "message" => "Logged user required"];
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    //! SVUOTA CART
    if (isset($_GET['svuota']) && !empty($_POST['session_id'])) {
        session_id($_POST['session_id']);
        session_start();
        if (!empty($_SESSION["user_id"])) {
            $deleteQuery = (new QueryBuilder())
                ->from("Carrello")
                ->where([
                    ["userId", "=", $_SESSION["user_id"]],
                ]);
            $result = $conn->query($deleteQuery->delete());

            $response = ["status" => "succes", "message" => "Cart cleared"];
        } else {
            $response = ["status" => "error", "message" => "Logged user required"];
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    //! PRODUCT PRINT
    if (isset($_GET['cart']) && !empty($_POST['session_id'])) {
        session_id($_POST['session_id']);
        session_start();
        if (!empty($_SESSION["user_id"])) {
            $clearZero = (new QueryBuilder())
                ->from('Carrello')
                ->select([
                    'Carrello.userId' => '',
                    'Carrello.prodId' => ''
                ])
                ->join('Libri', 'Libri.id', '=', 'Carrello.prodId', QueryBuilder::INNER_JOIN)
                ->where([
                    ['Libri.qty', '=', '0'],
                ]);
            $result = $conn->query($clearZero->build());

            foreach ($result as $row) {
                $deleteQuery = (new QueryBuilder())
                    ->from("Carrello")
                    ->where([
                        ["userId", "=", $row["userId"]],
                        ["prodId", "=", $row["prodId"]],
                    ]);
                $result = $conn->query($deleteQuery->delete());
            }

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
                ])
                ->where(
                    [["c.userId", "=", $_SESSION["user_id"]]]
                )
                ->join("Libri", "Libri.id", "=", "c.prodId");
            $result = $conn->query($query->build());

            $data = ["data" => []];

            foreach ($result as $element) {
                $data["data"][] = [
                    'id' => $element["id"],
                    'imgurl' => $element["imgurl"],
                    'title' => $element["title"],
                    'price' => $element["price"],
                    'qty' => $element["qty"],
                ];
            }

            $response = $data;
        } else {
            $response = ["status" => "error", "message" => "Logged user required"];
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    //! ORDINA
    if (isset($_GET['ordina']) && !empty($_POST['session_id'])) {
        $updatedProducts = 0;

        session_id($_POST['session_id']);
        session_start();
        if (empty($_SESSION["user_id"])) {
            $response = [
                "status" => "error",
                "message" => "Logged user required"
            ];
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

                $response = [
                    "status" => "success",
                    "message" => "Ordered"
                ];
            } else {
                $response = [
                    "status" => "success",
                    "message" => "Qty updated"
                ];
            }
        }
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}
