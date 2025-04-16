# AnatomyAR Pro

An augmented reality application for learning human anatomy with interactive 3D models.

## Features

- User authentication system
- Interactive 3D anatomical models in AR
- Button-based controls for rotation and manipulation
- Responsive design for various device sizes
- PHP backend with MySQL database support

## Setup Instructions

### Prerequisites

- Web server with PHP 7.4+ (Apache or Nginx recommended)
- MySQL database server
- Modern web browser with AR support

### Database Setup

1. Create a MySQL database for the application
2. Import the database schema:
   ```
   mysql -u username -p < api/config/database.sql
   ```
   Or run the SQL commands in the `api/config/database.sql` file using a tool like phpMyAdmin

### Configuration

1. Update the database connection details in `api/config/database.php`:
   ```php
   private $host = "localhost";      // Your database host
   private $db_name = "anatomyar_db"; // Your database name
   private $username = "root";        // Your database username
   private $password = "";            // Your database password
   ```

### Deployment

1. Copy all files to your web server's document root or a subdirectory
2. Ensure the web server has write permissions to the `api` directory
3. Access the application through your web browser

## Model Configuration

The application uses a database to store model configurations. The default models are:

- Heart
- Lung
- Brain
- Skeleton

Each model has the following properties:
- Path: Path to the 3D model file
- Scale: Size of the model in 3D space
- Position: Position of the model in 3D space
- Rotation: Initial rotation of the model

## Usage

1. Register a new account or log in with existing credentials
2. Select an anatomical model from the dashboard
3. Allow camera access when prompted
4. Point your camera at the Hiro marker
5. Use the on-screen buttons to rotate and manipulate the model

## AR Markers

This application uses the standard Hiro marker for AR tracking. You can print the Hiro marker from here:
[Hiro Marker](https://jeromeetienne.github.io/AR.js/data/images/HIRO.jpg)

## File Structure

```
├── css/
│   └── style.css
├── js/
│   └── auth.js
├── models/
│   ├── heart.gltf
│   ├── brain.gltf
│   └── skeleton.gltf
├── login.html
├── signup.html
├── dashboard.html
└── README.md
```

## Technologies Used

- HTML5
- CSS3
- JavaScript
- A-Frame
- AR.js

## Note

This application uses localStorage for user authentication. In a production environment, you would want to implement proper backend authentication and database storage.

## License

MIT License 