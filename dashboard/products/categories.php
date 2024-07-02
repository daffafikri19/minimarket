<?php
$title = "Dashboard - Daftar Kategori";
ob_start();
include '../../configs/db.php';


function get_categories()
{
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi Database Gagal";
    }

    $result = $conn->query("SELECT * FROM category");

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$categories = get_categories();

function validate_input($data)
{
    $errors = [];
    if (empty($data['name'])) {
        $errors[] = 'Nama kategori harus diisi';
    }
    return $errors;
}

function add_category($data)
{
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    $stmt = $conn->prepare("INSERT INTO category (name) VALUES (?)");
    if ($stmt === false) {
        return "Query gagal: " . $conn->error;
    }

    $stmt->bind_param("s", $data['name']);

    if ($stmt->execute()) {
        return true;
    } else {
        return $stmt->error;
    }
}

function edit_category($id, $data)
{
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    $stmt = $conn->prepare("UPDATE category SET name = ? WHERE id = ?");
    if ($stmt === false) {
        return "Gagal menyiapkan statement: " . $conn->error;
    }

    $stmt->bind_param("si", $data['name'], $id);

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
        'name' => htmlspecialchars($_POST['name']),
    ];

    if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
        // Edit category
        $category_id = (int)$_POST['category_id'];
        $errors = validate_input($data);
        if (empty($errors)) {
            $result = edit_category($category_id, $data);
            if ($result === true) {
                $message = '
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                    <span class="font-medium">Kategori berhasil diperbarui!</span>
                </div>';
                header("refresh:1;url=categories.php");
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
    } else {
        // Add new category
        $errors = validate_input($data);
        if (empty($errors)) {
            $result = add_category($data);
            if ($result === true) {
                $message = '
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                    <span class="font-medium">Berhasil menambahkan kategori produk!</span>
                </div>';
                header("refresh:1;url=categories.php");
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
}

function delete_category($id) {
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    $stmt = $conn->prepare("DELETE FROM category WHERE id = ?");
    if ($stmt === false) {
        return "Gagal menyiapkan statement: " . $conn->error;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        return true;
    } else {
        return $stmt->error;
    }
}

if (isset($_GET['delete_category_id'])) {
    $category_id = (int)$_GET['delete_category_id'];
    $result = delete_category($category_id);
    if ($result === true) {
        $message = '
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
            <span class="font-medium">Kategori berhasil dihapus!</span>
        </div>';
        header("refresh:1;url=categories.php");
        exit;
    } else {
        $message = '
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">Error:</span> ' . htmlspecialchars($result) . '
        </div>';
    }
}


?>

<div class="w-full h-full p-2 lg:p-4">

    <div class="relative overflow-x-auto">
        <div class="flex mb-2">
            <button onclick="document.getElementById('add-category').showModal();" class="text-white float-end bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Tambah Kategori</button>
        </div>
        <table class="w-full border lg:w-1/2 shadow-md text-sm text-left rtl:text-right text-gray-500 rounded-lg">
            <thead class="text-xs text-gray-700 uppercase bg-blue-500/20">
                <tr>
                    <th class="px-2 py-3" align="center">No</th>
                    <th scope="col" class="px-6 py-3">Nama</th>
                    <th scope="col" align="center" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($categories) && !empty($categories)) : ?>
                    <?php foreach ($categories as $index => $category) : ?>
                        <tr class="bg-white border-b">
                            <td class="px-2 py-3 text-center" align="center"><?php echo htmlspecialchars($index + 1); ?></td>
                            <td class="px-6 py-3 border"><?php echo htmlspecialchars($category['name']); ?></td>
                            <td class="px-6 py-3 border" align="center">
                                <div class="grid grid-cols-2">
                                    <button onclick="openEditCategoryDialog('<?php echo htmlspecialchars($category['id']); ?>', '<?php echo htmlspecialchars($category['name']); ?>')">
                                        <i class="fa-solid fa-pen-to-square cursor-pointer text-blue-500 text-base lg:text-lg"></i>
                                    </button>

                                    <a href="?delete_category_id=<?php echo htmlspecialchars($category['id']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori produk ini?');" title="Delete data ini" class="mx-auto fa-solid fa-trash cursor-pointer text-red-500 text-base lg:text-lg"></a>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="bg-white border-b">
                        <td colspan="2" class="px-6 py-3 text-center">Tidak ada data yang ditampilkan</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<dialog role="dialog" id="add-category" class="p-2 lg:p-4 rounded-lg shadow-lg">
    <div class="flex items-center justify-between gap-4 border-b pb-2">
        <h1>Tambah Kategori</h1>
        <button onclick="document.getElementById('add-category').close();">
            <i class="fa-solid fa-x cursor-pointer"></i>
        </button>
    </div>
    <div>
        <form class="max-w-lg mx-auto grid" action="" method="post">
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Kategori</label>
                <input type="text" name="name" id="name" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mt-5 grid grid-cols-2 gap-4">
                <button type="button" class="w-full text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2" onclick="document.getElementById('add-category').close();">Batal</button>

                <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

<dialog role="dialog" id="edit-category" class="p-2 lg:p-4 rounded-lg shadow-lg">
    <div class="flex items-center justify-between gap-4 border-b pb-2">
        <h1>Edit Kategori</h1>
        <button onclick="document.getElementById('edit-category').close();">
            <i class="fa-solid fa-x cursor-pointer"></i>
        </button>
    </div>
    <div>
        <form class="max-w-lg mx-auto grid" action="" method="post">
            <input type="hidden" name="category_id" id="edit-category-id">
            <div>
                <label for="edit-name" class="block mb-2 text-sm font-medium text-gray-900">Nama Kategori</label>
                <input type="text" name="name" id="edit-name" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mt-5 grid grid-cols-2 gap-4">
                <button type="button" class="w-full text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2" onclick="document.getElementById('edit-category').close();">Batal</button>
                <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

<script>
    function openEditCategoryDialog(id, name) {
        document.getElementById('edit-category-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-category').showModal();
    }
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>