<?php
session_start();
include_once "./inc/db_config.php";
include_once "./utilities/QueryBuilder.php";

define("UPLOAD_DIR", "uploads/");

$page = "";

if (empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

switch ($_SESSION["ruolo"]) {
    case 1:
        header("Location: index.php");
        exit();
        break;

    case 2:
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {
            if (isset($_POST['title'], $_POST['type'], $_FILES['file'], $_POST['description'], $_POST['price'], $_POST['qty'])) {
                $title = $_POST['title'];
                $type = $_POST['type'];
                $description = str_replace("'", "", $_POST['description']);
                $price = $_POST['price'];
                $qty = $_POST['qty'];

                $file = $_FILES['file'];
                $fileName = basename($file['name']);
                $uploadFilePath = UPLOAD_DIR . $fileName;

                if (file_exists($uploadFilePath)) {
                    echo "Error: File already exists.";
                } else {
                    if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                        $baseUrl = "https://" . $_SERVER['HTTP_HOST'] . "/" . "libbbreria/" . UPLOAD_DIR . $fileName;
                        $imgurl = $baseUrl;

                        $insertQuery = (new QueryBuilder())
                            ->from("Libri")
                            ->insert(["type", "imgurl", "title", "description", "price", "qty"])
                            ->values([$type, $imgurl, $title, $description, $price, $qty]);
                        $result = $conn->query($insertQuery->build());

                        header("Location: admin.php");
                        exit();
                    } else {
                        echo "Error: Unable to upload the file.";
                    }
                }
            } else {
                echo "Missing parameters";
            }
        } else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit'])) {
            if (isset($_POST['id'], $_POST['title'], $_POST['type'], $_POST['imgurl'], $_POST['description'], $_POST['price'], $_POST['qty'])) {
                $id = $_POST['id'];
                $title = $_POST['title'];
                $type = $_POST['type'];
                $imgurl = $_POST['imgurl'];
                $description = str_replace("'", "", $_POST['description']);
                $price = $_POST['price'];
                $qty = $_POST['qty'];

                $updateQuery = (new QueryBuilder())
                    ->from("Libri")
                    ->update([
                        "type" => $type,
                        "imgurl" => $imgurl,
                        "title" => $title,
                        "description" => $description,
                        "price" => $price,
                        "qty" => $qty,
                    ])
                    ->where([
                        ["id", "=", $id]
                    ]);
                $result = $conn->query($updateQuery->build());

                header("Location: admin.php");
                exit();
            } else {
                echo "Missing parameters";
            }
        } else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['rm'])) {
            if (isset($_POST['id'])) {
                $id = $_POST['id'];

                $deleteQuery = (new QueryBuilder())
                    ->from("Libri")
                    ->where([
                        ["id", "=", $id],
                    ]);
                $result = $conn->query($deleteQuery->delete());

                header("Location: admin.php");
                exit();
            } else {
                echo "Missing parameters";
            }
        } else {
            $booksQuery = (new QueryBuilder())
                ->from("Libri")
                ->select("*");
            $result = $conn->query($booksQuery->build());

            if ($result->num_rows > 0) {
                $page .= <<<HTML
                    <h1>Admin Book Modification</h1>
                    HTML;

                foreach ($result as $book) {
                    $page .= <<<HTML
                        <form action="" method="post" class="product_form">
                            <div class="input">
                                <label for="id">Id:</label>
                                <input type="number" name="id" readonly value="{$book['id']}">
                            </div>
                            <div class="input">
                                <label for="title">Title:</label>
                                <input type="text" name="title" value="{$book['title']}">
                            </div>
                            <div class="input">
                                <label for="type">Type:</label>
                                <input type="text" name="type" value="{$book['type']}">
                            </div>
                            <div class="input">
                                <label for="imgurl">Image URL:</label>
                                <input type="text" name="imgurl" value="{$book['imgurl']}">
                            </div>
                            <div class="input">
                                <label for="description">Description:</label>
                                <textarea name="description">{$book['description']}</textarea>
                            </div>
                            <div class="input">
                                <label for="price">Price:</label>
                                <input type="number" name="price" value="{$book['price']}">
                            </div>
                            <div class="input">
                                <label for="qty">Quantity:</label>
                                <input type="number" name="qty" value="{$book['qty']}">
                            </div>
                            <div class="input">
                                <input type="submit" name="edit" value="Save" class="save">
                                <input type="submit" name="rm" value="Rm" class="rm">
                            </div>
                        </form>
                    HTML;
                }
            } else {
                $page .= '<p>No books found.</p>';
            }
        }
        break;

    default:
        header("Location: index.php");
        exit();
        break;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Book Modification</title>
    <link rel="stylesheet" href="./style/style.css">
    <link rel="shortcut icon" href="./icon/icon.png" type="image/x-icon">
</head>

<body>
    <nav id="admin_nav">
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
                        <a id="selected" href="admin.php"><i style="background-image: url(./icon/admin.png);"></i></a>
                    HTML :
                ""
            ?>
            <a href="find.php"><i style="background-image: url(./icon/find.png);"></i></a>
            <a href="userarea.php"><i style="background-image: url(./icon/user.png);"></i></a>
            <a href="carrello.php"><i style="background-image: url(./icon/cart.png);"></i></a>
        </div>
    </nav>
    <main id="admin_main">
        <button id="addButton">Add Book</button>
        <div id="popup" class="popup">
            <h1>Add book</h1>
            <form action="" method="post" class="product_form" enctype="multipart/form-data">
                <div class="input">
                    <label for="title">Title:</label>
                    <input type="text" name="title" required><br>
                </div>
                <div class="input">
                    <label for="type">Type:</label>
                    <input type="text" name="type" required><br>
                </div>
                <div class="input">
                    <label for="file">ImgFile:</label>
                    <input type="file" id="file" name="file" required>
                </div>
                <div class="input">
                    <label for="description">Description:</label>
                    <textarea name="description" required></textarea><br>
                </div>
                <div class="input">
                    <label for="price">Price:</label>
                    <input type="number" name="price" value="0" required><br>
                </div>
                <div class="input">
                    <label for="qty">Quantity:</label>
                    <input type="number" name="qty" value="0" required><br>
                </div>
                <input type="submit" name="add" value="Add">
            </form>
        </div>

        <div id="itemlist">
            <?= $page ?>
        </div>
    </main>
    <footer id="admin_footer">
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

<script>
    const popup = document.getElementById("popup");
    const addButton = document.getElementById("addButton");

    addButton.onclick = () => popup.style.display = "block";
    window.addEventListener("click", (event) => {
        const isClickInsidePopup = popup.contains(event.target);
        const isClickOnAddButton = event.target === addButton;
        if (!isClickInsidePopup && !isClickOnAddButton) popup.style.display = "none";
    });
</script>

</html>
