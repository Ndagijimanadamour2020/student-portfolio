<?php
include_once "../../config/database.php";
include_once "../../includes/User.php";

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

if($_POST){
    $user->username = $_POST['username'];
    $user->email = $_POST['email'];
    $user->password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user->role = $_POST['role'];
    $user->status = $_POST['status'];

    if($user->create()){
        header("Location: list.php?message=User created successfully");
    } else{
        header("Location: create.php?error=Unable to create user");
    }
}
?>