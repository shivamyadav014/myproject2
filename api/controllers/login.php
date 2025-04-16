<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and user object files
include_once '../config/database.php';
include_once '../models/User.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate user object
$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Set user property values
$user->email = $data->email ?? '';
$user->password = $data->password ?? '';

// Login the user
if(
    !empty($user->email) &&
    !empty($user->password) &&
    $user->login()
){
    // Create array
    $user_arr = array(
        "status" => true,
        "message" => "Login successful",
        "id" => $user->id,
        "email" => $user->email
    );
}
else{
    // Create array
    $user_arr = array(
        "status" => false,
        "message" => "Email or password is incorrect"
    );
}

// Make JSON
echo json_encode($user_arr);
?> 