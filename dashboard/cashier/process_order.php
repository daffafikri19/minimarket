<?php
include '../../configs/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $is_membership = isset($_POST['is_membership']) ? 1 : 0;
    $customer_email = $_POST['customer_email'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
    $customer_address = $_POST['customer_address'] ?? '';
    $total_price = $_POST['total_price'];
    $order_details = json_decode($_POST['order_details'], true);

    $conn = get_db_connection();
    if ($conn === false) {
        die("Koneksi Database Gagal");
    }

    $stmt = $conn->prepare("INSERT INTO orders (name, membership, email, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sisss', $customer_name, $is_membership, $customer_email, $customer_phone, $customer_address);
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        $stmt_order_items = $conn->prepare("INSERT INTO order_detail (order_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
        foreach ($order_details as $item) {
            $stmt_order_items->bind_param('isii', $order_id, $item['id'], $item['quantity'], $item['total_price']);
            $stmt_order_items->execute();
        }
        
        echo "Order berhasil disimpan!";
    } else {
        echo "Gagal menyimpan order.";
    }

    $stmt->close();
    $conn->close();
}
?>
