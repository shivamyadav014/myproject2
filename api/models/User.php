<?php
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // Object properties
    public $id;
    public $email;
    public $password;
    public $created;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Sign up user
    public function signup() {
        // Check if email already exists
        if($this->emailExists()) {
            return false;
        }

        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    email=:email, password=:password, created=:created";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->created = date('Y-m-d H:i:s');

        // Bind values
        $stmt->bindParam(":email", $this->email);
        
        // Hash the password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":created", $this->created);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Login: check if email exists and password is correct
    public function login() {
        // Check if email exists
        $query = "SELECT id, email, password FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Bind email value
        $stmt->bindParam(1, $this->email);

        // Execute the query
        $stmt->execute();

        // Get number of rows
        $num = $stmt->rowCount();

        // If email exists, check password
        if($num > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if(password_verify($this->password, $row['password'])) {
                // Set values to object properties for easy access
                $this->id = $row['id'];
                $this->email = $row['email'];
                return true;
            }
        }

        return false;
    }

    // Check if email already exists
    function emailExists() {
        // Query to check if email exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Sanitize email
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind email value
        $stmt->bindParam(1, $this->email);

        // Execute the query
        $stmt->execute();

        // Get number of rows
        $num = $stmt->rowCount();

        // If email exists, return true
        if($num > 0) {
            return true;
        }

        // Return false if email does not exist
        return false;
    }
}
?> 