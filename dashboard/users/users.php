<?php
$title = "Dashboard - List Users";
ob_start();
include '../../configs/db.php';

function get_users()
{
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi Database Gagal";
    }

    $result = $conn->query("SELECT id, username, email, role FROM users");

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$users = get_users();


function delete_user($userid) 
{
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi Database Gagal";
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt === false) {
        return "Gagal menyiapkan statement";
    }

    $stmt->bind_param("i", $userid);

    if ($stmt->execute()) {
        $result = "Pengguna berhasil dihapus";
    } else {
        $result = "Gagal menghapus pengguna: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    return $result;
}

// Delete user if delete request is received
if (isset($_GET['delete_user_id'])) {
    $delete_result = delete_user((int)$_GET['delete_user_id']);
    // Redirect or show message as needed
    // For example, you could redirect to the same page to refresh the list:
    header("Location: users.php");
    exit;
}

?>


<div class="w-full h-full p-2 lg:p-4">
    <div class="relative overflow-x-auto">
        <div class="flex mb-2">
            <a href="add-user.php" class="text-white float-end bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Tambah User</a>
        </div>
        <div>
            <table class="w-full border shadow-md lg:w-1/2 text-sm text-left rtl:text-right text-gray-500 rounded-lg">
                <thead class="text-xs text-gray-700 uppercase bg-blue-500/20">
                    <tr>
                        <th class="px-2 py-3 border" align="center">No</th>
                        <th class="px-6 py-3 border">Username</th>
                        <th class="px-6 py-3 border">Email</th>
                        <th class="px-6 py-3 border">Role</th>
                        <th class="px-6 py-3 bordedr">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($users) && !empty($users)) : ?>
                        <?php foreach ($users as $index => $user) : ?>
                            <tr class="bg-white border-b">
                                <td class="px-2 py-3 text-center"><?php echo htmlspecialchars($index + 1); ?></td>
                                <td class="px-6 py-3 border"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="px-6 py-3 border"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="px-6 py-3 border"><?php echo htmlspecialchars($user['role']); ?></td>
                                <td class="px-6 py-3 border">
                                    <div class="grid grid-cols-2">
                                        <a href="edit-user.php?id=<?php echo htmlspecialchars($user['id']); ?>" title="Edit data ini" class="mx-auto fa-solid fa-pen-to-square cursor-pointer text-blue-500 text-base lg:text-lg"></a>
                                        <a href="?delete_user_id=<?php echo htmlspecialchars($user['id']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?');" title="Delete data ini" class="mx-auto fa-solid fa-trash cursor-pointer text-red-500 text-base lg:text-lg"></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="bg-white border-b">
                            <td colspan="4" class="px-6 py-3 text-center">Tidak ada data yang ditampilkan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>