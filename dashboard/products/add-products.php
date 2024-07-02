<?php
$title = "Dashboard - Add Product";
$base_dir = dirname(__FILE__);
$base_url = "/minimarket/";
include '../../configs/db.php';
session_start();
ob_start();

function validate_input($data) {
    $errors = [];
    if (empty($data['name'])) {
        $errors[] = 'Nama produk harus diisi';
    }
    if (empty($data['code'])) {
        $errors[] = 'Kode produk harus diisi';
    }
    if (empty($data['price'])) {
        $errors[] = 'Harga produk harus diisi';
    }
    if (empty($data['category_id'])) {
        $errors[] = 'Kategori produk harus diisi';
    }
    if (empty($data['stock'])) {
        $errors[] = 'Jumlah stok harus diisi';
    }
    if (empty($data['expire_date'])) {
        $errors[] = 'Tanggal kadaluarsa produk harus diisi';
    }
    return $errors;
}

function add_product($data) {
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    $stmt = $conn->prepare("INSERT INTO products (code, name, price, category_id, stock, expire_date) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        return "Query gagal: " . $conn->error;
    }

    $stmt->bind_param("ssdiis", $data['code'], $data['name'], $data['price'], $data['category_id'], $data['stock'], $data['expire_date']);

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
        'code' => $_POST['code'],
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'category_id' => $_POST['category_id'],
        'stock' => $_POST['stock'],
        'expire_date' => $_POST['expire_date']
    ];

    $errors = validate_input($data);
    if (empty($errors)) {
        $result = add_product($data);
        if ($result === true) {
            $message = '
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                <span class="font-medium">Berhasil menambahkan produk!</span>
            </div>';
            header("refresh:1;url=../products/index.php");
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


function get_categories() {
    $conn = get_db_connection();
    if ($conn === false) {
        return [];
    }

    $query = "SELECT * FROM category";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$categories = get_categories();
?>

<h1 class="text-base lg:text-xl">Tambah Produk</h1>
<div class="w-full h-full">
    <?php echo $message; ?>
    <form action="" method="post" class="max-w-sm mt-5 grid gap-3">
        <div>
            <label for="code" class="block mb-1 text-sm font-medium text-gray-900">Kode Produk</label>
            <input required type="text" name="code" id="code" value="<?php echo isset($_POST['code']) ? htmlspecialchars($_POST['code']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="name" class="block mb-1 text-sm font-medium text-gray-900">Nama Produk</label>
            <input required type="text" name="name" id="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="price" class="block mb-1 text-sm font-medium text-gray-900 ">Harga Produk</label>
            <input required type="number" name="price" id="price" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="category_id" class="block mb-1 text-sm font-medium text-gray-900">Kategori Produk</label>
            <select id="category_id" name="category_id" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                <option hidden selected>Pilih Kategori</option>
                <?php foreach ($categories as $index => $category) : ?>
                <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="stock" class="block mb-1 text-sm font-medium text-gray-900">Jumlah Stok</label>
            <input required type="number" name="stock" id="stock" value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="expire_date" class="block mb-1 text-sm font-medium text-gray-900">Tanggal Kadaluarsa</label>
            <input required type="datetime-local" name="expire_date" id="expire_date" value="<?php echo isset($_POST['expire_date']) ? htmlspecialchars($_POST['expire_date']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex items-center gap-2 justify-end">
            <a href="<?php echo $base_url; ?>dashboard/products/index.php" type="button" class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Cancel</a>

            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Submit</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>
