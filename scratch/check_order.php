<?php
$conn = new mysqli('localhost', 'root', 'tJ8ow?a1bOEhGsef;vX&', 'ysm');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$orders = $conn->query("SELECT so.id, so.increment_id, soi.item_id, soi.product_id, soi.product_name, soi.product_type 
    FROM sales_order so 
    JOIN sales_order_items soi ON soi.order_id = so.id 
    WHERE soi.product_name LIKE '%T-Shirt%' 
    ORDER BY so.id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

echo "SALES ORDER ITEMS:\n";
print_r($orders);

if (!empty($orders)) {
    $order_id = $orders[0]['id'];
    echo "\nB2B ORDERS for webshop_order_id = $order_id:\n";
    $b2b = $conn->query("SELECT order_id, webshop_order_id, increment_id, order_barcode, publisher_id, status FROM b2b_orders WHERE webshop_order_id = '$order_id'")->fetch_all(MYSQLI_ASSOC);
    print_r($b2b);

    echo "\nB2B ORDER ITEMS for these b2b orders:\n";
    if (!empty($b2b)) {
        $b2b_ids = array_column($b2b, 'order_id');
        $b2b_items = $conn->query("SELECT item_id, order_id, product_id, parent_product_id, name, item_status FROM b2b_order_items WHERE order_id IN (" . implode(',', $b2b_ids) . ")")->fetch_all(MYSQLI_ASSOC);
        print_r($b2b_items);
    }
}
