
<?php
function require_login(){
if(!isset($_SESSION['user'])){ header("Location: index.php"); exit; }
}

function require_keuangan(){
require_login();
if($_SESSION['user']['role']!='KEUANGAN'){
die("Akses ditolak");
}
}
?>
