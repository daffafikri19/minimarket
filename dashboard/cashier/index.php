<?php
$title = "Dashboard - Kasir";
ob_start();
include '../../configs/db.php';

function get_products()
{
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

?>

<div class="w-full h-full p-2 lg:p-4">
    <div class="w-full flex flex-col lg:flex-row gap-2">
        <div class="w-full rounded-lg border flex-1 p-2">
            <div class="grid gap-3">
                <div>
                    <label for="product_id" class="block mb-1 text-sm font-medium text-gray-900">Produk</label>
                    <select id="product_id" name="product_id" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option hidden selected>Pilih Produk</option>
                        <?php foreach ($products as $product) : ?>
                            <option value="<?php echo htmlspecialchars($product['id']); ?>" data-price="<?php echo htmlspecialchars($product['price']); ?>" data-category="<?php echo htmlspecialchars($product['category_name']); ?>" data-code="<?php echo htmlspecialchars($product['code']); ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mt-2">
                    <label for="product_code" class="block mb-1 text-sm font-medium text-gray-900">Kode Produk</label>
                    <input type="text" id="product_code" name="product_code" readonly class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="mt-2">
                    <label for="product_price" class="block mb-1 text-sm font-medium text-gray-900">Harga Produk</label>
                    <input type="text" id="product_price" name="product_price" readonly class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="mt-2">
                    <label for="product_category" class="block mb-1 text-sm font-medium text-gray-900">Kategori Produk</label>
                    <input type="text" id="product_category" name="product_category" readonly class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="quantity mb-1">Kuantiti</label>

                    <div class="grid grid-cols-3 gap-3">
                        <button id="decrement_quantity" type="button" class="py-1 px-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-full border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100"><i class="fa-solid fa-minus"></i></button>
                        <input type="number" id="quantity" name="quantity" value="1" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <button id="increment_quantity" type="button" class="px-1 px-2 text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm text-center"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="button" id="add_to_order" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center me-2 mb-2">Simpan ke Orderan</button>
                </div>
            </div>
        </div>
        <div class="w-full mt-5 lg:mt-0 lg:w-[300px] bg-gray-100/80 border rounded-lg p-2">
            <form action="process_order.php" id="order_form" method="post" class="grid gap-3">

                <!-- list orderan -->
                <div class="p-2 border-2 rounded-lg">
                    <div id="order_list" class="w-full">
                        <p class="text-center text-gray-500" id="non_order">belum ada orderan</p>
                    </div>
                </div>

                <input type="hidden" id="order_details" name="order_details">

                <div class="flex items-center justify-between gap-2 truncate">
                    <label for="total_price">total : </label>
                    <span id="total_price_display">Rp. 0.00</span>
                    <input type="hidden" id="total_price" name="total_price" readonly class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- list orderan -->

                <div>
                    <label for="customer_name" class="block mb-1 text-sm font-medium text-gray-900">Nama Pembeli</label>
                    <input type="text" id="customer_name" required name="customer_name" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="is_membership" class="block mb-1 text-sm font-medium text-gray-900">Anggota Member ?</label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="is_membership" type="checkbox" value="" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div>
                    <label for="customer_email" class="block mb-1 text-sm font-medium text-gray-900">Email (opsional)</label>
                    <input type="text" id="customer_email" name="customer_email" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="customer_phone" class="block mb-1 text-sm font-medium text-gray-900">No. Telp (opsional)</label>
                    <input type="text" id="customer_phone" name="customer_phone" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="customer_address" class="block mb-1 text-sm font-medium text-gray-900">Alamat (opsional)</label>
                    <input type="text" id="customer_address" name="customer_address" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="mt-4 flex items-center justify-end">
                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center me-2 mb-2">Buat Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('product_id').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var price = selectedOption.getAttribute('data-price');
        var category = selectedOption.getAttribute('data-category');
        var code = selectedOption.getAttribute('data-code');
        console.log(selectedOption)
        document.getElementById('product_price').value = price;
        document.getElementById('product_category').value = category;
        document.getElementById('product_code').value = code;
    });

    document.getElementById('increment_quantity').addEventListener('click', function() {
        var quantityInput = document.getElementById('quantity');
        quantityInput.value = parseInt(quantityInput.value) + 1;
    });

    document.getElementById('decrement_quantity').addEventListener('click', function() {
        var quantityInput = document.getElementById('quantity');
        if (quantityInput.value > 1) {
            quantityInput.value = parseInt(quantityInput.value) - 1;
        }
    });

    document.getElementById('add_to_order').addEventListener('click', function() {
        var selectedProduct = document.getElementById('product_id').options[document.getElementById('product_id').selectedIndex];
        var productId = selectedProduct.value;
        var productName = selectedProduct.text;
        var productPrice = parseFloat(selectedProduct.getAttribute('data-price'));
        var productCategory = selectedProduct.getAttribute('data-category');
        var productCode = selectedProduct.getAttribute('data-code');
        var quantity = parseInt(document.getElementById('quantity').value);
        var orderList = document.getElementById('order_list');
        var orderItem = document.createElement('div');
        var defaultText = document.getElementById('non_order');

        if (!productId) {
            alert("Pilih produk terlebih dahulu!");
            return;
        }

        orderItem.className = 'bg-white p-2 border-2 rounded-lg flex items-center justify-between gap-2';
        orderItem.innerHTML = `<span>${productName}</span>
                               <span class="order-price">Rp. ${productPrice.toFixed(2)}</span>
                               <span class="order-quantity">x ${quantity}</span>`;
        orderList.appendChild(orderItem);
        defaultText.style.display = "none";
        updateTotalPrice();
        document.getElementById('quantity').value = 1;
    });

    function updateTotalPrice() {
        var orderList = document.getElementById('order_list');
        var total = 0;
        for (var i = 0; i < orderList.children.length; i++) {
            var item = orderList.children[i];
            var priceElement = item.querySelector('.order-price');
            var quantityElement = item.querySelector('.order-quantity');

            if (priceElement && quantityElement) {
                var price = parseFloat(priceElement.innerText.replace('Rp. ', ''));
                var quantity = parseInt(quantityElement.innerText.replace('x ', ''));
                total += price * quantity;
            }
        }
        document.getElementById('total_price').value = total.toFixed(2);
        document.getElementById('total_price_display').innerText = 'Rp. ' + total.toFixed(2);
    }

    document.getElementById('order_form').addEventListener('submit', function(event) {
        event.preventDefault();

        var orderList = document.getElementById('order_list');
        var orderDetails = [];

        if (orderList.children.length === 0) {
            alert("Tambahkan produk ke order terlebih dahulu!");
            return;
        }

        for (var i = 0; i < orderList.children.length; i++) {
            var item = orderList.children[i];
            var productName = item.querySelector('span:nth-child(1)');
            var productPrice = item.querySelector('.order-price');
            var productQuantity = item.querySelector('.order-quantity');

            if (productName && productPrice && productQuantity) {
                orderDetails.push({
                    name: productName.innerText,
                    price: parseFloat(productPrice.innerText.replace('Rp. ', '')),
                    quantity: parseInt(productQuantity.innerText.replace('x ', ''))
                });
            }
        }

        document.getElementById('order_details').value = JSON.stringify(orderDetails);
        console.log(orderList)
        
        // this.submit();
    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>