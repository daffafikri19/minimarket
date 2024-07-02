<?php
$title = "Dashboard - Edit Purchasing";
$base_dir = dirname(__FILE__);
$base_url = "/minimarket/";
include '../../configs/db.php';
session_start();
ob_start();

function validate_input($data)
{
    $errors = [];
    if (empty($data['supplier_id'])) {
        $errors[] = 'Supplier harus diisi';
    }
    if (empty($data['product_id'])) {
        $errors[] = 'Produk harus diisi';
    }
    if (empty($data['purchase_date'])) {
        $errors[] = 'Tanggal Pembelian harus diisi';
    }
    if (empty($data['quantity'])) {
        $errors[] = 'Jumlah Pembelian harus diisi';
    }
    if (empty($data['total'])) {
        $errors[] = 'Jumlah harga Pembelian harus diisi';
    }
    return $errors;
}

function get_purchase($id)
{
    $conn = get_db_connection();
    if ($conn === false) {
        return null;
    }

    $stmt = $conn->prepare("SELECT * FROM purchases WHERE id = ?");
    if ($stmt === false) {
        return null;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

function update_purchase($data)
{
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi database gagal!";
    }

    $stmt = $conn->prepare("UPDATE purchases SET supplier_Id = ?, product_id = ?, purchase_date = ?, quantity = ?, total = ? WHERE id = ?");
    if ($stmt === false) {
        return "Query gagal: " . $conn->error;
    }

    $stmt->bind_param("iissii", $data['supplier_id'], $data['product_id'], $data['purchase_date'], $data['quantity'], $data['total'], $data['id']);

    if ($stmt->execute()) {
        return true;
    } else {
        return $stmt->error;
    }
}

$message = '';
$errors = [];
$purchase = null;

if (isset($_GET['id'])) {
    $purchase = get_purchase($_GET['id']);
    if ($purchase === null) {
        $message = '
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">Error:</span> Pembelian tidak ditemukan.
        </div>';
    }
} else {
    $message = '
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">Error:</span> ID Pembelian tidak disediakan.
    </div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => $_POST['id'],
        'supplier_id' => $_POST['supplier_id'],
        'product_id' => $_POST['product_id'],
        'purchase_date' => date('Y-m-d H:i:s', strtotime($_POST['purchase_date'])),
        'quantity' => $_POST['quantity'],
        'total' => $_POST['total']
    ];

    $errors = validate_input($data);
    if (empty($errors)) {
        $result = update_purchase($data);
        if ($result === true) {
            $message = '
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                <span class="font-medium">Berhasil memperbarui data pembelian!</span>
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

function get_suppliers()
{
    $conn = get_db_connection();
    if ($conn === false) {
        return [];
    }

    $query = "SELECT id, name FROM suppliers";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$suppliers = get_suppliers();

function get_products()
{
    $conn = get_db_connection();
    if ($conn === false) {
        return [];
    }

    $query = "SELECT id, name, price FROM products";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$products = get_products();

?>

<h1 class="text-base lg:text-xl">Edit Pembelian</h1>
<div class="w-full h-full">
    <?php echo $message; ?>
    <form action="" method="post" class="max-w-sm mt-5 grid gap-3">
        <input type="hidden" name="id" value="<?php echo isset($purchase['id']) ? htmlspecialchars($purchase['id']) : ''; ?>">
        <div>
            <label for="supplier_id" class="block mb-1 text-sm font-medium text-gray-900">Supplier</label>
            <select required id="supplier_id" name="supplier_id" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                <option hidden selected>Pilih supplier</option>
                <?php foreach ($suppliers as $index => $supplier) : ?>
                    <option value="<?php echo htmlspecialchars($supplier['id']); ?>" <?php echo ($supplier['id'] == $purchase['supplier_Id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($supplier['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="product_id" class="block mb-1 text-sm font-medium text-gray-900">Produk</label>
            <select required id="product_id" name="product_id" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" onchange="updateCurrentTotal()">
                <option hidden selected>Pilih produk</option>
                <?php foreach ($products as $index => $product) : ?>
                    <option price="<?php echo htmlspecialchars($product['price']); ?>" value="<?php echo htmlspecialchars($product['id']); ?>" <?php echo ($product['id'] == $purchase['product_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($product['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="quantity" class="block mb-1 text-sm font-medium text-gray-900">Jumlah Unit</label>
            <input required type="number" name="quantity" id="quantity" value="<?php echo isset($purchase['quantity']) ? htmlspecialchars($purchase['quantity']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500" oninput="updateCurrentTotal()">
        </div>
        <div>
            <label for="total" class="block mb-1 text-sm font-medium text-gray-900">Total Harga</label>
            <input required readonly type="number" name="total" id="update-total" value="<?php echo isset($purchase['total']) ? htmlspecialchars($purchase['total']) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="purchase_date" class="block mb-1 text-sm font-medium text-gray-900 ">Tanggal Pembelian</label>
            <input required type="datetime-local" name="purchase_date" id="purchase_date" value="<?php echo isset($purchase['purchase_date']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($purchase['purchase_date']))) : ''; ?>" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex items-center gap-2 justify-end">
            <a href="<?php echo $base_url; ?>dashboard/purchase/index.php" type="button" class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Cancel</a>

            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Submit</button>
        </div>
    </form>
</div>

<script>
        window.addEventListener('DOMContentLoaded', function() {
            updateCurrentTotal();

            var purchaseDateValue = document.getElementById('purchase_date').value;
            if (!purchaseDateValue) {
                var currentDate = new Date();
                
                // Format ke yyyy-MM-ddThh:mm
                var formattedDate = currentDate.toISOString().slice(0, 19);
                formattedDate = formattedDate.replace('T', ' ');

                // Ekstrak yyyy-MM-ddThh:mm
                var date = formattedDate.slice(0, 10);
                var time = formattedDate.slice(11, 16);
                
                // Gabungkan tanggal dan waktu untuk format yyyy-MM-ddThh:mm
                var dateTimeLocal = `${date}T${time}`;

                // Setel nilai pada input datetime-local
                var purchase_date = document.getElementById('purchase_date');
                purchase_date.value = dateTimeLocal;
            }
        })

        function updateCurrentTotal() {
            var productSelect = document.getElementById('product_id');
            var quantityInput = document.getElementById('quantity');
            var totalInput = document.getElementById('update-total');
            
            var selectedOption = productSelect.options[productSelect.selectedIndex];
            var price = parseInt(selectedOption.getAttribute('price')) || 0;
            
            var quantity = parseInt(quantityInput.value) || 0;
            
            var total = price * quantity;
            
            totalInput.value = total;
        }
    </script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>
