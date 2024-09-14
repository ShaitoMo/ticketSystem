<?php
session_start();
require_once 'config/database.php';
require_once 'models/User.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['id'];
    $password = $_POST['password'];

    $database = new Database();
    $db = $database->getConnection();

    $user = new User($db);
    $authUser = $user->authenticate($user_id, $password);

    if ($authUser) {
        $_SESSION['user_id'] = $authUser['id'];
        $role = $user->getUserRole($user_id);
        $_SESSION['role'] = $role;

        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid ID or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #508bfc;
        }
        .card {
            border-radius: 1rem;
        }
    </style>
</head>
<body>
    <section class="vh-100">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow-lg">
                        <div class="card-body p-5 text-center">
                            <h3 class="mb-4">Sign in</h3>
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="login.php">
                                <div class="mb-4">
                                    <input type="text" id="id" name="id" class="form-control form-control-lg" required />
                                    <label class="form-label" for="id">ID</label>
                                </div>

                                <div class="mb-4">
                                    <input type="password" id="password" name="password" class="form-control form-control-lg" required />
                                    <label class="form-label" for="password">Password</label>
                                </div>

                                <button class="btn btn-primary btn-lg btn-block" type="submit">Login</button>
                            </form>

                            <hr class="my-4">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
