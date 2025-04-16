<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection
include_once '../config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get request method
$request_method = $_SERVER["REQUEST_METHOD"];

// Process based on request method
switch($request_method) {
    // Get view statistics
    case 'GET':
        // Query to get view counts for each model
        $query = "SELECT model_name, COUNT(*) as view_count 
                  FROM model_views 
                  GROUP BY model_name 
                  ORDER BY view_count DESC";
        
        // Prepare statement
        $stmt = $db->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Check if any data found
        if($stmt->rowCount() > 0) {
            // Statistics array
            $stats_arr = array();
            
            // Fetch records
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stat_item = array(
                    "model_name" => $row['model_name'],
                    "view_count" => $row['view_count']
                );
                
                array_push($stats_arr, $stat_item);
            }
            
            // Set response code - 200 OK
            http_response_code(200);
            
            // Send response
            echo json_encode($stats_arr);
        } else {
            // Set response code - 404 Not found
            http_response_code(404);
            
            // No data found
            echo json_encode(array("message" => "No view statistics found."));
        }
        break;
        
    // Record a model view
    case 'POST':
        // Get data from request
        $data = json_decode(file_get_contents("php://input"));
        
        // Make sure model_name is provided
        if(!isset($data->model_name)) {
            http_response_code(400);
            echo json_encode(array("message" => "Model name is required."));
            exit();
        }
        
        // Get user ID if available
        $user_id = isset($data->user_id) ? $data->user_id : null;
        
        // Query to insert view record
        if($user_id) {
            $query = "INSERT INTO model_views (model_name, user_id) VALUES (?, ?)";
            $params = array($data->model_name, $user_id);
        } else {
            $query = "INSERT INTO model_views (model_name) VALUES (?)";
            $params = array($data->model_name);
        }
        
        // Prepare statement
        $stmt = $db->prepare($query);
        
        // Bind parameters
        for($i = 0; $i < count($params); $i++) {
            $stmt->bindParam($i + 1, $params[$i]);
        }
        
        // Execute query
        if($stmt->execute()) {
            // Set response code - 201 created
            http_response_code(201);
            
            // Send response
            echo json_encode(array("message" => "View recorded successfully."));
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Send response
            echo json_encode(array("message" => "Unable to record view."));
        }
        break;
        
    default:
        // Invalid request method
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?> 