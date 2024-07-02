<?php
$title = "Dashboard - List Products";
ob_start();
include '../../configs/db.php';


function get_purchases() {
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi Database Gagal";
    }

    $query = "
        SELECT p.*, c.name AS supplier_name, pr.name AS product_name
        FROM purchases p
        LEFT JOIN suppliers c ON p.supplier_id = c.id
        LEFT JOIN products pr ON p.product_id = pr.id
    ";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$purchases = get_purchases();

function delete_purchase($purchase_id) 
{
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi Database Gagal";
    }

    $stmt = $conn->prepare("DELETE FROM purchases WHERE id = ?");
    if ($stmt === false) {
        return "Gagal menyiapkan statement";
    }

    $stmt->bind_param("i", $purchase_id);

    if ($stmt->execute()) {
        $result = "Data pembelian berhasil dihapus";
    } else {
        $result = "Gagal menghapus data pembelian: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    return $result;
}

// Delete user if delete request is received
if (isset($_GET['delete_purchase_id'])) {
    $delete_result = delete_purchase((int)$_GET['delete_purchase_id']);
    // Redirect or show message as needed
    // For example, you could redirect to the same page to refresh the list:
    header("Location: index.php");
    exit;
}
?>

<div class="w-full h-full p-2 lg:p-4">

    <div class="relative overflow-x-auto">
        <div class="flex mb-2">
            <a href="add-purchase.php" class="text-white float-end bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none">Tambah Pembelanjaan</a>
        </div>
        <table class="w-full border shadow-md text-sm text-left rtl:text-right text-gray-500 rounded-lg">
            <thead class="text-xs text-gray-700 uppercase bg-blue-500/20">
                <tr>
                    <th class="px-2 py-3 border" align="center">No</th>
                    <th scope="col" class="px-6 py-3 border">Supplier</th>
                    <th scope="col" class="px-6 py-3 border">Product</th>
                    <th scope="col" align="center" class="px-6 py-3 border">Purchase Date</th>
                    <th scope="col" class="px-6 py-3 border" align="center">Quantity</th>
                    <th scope="col" class="px-6 py-3 border" align="center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($purchases) && !empty($purchases)) : ?>
                    <?php foreach ($purchases as $index => $purchase) : ?>
                        <tr class="bg-white border-b">
                            <td class="px-2 py-3 text-center" align="center"><?php echo htmlspecialchars($index + 1); ?></td>
                            <td class="px-6 py-3 border"><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                            <td class="px-6 py-3 border"><?php echo htmlspecialchars($purchase['product_name']); ?></td>
                            <td class="px-6 py-3 border" align="center"><?php echo htmlspecialchars($purchase['purchase_date']); ?></td>
                            <td class="px-6 py-3 border" align="center"><?php echo htmlspecialchars($purchase['quantity']); ?></td>
                            <td class="px-6 py-3 border" align="center">
                                <div class="grid grid-cols-2">
                                    <a href="edit-purchase.php?id=<?php echo htmlspecialchars($purchase['id']); ?>" title="Edit data ini" class="mx-auto fa-solid fa-pen-to-square cursor-pointer text-blue-500 text-base lg:text-lg"></a>
                                    <a href="?delete_purchase_id=<?php echo htmlspecialchars($purchase['id']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data pembelian ini?');" title="Delete data ini" class="mx-auto fa-solid fa-trash cursor-pointer text-red-500 text-base lg:text-lg"></a>
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
<?php
$content = ob_get_clean();
include '../layout.php';
?>