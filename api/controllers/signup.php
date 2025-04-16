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

// Check if email is valid
if(!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
    // Create array
    $user_arr = array(
        "status" => false,
        "message" => "Invalid email format"
    );
    echo json_encode($user_arr);
    exit();
}

// Check if any of the fields are empty
if(empty($user->email) || empty($user->password)) {
    // Create array
    $user_arr = array(
        "status" => false,
        "message" => "Email and password cannot be empty"
    );
    echo json_encode($user_arr);
    exit();
}

// Create the user
if($user->signup()) {
    // Create array
    $user_arr = array(
        "status" => true,
        "message" => "User created successfully"
    );
}
else {
    // User already exists or database error
    if($user->emailExists()) {
        $user_arr = array(
            "status" => false,
            "message" => "Email already exists"
        );
    } else {
        $user_arr = array(
            "status" => false,
            "message" => "Unable to create user"
        );
    }
}

// Make JSON
echo json_encode($user_arr);
?> 