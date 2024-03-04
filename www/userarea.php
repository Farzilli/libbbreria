<?php
ob_start(); //! non rimuovere, si scassa l' header()
?>

<?php
session_start();

//? senza db, account base di test
if (!isset($_SESSION["accounts"]["cookie@test.com"])) {
    $testEmail = "cookie@test.com";
    $testPassword = "cookie";
    $_SESSION["accounts"][$testEmail] = ["password" => password_hash($testPassword, PASSWORD_DEFAULT), "date" => "2023-05-15"];
}

$userError = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //? logout
    if (isset($_POST['logout'])) {
        unset($_SESSION["email"]);
        unset($_SESSION["password"]);
        header("Location:userarea.php");
    }
    //? delate
    if (isset($_POST['delate'])) {
        unset($_SESSION["accounts"][$_SESSION["email"]]);
        unset($_SESSION["email"]);
        unset($_SESSION["password"]);
        header("Location:userarea.php");
    }
    //? login
    elseif (isset($_POST['login'])) {
        $email = $_POST['email'];
        $PASSWORD = $_POST['password'];

        if (isset($_SESSION["accounts"][$email]) && password_verify($PASSWORD, $_SESSION["accounts"][$email]["password"])) {
            $_SESSION["email"] = $email;
            $_SESSION["password"] = $PASSWORD;
            isset($_SESSION["backpage"]) ? header("Location:carrello.php") : header("Location:index.php");
            unset($_SESSION["backpage"]);
        } else $userError = "Invalid email or password!";
    }
    //? register
    elseif (isset($_POST['register'])) {
        $email = $_POST['email'];
        $PASSWORD = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $date = $_POST['date'];

        if (!isset($_SESSION["accounts"][$email]) && isset($email) && isset($PASSWORD) && isset($date)) {
            $_SESSION["accounts"][$email] = ["password" => $PASSWORD, "date" => $date];
            $_SESSION["email"] = $email;
            $_SESSION["password"] = $PASSWORD;
            isset($_SESSION["backpage"]) ? header("Location:carrello.php") : header("Location:index.php");
            unset($_SESSION["backpage"]);
        } else $userError = "Invalid data for registration!";
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
            <a href="find.php"><i style="background-image: url(./icon/find.png);"></i></a>
            <a id="selected" href="userarea.php"><i style="background-image: url(./icon/user.png);"></i></a>
            <a href="carrello.php"><i style="background-image: url(./icon/cart.png);"></i></a>
        </div>
    </nav>
    <main id="userarea_main">
        <?= isset($_SESSION["email"]) && isset($_SESSION["password"]) ?
            <<<HTML
                <div id="logout">
                    <h1>User: $_SESSION[email]</h1>
                    <form action="" method="post">
                        <input type="submit" value="logout" name="logout">
                        <input type="submit" value="delate" name="delate">
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
                        <input type="email" name="email" class="email" required placeholder="nome@esempio.com">
                        <input type="password" name="password" class="password" required placeholder="P@s5u0rd">
                        <input type="date" name="date" class="date" required>
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