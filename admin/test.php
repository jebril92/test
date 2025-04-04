<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Test de fonctionnement";
var_dump($_SESSION);
?>