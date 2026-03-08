<?php 
session_start();
include 'header.php';

if ($role!==0) {
    header("Location: emp_profile.php");
} else {
    header("Location: admin_profile.php");

}


?>