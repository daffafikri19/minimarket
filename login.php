<?php
session_start();
include 'configs/db.php'; 

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $conn = get_db_connection();
    if ($conn === false) {
        $error = "Database connection failed";
    } else {
        $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: dashboard/index.php');
                exit;
            } else {
                $error = 'Password Salah';
            }
        } else {
            $error = 'Akun tidak ditemukan';
        }
    }
}
?>

<div class="w-full min-h-screen">
    <div class="grid grid-cols-1 lg:grid-cols-2">
        <div style="background-image: url('assets/background-minimarket.jpg'); background-repeat: no-repeat; background-position: center;">
        </div>
        <div class="w-full h-screen flex items-center justify-center">
            <div>
                <h1 class="text-xl lg:text-2xl text-center font-bold text-blue-400">
                    Dashboard Kelola MiniMarket
                </h1>
                <?php if ($error): ?>
                    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form action="" method="post" class="mt-5 grid gap-3">
                    <div class="grid">
                        <label for="email">Email</label>
                        <input class="rounded-md p-1 border-2 border-blue-300" type="email" placeholder="Masukan email" name="email" required>
                    </div>
                    <div class="grid">
                        <label for="password">Password</label>
                        <input class="rounded-md p-1 border-2 border-blue-300" type="password" name="password" required>
                    </div>
                    <div class="flex items-center justify-end">
                        <button type="submit" class="rounded-md py-1 px-6 bg-blue-500 text-white">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
