<?php
session_start();
session_destroy();

// Clear remember me cookie
setcookie('remember_token', '', time() - 3600, "/");

header('Location: index.html');
exit();
?>