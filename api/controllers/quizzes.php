<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
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
    // Get quizzes or quiz questions
    case 'GET':
        $quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;
        $model_name = isset($_GET['model_name']) ? $_GET['model_name'] : null;
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        
        if($quiz_id) {
            // Get questions for a specific quiz
            $query = "SELECT id, question, option_a, option_b, option_c, option_d FROM quiz_questions WHERE quiz_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $quiz_id);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $questions_arr = array();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $question_item = array(
                        "id" => $row['id'],
                        "question" => $row['question'],
                        "options" => array(
                            "a" => $row['option_a'],
                            "b" => $row['option_b'],
                            "c" => $row['option_c'],
                            "d" => $row['option_d']
                        )
                    );
                    
                    array_push($questions_arr, $question_item);
                }
                
                // Also get quiz info
                $quiz_query = "SELECT title, description FROM quizzes WHERE id = ?";
                $quiz_stmt = $db->prepare($quiz_query);
                $quiz_stmt->bindParam(1, $quiz_id);
                $quiz_stmt->execute();
                $quiz_info = $quiz_stmt->fetch(PDO::FETCH_ASSOC);
                
                // Get user's previous score, if exists
                $user_score = null;
                if($user_id) {
                    $score_query = "SELECT score, total_questions, date_taken 
                                    FROM quiz_results 
                                    WHERE user_id = ? AND quiz_id = ?
                                    ORDER BY date_taken DESC
                                    LIMIT 1";
                    $score_stmt = $db->prepare($score_query);
                    $score_stmt->bindParam(1, $user_id);
                    $score_stmt->bindParam(2, $quiz_id);
                    $score_stmt->execute();
                    
                    if($score_stmt->rowCount() > 0) {
                        $user_score = $score_stmt->fetch(PDO::FETCH_ASSOC);
                    }
                }
                
                // Create response object
                $response = array(
                    "quiz_id" => $quiz_id,
                    "title" => $quiz_info['title'],
                    "description" => $quiz_info['description'],
                    "questions" => $questions_arr,
                    "question_count" => count($questions_arr),
                    "user_score" => $user_score
                );
                
                http_response_code(200);
                echo json_encode($response);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No questions found."));
            }
        } else if($model_name) {
            // Get available quizzes for a model
            $query = "SELECT id, title, description FROM quizzes WHERE model_name = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $model_name);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $quizzes_arr = array();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Count questions
                    $count_query = "SELECT COUNT(*) as question_count FROM quiz_questions WHERE quiz_id = ?";
                    $count_stmt = $db->prepare($count_query);
                    $count_stmt->bindParam(1, $row['id']);
                    $count_stmt->execute();
                    $count_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Get user's previous score, if exists
                    $user_score = null;
                    if($user_id) {
                        $score_query = "SELECT score, total_questions, date_taken 
                                        FROM quiz_results 
                                        WHERE user_id = ? AND quiz_id = ?
                                        ORDER BY date_taken DESC
                                        LIMIT 1";
                        $score_stmt = $db->prepare($score_query);
                        $score_stmt->bindParam(1, $user_id);
                        $score_stmt->bindParam(2, $row['id']);
                        $score_stmt->execute();
                        
                        if($score_stmt->rowCount() > 0) {
                            $user_score = $score_stmt->fetch(PDO::FETCH_ASSOC);
                        }
                    }
                    
                    $quiz_item = array(
                        "id" => $row['id'],
                        "title" => $row['title'],
                        "description" => $row['description'],
                        "question_count" => $count_row['question_count'],
                        "user_score" => $user_score
                    );
                    
                    array_push($quizzes_arr, $quiz_item);
                }
                
                http_response_code(200);
                echo json_encode($quizzes_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No quizzes found for this model."));
            }
        } else {
            // Get all available quizzes
            $query = "SELECT id, model_name, title, description FROM quizzes";
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $quizzes_arr = array();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Count questions
                    $count_query = "SELECT COUNT(*) as question_count FROM quiz_questions WHERE quiz_id = ?";
                    $count_stmt = $db->prepare($count_query);
                    $count_stmt->bindParam(1, $row['id']);
                    $count_stmt->execute();
                    $count_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $quiz_item = array(
                        "id" => $row['id'],
                        "model_name" => $row['model_name'],
                        "title" => $row['title'],
                        "description" => $row['description'],
                        "question_count" => $count_row['question_count']
                    );
                    
                    array_push($quizzes_arr, $quiz_item);
                }
                
                http_response_code(200);
                echo json_encode($quizzes_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No quizzes found."));
            }
        }
        break;
        
    // Submit quiz results
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Make sure required fields are provided
        if(!isset($data->user_id) || !isset($data->quiz_id) || !isset($data->answers) || !isset($data->score)) {
            http_response_code(400);
            echo json_encode(array("message" => "User ID, quiz ID, answers, and score are required."));
            exit();
        }
        
        // Get total number of questions
        $count_query = "SELECT COUNT(*) as question_count FROM quiz_questions WHERE quiz_id = ?";
        $count_stmt = $db->prepare($count_query);
        $count_stmt->bindParam(1, $data->quiz_id);
        $count_stmt->execute();
        $count_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
        $total_questions = $count_row['question_count'];
        
        // Insert results
        $query = "INSERT INTO quiz_results (user_id, quiz_id, score, total_questions) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $data->user_id);
        $stmt->bindParam(2, $data->quiz_id);
        $stmt->bindParam(3, $data->score);
        $stmt->bindParam(4, $total_questions);
        
        if($stmt->execute()) {
            $result_id = $db->lastInsertId();
            
            // Get the correct answers for feedback
            $quiz_query = "SELECT id, correct_answer FROM quiz_questions WHERE quiz_id = ?";
            $quiz_stmt = $db->prepare($quiz_query);
            $quiz_stmt->bindParam(1, $data->quiz_id);
            $quiz_stmt->execute();
            
            $correct_answers = array();
            while($row = $quiz_stmt->fetch(PDO::FETCH_ASSOC)) {
                $correct_answers[$row['id']] = $row['correct_answer'];
            }
            
            $response = array(
                "message" => "Quiz results saved successfully.",
                "result_id" => $result_id,
                "score" => $data->score,
                "total_questions" => $total_questions,
                "correct_answers" => $correct_answers
            );
            
            http_response_code(201);
            echo json_encode($response);
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to save quiz results."));
        }
        break;
        
    default:
        // Invalid request method
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?> 