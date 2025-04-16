<?php
class Model {
    // Database connection and table name
    private $conn;
    private $table_name = "models";

    // Object properties
    public $id;
    public $name;
    public $path;
    public $scale;
    public $position;
    public $rotation;
    public $description;
    public $created;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all models
    public function read() {
        // Query to read all models
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Read single model
    public function readOne() {
        // Query to read one model
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind ID
        $stmt->bindParam(1, $this->id);

        // Execute query
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            // Set values to object properties
            $this->name = $row['name'];
            $this->path = $row['path'];
            $this->scale = $row['scale'];
            $this->position = $row['position'];
            $this->rotation = $row['rotation'];
            $this->description = $row['description'];
            return true;
        }
        
        return false;
    }

    // Get model by name (heart, lung, brain, skeleton)
    public function readByName() {
        // Query to read one model by name
        $query = "SELECT * FROM " . $this->table_name . " WHERE name = ? LIMIT 0,1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind name
        $stmt->bindParam(1, $this->name);

        // Execute query
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            // Set values to object properties
            $this->id = $row['id'];
            $this->path = $row['path'];
            $this->scale = $row['scale'];
            $this->position = $row['position'];
            $this->rotation = $row['rotation'];
            $this->description = $row['description'];
            return true;
        }
        
        return false;
    }

    // Create model
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    name=:name, 
                    path=:path, 
                    scale=:scale, 
                    position=:position, 
                    rotation=:rotation, 
                    description=:description,
                    created=:created";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->path = htmlspecialchars(strip_tags($this->path));
        $this->scale = htmlspecialchars(strip_tags($this->scale));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->rotation = htmlspecialchars(strip_tags($this->rotation));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->created = date('Y-m-d H:i:s');

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":path", $this->path);
        $stmt->bindParam(":scale", $this->scale);
        $stmt->bindParam(":position", $this->position);
        $stmt->bindParam(":rotation", $this->rotation);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":created", $this->created);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Update model
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                SET
                    name=:name, 
                    path=:path, 
                    scale=:scale, 
                    position=:position, 
                    rotation=:rotation, 
                    description=:description
                WHERE
                    id=:id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->path = htmlspecialchars(strip_tags($this->path));
        $this->scale = htmlspecialchars(strip_tags($this->scale));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->rotation = htmlspecialchars(strip_tags($this->rotation));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":path", $this->path);
        $stmt->bindParam(":scale", $this->scale);
        $stmt->bindParam(":position", $this->position);
        $stmt->bindParam(":rotation", $this->rotation);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?> 