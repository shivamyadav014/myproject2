<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and model object files
include_once '../config/database.php';
include_once '../models/Model.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate model object
$model = new Model($db);

// Get model name from URL
$model_name = isset($_GET['name']) ? $_GET['name'] : '';

if(empty($model_name)) {
    // Return all models
    $stmt = $model->read();
    $num = $stmt->rowCount();

    if($num > 0) {
        // Models array
        $models_arr = array();
        $models_arr["models"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $model_item = array(
                "id" => $id,
                "name" => $name,
                "path" => $path,
                "scale" => $scale,
                "position" => $position,
                "rotation" => $rotation,
                "description" => $description
            );

            array_push($models_arr["models"], $model_item);
        }

        // Return models
        echo json_encode($models_arr);
    } else {
        // No models found
        echo json_encode(
            array("message" => "No models found.")
        );
    }
} else {
    // Get specific model
    $model->name = $model_name;

    if($model->readByName()) {
        // Create array
        $model_arr = array(
            "id" => $model->id,
            "name" => $model->name,
            "path" => $model->path,
            "scale" => $model->scale,
            "position" => $model->position,
            "rotation" => $model->rotation,
            "description" => $model->description
        );

        // Return model
        echo json_encode($model_arr);
    } else {
        // Default configurations if not found in DB
        $default_configs = array(
            'heart' => array(
                'path' => 'models/scene.gltf',
                'scale' => '0.8 0.8 0.8',
                'position' => '0 0.1 0',
                'rotation' => '0 0 0',
                'description' => 'Heart 3D model'
            ),
            'lung' => array(
                'path' => 'models/lung/scene.gltf',
                'scale' => '8 8 8',
                'position' => '0 0 0.3',
                'rotation' => '0 0 0',
                'description' => 'Lung 3D model'
            ),
            'brain' => array(
                'path' => 'models/brain/scene.gltf',
                'scale' => '2.5 2.5 2.5',
                'position' => '0 0 0',
                'rotation' => '0 180 0',
                'description' => 'Brain 3D model'
            ),
            'skeleton' => array(
                'path' => 'models/skeleton/scene.gltf',
                'scale' => '0.2 0.2 0.2',
                'position' => '0 -0.5 0',
                'rotation' => '0 0 0',
                'description' => 'Skeleton 3D model'
            )
        );
        
        if(isset($default_configs[$model_name])) {
            $config = $default_configs[$model_name];
            // Create array with default values
            $model_arr = array(
                "name" => $model_name,
                "path" => $config['path'],
                "scale" => $config['scale'],
                "position" => $config['position'],
                "rotation" => $config['rotation'],
                "description" => $config['description']
            );
            
            // Return model with default values
            echo json_encode($model_arr);
        } else {
            // Model not found
            echo json_encode(
                array("message" => "Model not found.")
            );
        }
    }
}
?> 