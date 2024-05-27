<?php
include_once "../../inc/db_config.php";
include_once "../../utilities/QueryBuilder.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //! LOGIN
    if (!empty($_POST["login"])) {
        session_start();
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

                    $response = [
                        "status" => "success",
                        "message" => "Successfully registered",
                        "session_id" => session_id()
                    ];
                } else {
                    //? User not found
                    $response = [
                        "status" => "error",
                        "message" => "Invalid email or password!"
                    ];
                }
            }
        } else {
            //? Email or password missing
            $response = [
                "status" => "error",
                "message" => "Email or password missing!"
            ];
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    //! REGISTER
    if (!empty($_POST["register"])) {
        session_start();
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

                    $response = [
                        "status" => "success",
                        "message" => "Successfully registered",
                        "session_id" => session_id()
                    ];
                } else {
                    $response = [
                        "status" => "error",
                        "message" => "Invalid data for registration!"
                    ];
                }
            } else {
                $response = [
                    "status" => "error",
                    "message" => "User with this email already exists!"
                ];
            }
        } else {
            $userError = "Fields missing!";
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    //! IS LOGGED
    if (isset($_GET['logged']) && !empty($_POST['session_id'])) {
        session_id($_POST['session_id']);
        session_start();
        $response = !empty($_SESSION['user_id']) ?
            [
                "status" => "succes",
                "message" => "Logged",
            ] :
            [
                "status" => "succes",
                "message" => "Not Logged",
            ];
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    //! LOGOUT
    if (isset($_GET['logout']) && !empty($_POST['session_id'])) {
        session_id($_POST['session_id']);
        session_start();
        session_destroy();
        $response = [
            "status" => "succes",
            "message" => "Successfully slogged",
        ];
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}
