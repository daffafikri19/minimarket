<?php
$title = "Dashboard - List Products";
ob_start();
include '../../configs/db.php';

function get_products() {
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi Database Gagal";
    }

    $query = "
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN category c ON p.category_id = c.id
    ";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$products = get_products();

function delete_product($product_id) 
{
    $conn = get_db_connection();
    if ($conn === false) {
        return "Koneksi Database Gagal";
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt === false) {
        return "Gagal menyiapkan statement";
    }

    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $result = "Produk berhasil dihapus";
    } else {
        $result = "Gagal menghapus produk: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    return $result;
}

// Delete user if delete request is received
if (isset($_GET['delete_product_id'])) {
    $delete_result = delete_product((int)$_GET['delete_product_id']);
    // Redirect or show message as needed
    // For example, you could redirect to the same page to refresh the list:
    header("Location: index.php");
    exit;
}
?>

<div class="w-full h-full p-2 lg:p-4">

    <div class="relative overflow-x-auto">
        <div class="flex mb-2">
            <a href="add-products.php" class="text-white float-end bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none">Tambah Produk</a>
        </div>
        <table class="w-full border shadow-md text-sm text-left rtl:text-right text-gray-500 rounded-lg">
            <thead class="text-xs text-gray-700 uppercase bg-blue-500/20">
                <tr>
                    <th class="px-2 py-3 border" align="center">No</th>
                    <th scope="col" align="center" class="px-6 py-3 border">Kode Produk</th>
                    <th scope="col" class="px-6 py-3 border">Nama</th>
                    <th scope="col" align="center" class="px-6 py-3 border">Harga</th>
                    <th scope="col" class="px-6 py-3 border">Kategori</th>
                    <th scope="col" align="center" class="px-6 py-3 border">Jumlah Stok</th>
                    <th scope="col" class="px-6 py-3 border" align="center">Tgl Kadaluarsa</th>
                    <th scope="col" class="px-6 py-3 border" align="center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($products) && !empty($products)) : ?>
                    <?php foreach ($products as $index => $product) : ?>
                        <tr class="bg-white border-b">
                            <td class="px-2 py-3 text-center" align="center"><?php echo htmlspecialchars($index + 1); ?></td>
                            <td class="px-6 py-3 border" align="center"><?php echo htmlspecialchars($product['code']); ?></td>
                            <td class="px-6 py-3 border"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="px-6 py-3 border" align="center">Rp. <?php echo htmlspecialchars($product['price']); ?></td>
                            <td class="px-6 py-3 border"><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td class="px-6 py-3 border" align="center"><?php echo htmlspecialchars($product['stock']); ?></td>
                            <td class="px-6 py-3 border" align="center"><?php echo htmlspecialchars($product['expire_date']); ?></td>
                            <td class="px-6 py-3 border" align="center">
                                <div class="grid grid-cols-2">
                                    <a href="edit-product.php?id=<?php echo htmlspecialchars($product['id']); ?>" title="Edit data ini" class="mx-auto fa-solid fa-pen-to-square cursor-pointer text-blue-500 text-base lg:text-lg"></a>
                                    <a href="?delete_product_id=<?php echo htmlspecialchars($product['id']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" title="Delete data ini" class="mx-auto fa-solid fa-trash cursor-pointer text-red-500 text-base lg:text-lg"></a>
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