<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection
include_once '../config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get request method
$request_method = $_SERVER["REQUEST_METHOD"];

// Get data from request
$data = json_decode(file_get_contents("php://input"));

// Process based on request method
switch($request_method) {
    // Get ratings
    case 'GET':
        $model_name = isset($_GET['model_name']) ? $_GET['model_name'] : null;
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        
        // Base query to get ratings
        if($user_id && $model_name) {
            // Get specific user rating for a model
            $query = "SELECT r.id, r.model_name, r.rating, r.comment, r.date_added FROM ratings r 
                      WHERE r.user_id = ? AND r.model_name = ?";
            $params = array($user_id, $model_name);
        } elseif($model_name) {
            // Get average rating and count for a specific model
            $query = "SELECT
                        model_name,
                        AVG(rating) as average_rating,
                        COUNT(*) as rating_count
                      FROM ratings
                      WHERE model_name = ?
                      GROUP BY model_name";
            $params = array($model_name);
        } else {
            // Get average ratings for all models
            $query = "SELECT 
                        model_name,
                        AVG(rating) as average_rating,
                        COUNT(*) as rating_count
                      FROM ratings
                      GROUP BY model_name
                      ORDER BY average_rating DESC";
            $params = array();
        }
        
        // Prepare statement
        $stmt = $db->prepare($query);
        
        // Bind parameters
        for($i = 0; $i < count($params); $i++) {
            $stmt->bindParam($i + 1, $params[$i]);
        }
        
        // Execute query
        $stmt->execute();
        
        // Check if any ratings found
        if($stmt->rowCount() > 0) {
            // Ratings array
            $ratings_arr = array();
            
            // Fetch records
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if(isset($row['average_rating'])) {
                    $rating_item = array(
                        "model_name" => $row['model_name'],
                        "average_rating" => round(floatval($row['average_rating']), 1),
                        "rating_count" => $row['rating_count']
                    );
                } else {
                    $rating_item = array(
                        "id" => $row['id'],
                        "model_name" => $row['model_name'],
                        "rating" => $row['rating'],
                        "comment" => $row['comment'],
                        "date_added" => $row['date_added']
                    );
                }
                
                array_push($ratings_arr, $rating_item);
            }
            
            // Set response code - 200 OK
            http_response_code(200);
            
            // Send response
            echo json_encode($ratings_arr);
        } else {
            // Set response code - 404 Not found
            http_response_code(404);
            
            // No ratings found
            echo json_encode(array("message" => "No ratings found."));
        }
        break;
        
    // Submit a rating
    case 'POST':
        // Make sure required fields are provided
        if(!isset($data->user_id) || !isset($data->model_name) || !isset($data->rating)) {
            http_response_code(400);
            echo json_encode(array("message" => "User ID, model name, and rating are required."));
            exit();
        }
        
        // Validate rating (1-5)
        if($data->rating < 1 || $data->rating > 5) {
            http_response_code(400);
            echo json_encode(array("message" => "Rating must be between 1 and 5."));
            exit();
        }
        
        // Check if rating already exists
        $check_query = "SELECT id FROM ratings WHERE user_id = ? AND model_name = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(1, $data->user_id);
        $check_stmt->bindParam(2, $data->model_name);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            // Rating exists, update it
            $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
            $rating_id = $row['id'];
            
            $query = "UPDATE ratings SET rating = ?, comment = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->rating);
            $comment = isset($data->comment) ? $data->comment : null;
            $stmt->bindParam(2, $comment);
            $stmt->bindParam(3, $rating_id);
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Rating updated successfully."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update rating."));
            }
        } else {
            // Rating doesn't exist, create new
            $query = "INSERT INTO ratings (user_id, model_name, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->user_id);
            $stmt->bindParam(2, $data->model_name);
            $stmt->bindParam(3, $data->rating);
            $comment = isset($data->comment) ? $data->comment : null;
            $stmt->bindParam(4, $comment);
            
            if($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Rating submitted successfully."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to submit rating."));
            }
        }
        break;
        
    // Delete a rating
    case 'DELETE':
        // Make sure user_id and model_name are provided
        if(!isset($data->user_id) || !isset($data->model_name)) {
            http_response_code(400);
            echo json_encode(array("message" => "User ID and model name are required."));
            exit();
        }
        
        // Delete rating
        $query = "DELETE FROM ratings WHERE user_id = ? AND model_name = ?";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $data->user_id);
        $stmt->bindParam(2, $data->model_name);
        
        if($stmt->execute() && $stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(array("message" => "Rating deleted successfully."));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Rating not found."));
        }
        break;
        
    default:
        // Invalid request method
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?> 