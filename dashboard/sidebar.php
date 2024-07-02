<?php
$base_dir = dirname(__FILE__);
$base_url = "/minimarket/";
?>

<?php
// Mendapatkan URL aktif
function get_current_page()
{
    // Gunakan REQUEST_URI untuk mendapatkan URL path lengkap
    $request_uri = $_SERVER['REQUEST_URI'];

    // Gunakan parse_url untuk memisahkan path dari query
    $parsed_url = parse_url($request_uri);
    $path = $parsed_url['path'];

    // Mengembalikan path tanpa leading slash
    return trim($path, '/');
}

// Memeriksa apakah halaman aktif
function is_active($page)
{
    $current_page = get_current_page();

    // Bandingkan page dengan current_page untuk penandaan aktif
    return strpos($current_page, $page) !== false ? 'bg-blue-500 text-white' : '';
}
?>


<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white">
        <ul class="space-y-2 font-medium">
            <li>
                <a href="<?php echo $base_url; ?>dashboard/index.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-blue-600 hover:text-white group <?php echo is_active('dashboard/index.php'); ?>">
                    <i class="fa-solid fa-house"></i>
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>dashboard/cashier/index.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-blue-600 hover:text-white group <?php echo is_active('dashboard/cashier/index.php'); ?>">
                    <i class="fa-solid fa-list"></i>                    
                    <span class="ms-3">Kasir</span>
                </a>
            </li>
            <li>
                <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-blue-600 hover:text-white <?php echo is_active('dashboard/products'); ?>" aria-controls="dropdown-product" data-collapse-toggle="dropdown-product">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Produk</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                    </svg>
                </button>
                <ul id="dropdown-product" class="hidden py-2 space-y-2">
                    <li>
                        <a href="<?php echo $base_url; ?>dashboard/products/index.php" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-blue-600 hover:text-white <?php echo is_active('dashboard/products/index.php'); ?>">Daftar Produk</a>
                    </li>
                    <li>
                        <a href="<?php echo $base_url; ?>dashboard/products/categories.php" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-blue-600 hover:text-white <?php echo is_active('dashboard/products/categories.php'); ?>">Daftar Kategori</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>dashboard/suppliers/index.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-blue-600 group hover:text-white <?php echo is_active('dashboard/suppliers/index.php'); ?>">
                    <i class="fa-solid fa-link"></i>
                    <span class="flex-1 ms-3 whitespace-nowrap">Daftar Supplier</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>dashboard/purchase/index.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-blue-600 group hover:text-white <?php echo is_active('dashboard/purchase/index.php'); ?>">
                    <i class="fa-brands fa-shopify"></i>
                    <span class="flex-1 ms-3 whitespace-nowrap">Daftar Puchasing</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>dashboard/users/users.php" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-blue-600 group hover:text-white <?php echo is_active('dashboard/users/users.php'); ?>">
                    <i class="fa-solid fa-user"></i>
                    <span class="flex-1 ms-3 whitespace-nowrap">Daftar Pengguna</span>
                </a>
            </li>
        </ul>
    </div>
</aside>