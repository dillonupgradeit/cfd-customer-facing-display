<?php
if (!isset($_SESSION)) {
    session_start();
    
}
error_log('logout.php');
session_destroy();
header('Location: login.php');
