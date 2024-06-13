<?php
session_start();
require_once "connection.php";



$response = [];

if (isset($_SESSION["user"])) {
    $response["status"] = "redirect";
    $response["redirect"] = "index.php";
    echo json_encode($response);
    die();
}

if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql_user = "SELECT * FROM user WHERE email = ?";
    $sql_admin = "SELECT * FROM admin WHERE email = ?";

    $stmt_user = mysqli_stmt_init($conn);
    $stmt_admin = mysqli_stmt_init($conn);

    // Login untuk user biasa
    if (mysqli_stmt_prepare($stmt_user, $sql_user)) {
        mysqli_stmt_bind_param($stmt_user, "s", $email);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);
        $user = mysqli_fetch_array($result_user, MYSQLI_ASSOC);

        if ($user) {
            if (password_verify($password, $user["password"])) {
                $_SESSION["user"] = $user["nama"];
                $response["status"] = "success";
                $response["redirect"] = "index.php";
            } else {
                $response["status"] = "error";
                $response["message"] = "Password does not match";
            }
        } else {
            $response["status"] = "error";
            $response["message"] = "Email not found";
        }
    } else {
        $response["status"] = "error";
        $response["message"] = "Error";
    }

    // Login untuk admin
    if (mysqli_stmt_prepare($stmt_admin, $sql_admin)) {
        mysqli_stmt_bind_param($stmt_admin, "s", $email);
        mysqli_stmt_execute($stmt_admin);
        $result_admin = mysqli_stmt_get_result($stmt_admin);
        $admin = mysqli_fetch_array($result_admin, MYSQLI_ASSOC);

        if ($admin && $email === "admin@gmail.com" && $password === "admin123") {
            $_SESSION["admin"] = $admin["nama"];
            $response["status"] = "success";
            $response["redirect"] = "admin-index.php";
        }
    }

    echo json_encode($response);
    die();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VolunTrek - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css"
        integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="./assets/images/favicon.png" type="image/svg+xml">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
    .notification-popup {
        position: fixed;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        padding: 10px;
        color: #fff;
        display: none;
        /* Initially hide the notification */
    }
</style>
</head>

<body>
    <div class="container">
        <form id="loginForm" method="post">
            <div class="form-group">
                <input type="email" placeholder="Enter Email:" name="email" class="form-control" id="email">
            </div>
            <div class="form-group">
                <input type="password" placeholder="Enter Password:" name="password" class="form-control" id="password">
            </div>
            <div class="form-btn">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
        <div>
            <p>Not registered yet? <a href="registration.php">Register Here</a></p>
        </div>
        <div>
            <p>Forgot your password? <a href="forgot_password.php">Reset it here</a></p>
        </div>
        <div id="response" class="notification-popup"></div>
    </div>

    <script>
    $(document).ready(function () {
        $("#loginForm").on('submit', function (event) {
            event.preventDefault();

            var email = $("#email").val();
            var password = $("#password").val();

            $.ajax({
                url: "login.php",
                type: "POST",
                data: {
                    login: true,
                    email: email,
                    password: password
                },
                success: function (response) {
                    var data = JSON.parse(response);
                    if (data.status === "success") {
                        window.location.href = data.redirect;
                    } else {
                        var responseDiv = $("#response");
                        responseDiv.html("<p class='alert alert-danger'>" + data.message + "</p>");
                        responseDiv.show(); // Show the notification
                        setTimeout(function() {
                            responseDiv.hide(); // Hide the notification after 0.5 seconds
                        }, 1000);
                    }
                },
                error: function () {
                    var responseDiv = $("#response");
                    responseDiv.html("<p class='alert alert-danger'>An error occurred. Please try again.</p>");
                    responseDiv.show(); // Show the notification
                    setTimeout(function() {
                        responseDiv.hide(); // Hide the notification after 0.5 seconds
                    }, 1000);
                }
            });
        });
    });
</script>
</body>

</html>
