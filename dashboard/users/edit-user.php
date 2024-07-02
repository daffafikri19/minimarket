<?php
$title = "Dashboard - Edit Pengguna";
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
    if (!empty($data['password']) && strlen($data['password']) < 8) {
        $errors[] = 'Password tidak bisa kurang dari 8 karakter';
    }
    if ($data['password'] !== $data['confPassword']) {
        $errors[] = 'Password dan konfirmasi password tidak cocok';
    }
    if (empty($data['role']) || $data['role'] == 'select role') {
        $errors[] = 'Role harus diisi';
    }
    return $errors;
}

function get_user_by_id($id) {
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if ($stmt === false) {
        return "Gagal menyiapkan statement";
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $user;
}

function edit_user($id, $data) {
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? " . (!empty($data['password']) ? ", password = ?" : "") . " WHERE id = ?");
    if ($stmt === false) {
        return "Gagal menyiapkan statement: " . $conn->error;
    }

    if (!empty($data['password'])) {
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt->bind_param("ssssi", $data['username'], $data['email'], $data['role'], $hashed_password, $id);
    } else {
        $stmt->bind_param("sssi", $data['username'], $data['email'], $data['role'], $id);
    }

    if ($stmt->execute()) {
        $result = true;
    } else {
        $result = $stmt->error;
    }

    $stmt->close();
    $conn->close();

    return $result;
}

$message = '';
$errors = [];
$user = null;

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $user = get_user_by_id($user_id);
    if (!$user) {
        $message = '
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">Error:</span> Pengguna tidak ditemukan.
        </div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $data = [
        'username' => htmlspecialchars($_POST['username']),
        'email' => htmlspecialchars($_POST['email']),
        'password' => $_POST['password'],
        'confPassword' => $_POST['confPassword'],
        'role' => htmlspecialchars($_POST['role'])
    ];

    $errors = validate_input($data);
    if (empty($errors)) {
        $result = edit_user($user_id, $data);
        if ($result === true) {
            $message = '
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                <span class="font-medium">User updated successfully!</span>
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

<h1 class="text-base lg:text-xl">Edit User</h1>
<div class="w-full h-full">
    <?php echo $message; ?>
    <form action="" method="post" class="max-w-sm mt-5 grid gap-3">
        <div>
            <label for="username" class="block mb-1 text-sm font-medium text-gray-900">Username</label>
            <input required type="text" name="username" id="username" value="<?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="email" class="block mb-1 text-sm font-medium text-gray-900">Email</label>
            <input required type="email" name="email" id="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="role" class="block mb-1 text-sm font-medium text-gray-900">Role</label>
            <select id="role" name="role" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                <option value="select role">Pilih role</option>
                <option value="SuperAdmin" <?php echo isset($user['role']) && $user['role'] === 'SuperAdmin' ? 'selected' : ''; ?>>Super Admin</option>
                <option value="Admin" <?php echo isset($user['role']) && $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        <div>
            <label for="password" class="block mb-1 text-sm font-medium text-gray-900">Password (biarkan kosong jika tidak ingin diubah)</label>
            <input type="password" name="password" id="password" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="confPassword" class="block mb-1 text-sm font-medium text-gray-900">Konfirmasi Password (biarkan kosong jika tidak ingin diubah)</label>
            <input type="password" name="confPassword" id="confPassword" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex items-center gap-2 justify-end">
            <a href="<?php echo $base_url; ?>dashboard/users/users.php" class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Kembali</a>

            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Simpan</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>
