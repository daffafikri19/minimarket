<?php
$title = "Dashboard - Tambah Pengguna";
$base_dir = dirname(__FILE__);
$base_url = "/minimarket/";
include '../../configs/db.php';
ob_start();

function validate_input($data) {
    $errors = [];
    if (empty($data['username'])) {
        $errors[] = 'Username harus diisi';
    }
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid';
    }
    if (empty($data['password']) || strlen($data['password']) < 8) {
        $errors[] = 'Password harus diisi dan tidak bisa kurang dari 8 karakter';
    }
    if ($data['password'] !== $data['confPassword']) {
        $errors[] = 'Passwords dan konfirmasi password tidak cocok';
    }
    if (empty($data['role']) || $data['role'] == 'select role') {
        $errors[] = 'Role harus diisi';
    }
    return $errors;
}

function add_user($data) {
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        return "Query gagal: " . $conn->error;
    }

    $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
    $stmt->bind_param("ssss", $data['username'], $data['email'], $hashed_password, $data['role']);

    if ($stmt->execute()) {
        return true;
    } else {
        return $stmt->error;
    }
}

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => htmlspecialchars($_POST['username']),
        'email' => htmlspecialchars($_POST['email']),
        'password' => $_POST['password'],
        'confPassword' => $_POST['confPassword'],
        'role' => htmlspecialchars($_POST['role'])
    ];

    $errors = validate_input($data);
    if (empty($errors)) {
        $result = add_user($data);
        if ($result === true) {
            $message = '
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                <span class="font-medium">Berhasil menambahkan user!</span>
            </div>';
            header("refresh:1;url=../users/users.php");
            exit;
        } else {
            $message = '
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                <span class="font-medium">Error:</span> ' . htmlspecialchars($result) . '
            </div>';
        }
    } else {
        foreach ($errors as $error) {
            $message .= '
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                <span class="font-medium">Error:</span> ' . htmlspecialchars($error) . '
            </div>';
        }
    }
}
?>

<h1 class="text-base lg:text-xl">Tambah Pengguna Baru</h1>
<div class="w-full h-full">
    <?php echo $message; ?>
    <form action="" method="post" class="max-w-sm mt-5 grid gap-3">
        <div>
            <label for="username" class="block mb-1 text-sm font-medium text-gray-900">Username</label>
            <input required type="text" name="username" id="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="email" class="block mb-1 text-sm font-medium text-gray-900">Email</label>
            <input required type="email" name="email" id="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="role" class="block mb-1 text-sm font-medium text-gray-900">Role</label>
            <select id="role" name="role" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                <option value="select role">Pilih role</option>
                <option value="SuperAdmin" <?php echo isset($_POST['role']) && $_POST['role'] === 'SuperAdmin' ? 'selected' : ''; ?>>Super Admin</option>
                <option value="Admin" <?php echo isset($_POST['role']) && $_POST['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        <div>
            <label for="password" class="block mb-1 text-sm font-medium text-gray-900">Password</label>
            <input required type="password" name="password" id="password" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="confPassword" class="block mb-1 text-sm font-medium text-gray-900">Konfirmasi Password</label>
            <input required type="password" name="confPassword" id="confPassword" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex items-center gap-2 justify-end">
            <a href="<?php echo $base_url; ?>dashboard/users/users.php" type="button" class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Kembali</a>

            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Simpan</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>
