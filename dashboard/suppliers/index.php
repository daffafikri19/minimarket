<?php
$title = "Dashboard - List Suppliers";
ob_start();
include '../../configs/db.php';

function get_suppliers() {
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi Database Gagal";
    }

    $query = "SELECT * FROM suppliers";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$suppliers = get_suppliers();

function validate_input($data) {
    $errors = [];
    if (empty($data['name'])) {
        $errors[] = 'Nama supplier harus diisi';
    }
    if (empty($data['contact'])) {
        $errors[] = 'Kontak Telp supplier harus diisi';
    }
    if (empty($data['email'])) {
        $errors[] = 'Email supplier harus diisi';
    }
    return $errors;
}

function add_supplier($data)
{
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    $stmt = $conn->prepare("INSERT INTO suppliers (name, contact, email) VALUES (?, ?, ?)");
    if ($stmt === false) {
        return "Query gagal: " . $conn->error;
    }

    $stmt->bind_param("sss", $data['name'], $data['contact'], $data['email']);

    if ($stmt->execute()) {
        return true;
    } else {
        return $stmt->error;
    }
}

function edit_supplier($id, $data)
{
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    $stmt = $conn->prepare("UPDATE suppliers SET name = ?, contact = ?, email = ? WHERE id = ?");
    if ($stmt === false) {
        return "Gagal menyiapkan statement: " . $conn->error;
    }

    $stmt->bind_param("sssi", $data['name'], $data['contact'], $data['email'], $id);

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
        'contact' => htmlspecialchars($_POST['contact']),
        'email' => htmlspecialchars($_POST['email']),
    ];

    if (isset($_POST['supplier_id']) && !empty($_POST['supplier_id'])) {
        // Edit category
        $supplier_id = (int)$_POST['supplier_id'];
        $errors = validate_input($data);
        if (empty($errors)) {
            $result = edit_supplier($supplier_id, $data);
            if ($result === true) {
                $message = '
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                    <span class="font-medium">Data supplier berhasil diperbarui!</span>
                </div>';
                header("refresh:1;url=index.php");
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
        // Add new supplier
        $errors = validate_input($data);
        if (empty($errors)) {
            $result = add_supplier($data);
            if ($result === true) {
                $message = '
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                    <span class="font-medium">Berhasil menambahkan data supplier baru!</span>
                </div>';
                header("refresh:1;url=index.php");
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

    $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
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

if (isset($_GET['delete_supplier_id'])) {
    $supplier_id = (int)$_GET['delete_supplier_id'];
    $result = delete_category($supplier_id);
    if ($result === true) {
        $message = '
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
            <span class="font-medium">Data supplier berhasil dihapus!</span>
        </div>';
        header("refresh:1;url=index.php");
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
            <button onclick="document.getElementById('add-supplier').showModal();" class="text-white float-end bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none">Tambah Supplier</button>
        </div>
        <table class="w-full border shadow-md text-sm text-left rtl:text-right text-gray-500 rounded-lg">
            <thead class="text-xs text-gray-700 uppercase bg-blue-500/20">
                <tr>
                    <th class="px-2 py-3 border" align="center">No</th>
                    <th scope="col" class="px-6 py-3 border">Name</th>
                    <th scope="col" class="px-6 py-3 border">Contact</th>
                    <th scope="col" class="px-6 py-3 border">Email</th>
                    <th scope="col" class="px-6 py-3 border" align="center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($suppliers) && !empty($suppliers)) : ?>
                    <?php foreach ($suppliers as $index => $supplier) : ?>
                        <tr class="bg-white border-b">
                            <td class="px-2 py-3 text-center" align="center"><?php echo htmlspecialchars($index + 1); ?></td>
                            <td class="px-6 py-3 border" align="center"><?php echo htmlspecialchars($supplier['name']); ?></td>
                            <td class="px-6 py-3 border"><?php echo htmlspecialchars($supplier['contact']); ?></td>
                            <td class="px-6 py-3 border"><?php echo htmlspecialchars($supplier['email']); ?></td>
                            <td class="px-6 py-3 border" align="center">
                                <div class="grid grid-cols-2">
                                    <button onclick="openEditSupplierDialog('<?php echo htmlspecialchars($supplier['id']); ?>', '<?php echo htmlspecialchars($supplier['name']); ?>', '<?php echo htmlspecialchars($supplier['contact']); ?>', '<?php echo htmlspecialchars($supplier['email']); ?>')" title="Edit data ini" class="mx-auto fa-solid fa-pen-to-square cursor-pointer text-blue-500 text-base lg:text-lg"></button>
                                    <a href="?delete_supplier_id=<?php echo htmlspecialchars($supplier['id']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data supplier ini?');" title="Delete data ini" class="mx-auto fa-solid fa-trash cursor-pointer text-red-500 text-base lg:text-lg"></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="bg-white border-b">
                        <td colspan="5" class="px-6 py-3 text-center">Tidak ada data yang ditampilkan</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<dialog role="dialog" id="add-supplier" class="p-2 lg:p-4 rounded-lg shadow-lg">
    <div class="flex items-center justify-between gap-4 border-b pb-2">
        <h1>Tambah Supplier</h1>
        <button onclick="document.getElementById('add-supplier').close();">
            <i class="fa-solid fa-x cursor-pointer"></i>
        </button>
    </div>
    <div>
        <form class="max-w-lg mx-auto grid gap-4" action="" method="post">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-900">Nama Supplier</label>
                <input type="text" required name="name" id="name" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="contact" class="block text-sm font-medium text-gray-900">No. Telp Supplier</label>
                <input type="tel" required name="contact" id="contact" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-900">Email Supplier</label>
                <input type="email" required name="email" id="email" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mt-5 grid grid-cols-2 gap-4">
                <button type="button" class="w-full text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2" onclick="document.getElementById('add-supplier').close();">Batal</button>

                <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

<dialog role="dialog" id="edit-supplier" class="p-2 lg:p-4 rounded-lg shadow-lg">
    <div class="flex items-center justify-between gap-4 border-b pb-2">
        <h1>Tambah Supplier</h1>
        <button onclick="document.getElementById('edit-supplier').close();">
            <i class="fa-solid fa-x cursor-pointer"></i>
        </button>
    </div>
    <div>
        <form class="max-w-lg mx-auto grid gap-4" action="" method="post">
            <input type="hidden" name="supplier_id" id="edit-supplier-id">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-900">Nama Supplier</label>
                <input type="text" required name="name" id="edit-name" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="contact" class="block text-sm font-medium text-gray-900">No. Telp Supplier</label>
                <input type="tel" required name="contact" id="edit-contact" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-900">Email Supplier</label>
                <input type="email" required name="email" id="edit-email" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mt-5 grid grid-cols-2 gap-4">
                <button type="button" class="w-full text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2" onclick="document.getElementById('edit-supplier').close();">Batal</button>

                <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

<script>
    function openEditSupplierDialog(id, name, contact, email) {
        document.getElementById('edit-supplier-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-contact').value = contact;
        document.getElementById('edit-email').value = email;
        document.getElementById('edit-supplier').showModal();
    }
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>