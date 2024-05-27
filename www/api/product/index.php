<?php
require_once('../../inc/db_config.php');
require_once('../../utilities/QueryBuilder.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = (new QueryBuilder())
        ->from([
            "Libri" => "l"
        ])
        ->select([
            "l.id" => "",
            "l.type" => "",
            "l.imgurl" => "",
            "l.title" => "",
            "l.description" => "ds",
            "l.price" => "",
            "l.qty" => ""
        ])->where(
            [["l.qty", ">", 0]],
        );

    //! FIND FOR ID
    if (!empty($_GET["id"]))
        $query->where(
            [["l.id", "=", $_GET["id"]]],
        );

    //! FIND FOR TYPE
    if (!empty($_GET["type"]))
        $query->where(
            [["l.type", "=", $_GET["type"]]],
        );

    //! GENERIC SEARCH
    if (!empty($_GET["search"]))
        $query->where(
            [
                ["l.title", "like", "%{$_GET['search']}%"],
                ["l.description", "like", "%{$_GET['search']}%"]
            ],
            QueryBuilder::AND,
            QueryBuilder::OR,
        );

    $response = $conn->query($query->build());

    $data = ["data" => []];

    foreach ($response as $element) {
        $data["data"][] = [
            'id' => $element["id"],
            'type' => $element["type"],
            'imgurl' => $element["imgurl"],
            'title' => $element["title"],
            'description' => $element["ds"],
            'price' => $element["price"],
            'qty' => $element["qty"],
        ];
    }

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data, JSON_PRETTY_PRINT);
}
