<?php
session_start();
$base_dir = dirname(__FILE__);
$base_url = "/minimarket/";
$timeout_duration = 800; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // session timeout
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$title = "Dashboard";
ob_start();
?>

<h1>Wellcome To MiniMarket Dashboard <?php echo htmlspecialchars($_SESSION['username']); ?></h1>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
