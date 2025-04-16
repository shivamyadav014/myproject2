<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection
include_once '../config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get request method
$request_method = $_SERVER["REQUEST_METHOD"];

// Get user ID from request
$data = json_decode(file_get_contents("php://input"));
$user_id = isset($data->user_id) ? $data->user_id : (isset($_GET['user_id']) ? $_GET['user_id'] : null);

// Check if user_id is provided
if(!$user_id) {
    http_response_code(400);
    echo json_encode(array("message" => "User ID is required."));
    exit();
}

// Process based on request method
switch($request_method) {
    // Get favorites for a user
    case 'GET':
        // Query to get user favorites
        $query = "SELECT f.id, f.model_name, f.date_added FROM favorites f 
                  WHERE f.user_id = ?";
        
        // Prepare statement
        $stmt = $db->prepare($query);
        
        // Bind parameter
        $stmt->bindParam(1, $user_id);
        
        // Execute query
        $stmt->execute();
        
        // Check if any favorites found
        if($stmt->rowCount() > 0) {
            // Favorites array
            $favorites_arr = array();
            
            // Fetch records
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $favorite_item = array(
                    "id" => $row['id'],
                    "model_name" => $row['model_name'],
                    "date_added" => $row['date_added']
                );
                
                array_push($favorites_arr, $favorite_item);
            }
            
            // Set response code - 200 OK
            http_response_code(200);
            
            // Send response
            echo json_encode($favorites_arr);
        } else {
            // Set response code - 404 Not found
            http_response_code(404);
            
            // No favorites found
            echo json_encode(array("message" => "No favorites found."));
        }
        break;
        
    // Add a favorite
    case 'POST':
        // Make sure model_name is provided
        if(!isset($data->model_name)) {
            http_response_code(400);
            echo json_encode(array("message" => "Model name is required."));
            exit();
        }
        
        // Query to check if favorite already exists
        $check_query = "SELECT id FROM favorites WHERE user_id = ? AND model_name = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(1, $user_id);
        $check_stmt->bindParam(2, $data->model_name);
        $check_stmt->execute();
        
        // If favorite already exists
        if($check_stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(array("message" => "Model is already in favorites."));
            exit();
        }
        
        // Query to insert favorite
        $query = "INSERT INTO favorites (user_id, model_name) VALUES (?, ?)";
        
        // Prepare statement
        $stmt = $db->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $data->model_name);
        
        // Execute query
        if($stmt->execute()) {
            // Set response code - 201 created
            http_response_code(201);
            
            // Send response
            echo json_encode(array("message" => "Favorite added successfully."));
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Send response
            echo json_encode(array("message" => "Unable to add favorite."));
        }
        break;
        
    // Remove a favorite
    case 'DELETE':
        // Make sure model_name is provided
        if(!isset($data->model_name)) {
            http_response_code(400);
            echo json_encode(array("message" => "Model name is required."));
            exit();
        }
        
        // Query to delete favorite
        $query = "DELETE FROM favorites WHERE user_id = ? AND model_name = ?";
        
        // Prepare statement
        $stmt = $db->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $data->model_name);
        
        // Execute query
        if($stmt->execute()) {
            // Set response code - 200 OK
            http_response_code(200);
            
            // Send response
            echo json_encode(array("message" => "Favorite removed successfully."));
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Send response
            echo json_encode(array("message" => "Unable to remove favorite."));
        }
        break;
        
    default:
        // Invalid request method
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?> 