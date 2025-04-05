<?php
session_start();
require_once '../../config/db-config.php';
require_once '../../includes/sessions-functions.php';

if (!is_logged_in(true)) {
    header("Location: ../../login.php?message=unauthorized");
    exit();
}
?>