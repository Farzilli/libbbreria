<?php
ob_start(); //! non rimuovere, si scassa l' header()

//USER ADMIN:
// email = francesco.arzilli@iticopernico.it
// password = abc

//USER:
// email = daniele.boggian@iticopernico.it
// password = 1234
?>

<?php
session_start();
include_once "./inc/db_config.php";
include_once "./utilities/QueryBuilder.php";

$userError = "";
$userRuolo = $_SESSION["ruolo"] == 2 ? "Admin" : "User";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //! LOGIN
    if (!empty($_POST["login"])) {
        $email = $_POST["email"];
        $password = $_POST["password"];

        if (
            !empty($email) &&
            !empty($password)
        ) {
            $query = (new QueryBuilder())
                ->from("Utenti")
                ->select("*")
                ->where([
                    ["email", "=", $email],
                ]);
            $result = $conn->query($query->build());

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user["password"])) {
                    //? Logged in
                    $_SESSION['user_id'] = $user['email'];
                    $_SESSION['username'] = $user['nome'];
                    $_SESSION['ruolo'] = $user['ruolo'];

                    !empty($_SESSION["backpage"]) ? header("Location: carrello.php") : header("Location: index.php");
                    unset($_SESSION["backpage"]);
                } else {
                    //? password error
                    $userError = "Invalid password!";
                }
            }   else {
                //? email error
                $userError = "Invalid email!";
            }
        } else {
            //? Email or password missing
            $userError = "Email or password missing!";
        }
    }

    //! REGISTER
    if (!empty($_POST["register"])) {
        $nome = $_POST["name"];
        $cognome = $_POST["surname"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $domicilio = $_POST["domicilio"];

        if (
            !empty($nome) &&
            !empty($cognome) &&
            !empty($email) &&
            !empty($password) &&
            !empty($domicilio)
        ) {
            $checkUserQuery = (new QueryBuilder())
                ->from(["Utenti" => "u"])
                ->select("")
                ->where([
                    ["u.email", "=", $email]
                ])
                ->count("*", "qty");

            $result = $conn->query($checkUserQuery->build());
            $row = $result->fetch_assoc();
            $userExists = $row["qty"] > 0;

            if (!$userExists) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $insertQuery = (new QueryBuilder())
                    ->from("Utenti")
                    ->insert(["nome", "cognome", "email", "password", "domicilio"])
                    ->values([$nome, $cognome, $email, $hashed_password, $domicilio]);

                if ($conn->query($insertQuery->build()) === TRUE) {
                    $_SESSION['user_id'] = $email;
                    $_SESSION['username'] = $nome;
                    $_SESSION['ruolo'] = 1;

                    !empty($_SESSION["backpage"]) ? header("Location:carrello.php") : header("Location:index.php");
                    unset($_SESSION["backpage"]);
                } else {
                    $userError = "Invalid data for registration!";
                }
            } else {
                $userError = "User with this email already exists!";
            }
        } else {
            $userError = "Fields missing!";
        }
    }

    //! LOGOUT
    if (!empty($_POST['logout'])) {
        session_destroy();
        header("Location:userarea.php");
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
    <link rel="shortcut icon" href="./icon/icon.png" type="image/x-icon">
</head>

<body>
    <nav id="userarea_nav">
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
            <a id="selected" href="userarea.php"><i style="background-image: url(./icon/user.png);"></i></a>
            <a href="carrello.php"><i style="background-image: url(./icon/cart.png);"></i></a>
        </div>
    </nav>
    <main id="userarea_main">
        <?= !empty($_SESSION["user_id"]) ?
            <<<HTML
                <div id="logout">
                    <h1>$userRuolo: $_SESSION[username]</h1>
                    <form action="" method="post">
                        <input type="submit" value="logout" name="logout">
                    </form>
                </div>
            HTML :
            <<<HTML
                <div id="form">
                    <form action="" method="post" id="login" style="display: flex;">
                        <h1>login</h1>
                        <h2>$userError</h2>
                        <input type="email" name="email" class="email" required placeholder="nome@esempio.com">
                        <input type="password" name="password" class="password" required placeholder="P@s5u0rd">
                        <input type="submit" class="login" value="login" name="login">
                        <button class="changeform">non hai un account?</button>
                    </form>

                    <form action="" method="post" id="register" style="display: none;">
                        <h1>register</h1>
                        <h2>$userError</h2>
                        <input type="text" name="name" class="name" required placeholder="nome">
                        <input type="text" name="surname" class="surname" required placeholder="cognome">
                        <input type="email" name="email" class="email" required placeholder="nome@esempio.com">
                        <input type="password" name="password" class="password" required placeholder="P@s5u0rd">
                        <input type="text" name="domicilio" class="domicilio" required placeholder="indirizzo di casa">
                        <input type="submit" class="submit" value="register" name="register">
                        <button class="changeform">hai già un account?</button>
                    </form>
                </div>
            HTML;
        ?>
    </main>
    <footer id="userarea_footer">
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

<script src="./userarea.js"></script>

</html>