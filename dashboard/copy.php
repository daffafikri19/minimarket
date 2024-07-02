<?php
$title = "";
ob_start();
?>

<div>
    
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>