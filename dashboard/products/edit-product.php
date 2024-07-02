<?php
$title = "Dashboard - Edit Product";
$base_dir = dirname(__FILE__);
$base_url = "/minimarket/";
include '../../configs/db.php';
session_start();
ob_start();

function validate_input($data) {
    $errors = [];
    if (empty($data['code'])) {
        $errors[] = 'Kode produk harus diisi';
    }
    if (empty($data['name'])) {
        $errors[] = 'Nama produk harus diisi';
    }
    if (empty($data['price'])) {
        $errors[] = 'Harga produk harus diisi';
    }
    if (empty($data['category_id'])) {
        $errors[] = 'Kategori produk harus diisi';
    }
    if (empty($data['stock'])) {
        $errors[] = 'Stok produk harus diisi';
    }
    if (empty($data['expire_date'])) {
        $errors[] = 'Tanggal kadaluarsa produk harus diisi';
    }
    return $errors;
}

function format_datetime_for_input($datetime) {
    return date('Y-m-d\TH:i', strtotime($datetime));
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

function get_product_by_id($id) {
    $conn = get_db_connection();
    if ($conn === false) {
        return null;
    }

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    if ($stmt === false) {
        return null;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $product;
}

function edit_product($id, $data) {
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    // Mengatur default waktu jika kosong
    if (empty($data['expire_date'])) {
        $data['expire_date'] = date('Y-m-d H:i:s');
    } else {
        $data['expire_date'] = date('Y-m-d H:i:s', strtotime($data['expire_date']));
    }

    $stmt = $conn->prepare("UPDATE products SET code = ?, name = ?, price = ?, category_id = ?, stock = ?, expire_date = ? WHERE id = ?");
    if ($stmt === false) {
        return "Gagal menyiapkan statement: " . $conn->error;
    }

    $stmt->bind_param("ssiiisi", $data['code'], $data['name'], $data['price'], $data['category_id'], $data['stock'], $data['expire_date'], $id);

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
$product = null;

if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $product = get_product_by_id($product_id);
    if (!$product) {
        $message = '
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">Error:</span> Produk tidak ditemukan.
        </div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($product_id)) {
    $data = [
        'code' => htmlspecialchars($_POST['code']),
        'name' => htmlspecialchars($_POST['name']),
        'price' => htmlspecialchars($_POST['price']),
        'category_id' => htmlspecialchars($_POST['category_id']),
        'stock' => htmlspecialchars($_POST['stock']),
        'expire_date' => htmlspecialchars($_POST['expire_date']),
    ];

    $errors = validate_input($data);
    if (empty($errors)) {
        $result = edit_product($product_id, $data);
        if ($result === true) {
            $message = '
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                <span class="font-medium">Produk diperbarui dengan sukses!</span>
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

?>

<h1 class="text-base lg:text-xl">Edit Produk</h1>
<div class="w-full h-full">
    <?php echo $message; ?>
    <form action="" method="post" class="max-w-sm mt-5 grid gap-3">
        <div>
            <label for="code" class="block mb-1 text-sm font-medium text-gray-900">Kode Produk</label>
            <input required type="text" name="code" id="code" value="<?php echo isset($_POST['code']) ? htmlspecialchars($_POST['code']) : htmlspecialchars($product['code']); ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="name" class="block mb-1 text-sm font-medium text-gray-900">Nama Produk</label>
            <input required type="text" name="name" id="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($product['name']); ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="price" class="block mb-1 text-sm font-medium text-gray-900">Harga Produk</label>
            <input required type="number" step="0.01" name="price" id="price" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : htmlspecialchars($product['price']); ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="category_id" class="block mb-1 text-sm font-medium text-gray-900">Kategori Produk</label>
            <select id="category_id" name="category_id" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                <option hidden selected>Pilih Kategori</option>
                <?php foreach ($categories as $index => $category) : ?>
                <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $category['id'] ? 'selected' : ($product['category_id'] == $category['id'] ? 'selected' : ''); ?>><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="stock" class="block mb-1 text-sm font-medium text-gray-900">Jumlah Stok</label>
            <input required type="number" name="stock" id="stock" value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : htmlspecialchars($product['stock']); ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
        <label for="expire_date" class="block mb-1 text-sm font-medium text-gray-900">Tanggal Kadaluarsa</label>
        <input required type="datetime-local" name="expire_date" id="expire_date" 
               value="<?php echo isset($_POST['expire_date']) ? htmlspecialchars($_POST['expire_date']) : (isset($product['expire_date']) ? htmlspecialchars(format_datetime_for_input($product['expire_date'])) : ''); ?>" 
               class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
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
