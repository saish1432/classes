<?php
require_once '../config.php';

// Clear admin session
unset($_SESSION['admin_logged_in']);
session_destroy();

redirect('login.php');
?>