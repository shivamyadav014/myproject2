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
$user_id = isset($data->user_id) ? $data->user_id : (isset($_GET['user_id']) ? $_GET['user_id'] : null);

// Check if user_id is provided
if(!$user_id) {
    http_response_code(400);
    echo json_encode(array("message" => "User ID is required."));
    exit();
}

// Process based on request method
switch($request_method) {
    // Get notes for a user
    case 'GET':
        $model_name = isset($_GET['model_name']) ? $_GET['model_name'] : null;
        
        // Base query to get notes
        $query = "SELECT id, model_name, content, last_updated FROM notes WHERE user_id = ?";
        $params = array($user_id);
        
        // If model name is specified, filter by it
        if($model_name) {
            $query .= " AND model_name = ?";
            $params[] = $model_name;
        }
        
        // Order by last updated
        $query .= " ORDER BY last_updated DESC";
        
        // Prepare statement
        $stmt = $db->prepare($query);
        
        // Bind parameters
        for($i = 0; $i < count($params); $i++) {
            $stmt->bindParam($i + 1, $params[$i]);
        }
        
        // Execute query
        $stmt->execute();
        
        // Check if any notes found
        if($stmt->rowCount() > 0) {
            // Notes array
            $notes_arr = array();
            
            // Fetch records
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $note_item = array(
                    "id" => $row['id'],
                    "model_name" => $row['model_name'],
                    "content" => $row['content'],
                    "last_updated" => $row['last_updated']
                );
                
                array_push($notes_arr, $note_item);
            }
            
            // Set response code - 200 OK
            http_response_code(200);
            
            // Send response
            echo json_encode($notes_arr);
        } else {
            // Set response code - 404 Not found
            http_response_code(404);
            
            // No notes found
            echo json_encode(array("message" => "No notes found."));
        }
        break;
        
    // Add a new note
    case 'POST':
        // Make sure model_name and content are provided
        if(!isset($data->model_name) || !isset($data->content)) {
            http_response_code(400);
            echo json_encode(array("message" => "Model name and content are required."));
            exit();
        }
        
        // Check if note for this model already exists
        $check_query = "SELECT id FROM notes WHERE user_id = ? AND model_name = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(1, $user_id);
        $check_stmt->bindParam(2, $data->model_name);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            // Note exists, update it
            $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
            $note_id = $row['id'];
            
            $query = "UPDATE notes SET content = ?, last_updated = NOW() WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $data->content);
            $stmt->bindParam(2, $note_id);
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Note updated successfully."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update note."));
            }
        } else {
            // Note doesn't exist, create new
            $query = "INSERT INTO notes (user_id, model_name, content) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->bindParam(2, $data->model_name);
            $stmt->bindParam(3, $data->content);
            
            if($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Note created successfully."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create note."));
            }
        }
        break;
        
    // Update a note
    case 'PUT':
        // Make sure model_name and content are provided
        if(!isset($data->model_name) || !isset($data->content)) {
            http_response_code(400);
            echo json_encode(array("message" => "Model name and content are required."));
            exit();
        }
        
        // Update note
        $query = "UPDATE notes SET content = ?, last_updated = NOW() 
                  WHERE user_id = ? AND model_name = ?";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $data->content);
        $stmt->bindParam(2, $user_id);
        $stmt->bindParam(3, $data->model_name);
        
        if($stmt->execute() && $stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(array("message" => "Note updated successfully."));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Note not found or no changes made."));
        }
        break;
    
    // Delete a note
    case 'DELETE':
        // Make sure model_name is provided
        if(!isset($data->model_name)) {
            http_response_code(400);
            echo json_encode(array("message" => "Model name is required."));
            exit();
        }
        
        // Delete note
        $query = "DELETE FROM notes WHERE user_id = ? AND model_name = ?";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $data->model_name);
        
        if($stmt->execute() && $stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(array("message" => "Note deleted successfully."));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Note not found."));
        }
        break;
        
    default:
        // Invalid request method
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?> 