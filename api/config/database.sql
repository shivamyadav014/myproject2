-- Create database
CREATE DATABASE IF NOT EXISTS anatomyar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Use database
USE anatomyar_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create models table
CREATE TABLE IF NOT EXISTS `models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `path` varchar(255) NOT NULL,
  `scale` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `rotation` varchar(50) NOT NULL,
  `description` text,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial model data, ignoring duplicates
INSERT IGNORE INTO models (name, path, scale, position, rotation, description, created) VALUES
('heart', 'models/scene.gltf', '0.8 0.8 0.8', '0 0.1 0', '0 0 0', 'Heart 3D model', NOW()),
('lung', 'models/lung/scene.gltf', '8 8 8', '0 0 0.3', '0 0 0', 'Lung 3D model', NOW()),
('brain', 'models/brain/scene.gltf', '2.5 2.5 2.5', '0 0 0', '0 180 0', 'Brain 3D model', NOW()),
('skeleton', 'models/skeleton/scene.gltf', '0.2 0.2 0.2', '0 -0.5 0', '0 0 0', 'Skeleton 3D model', NOW());

-- Create favorites table
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `model_name` varchar(50) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favorite` (`user_id`, `model_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create notes table
CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `model_name` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_model` (`user_id`, `model_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create model_views table for tracking usage statistics
CREATE TABLE IF NOT EXISTS `model_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `view_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_model_name` (`model_name`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create ratings table for model ratings
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `model_name` varchar(50) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_model_rating` (`user_id`, `model_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create quizzes table
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create quiz_questions table
CREATE TABLE IF NOT EXISTS `quiz_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_a` text NOT NULL,
  `option_b` text NOT NULL,
  `option_c` text NOT NULL,
  `option_d` text NOT NULL,
  `correct_answer` char(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `question_quiz_fk` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create quiz_results table
CREATE TABLE IF NOT EXISTS `quiz_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `date_taken` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `result_quiz_fk` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample quiz for Heart model
INSERT IGNORE INTO `quizzes` (`model_name`, `title`, `description`) VALUES
('heart', 'Heart Anatomy Quiz', 'Test your knowledge of the human heart');

-- Insert sample questions for the Heart quiz
INSERT IGNORE INTO `quiz_questions` (`quiz_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`) VALUES
(1, 'What chamber of the heart receives oxygenated blood from the lungs?', 'Right atrium', 'Left atrium', 'Right ventricle', 'Left ventricle', 'b'),
(1, 'Which blood vessel carries deoxygenated blood from the heart to the lungs?', 'Aorta', 'Pulmonary artery', 'Pulmonary vein', 'Superior vena cava', 'b'),
(1, 'What is the function of the mitral valve?', 'Controls blood flow between the left atrium and left ventricle', 'Controls blood flow between the right atrium and right ventricle', 'Controls blood flow from the left ventricle to the aorta', 'Controls blood flow from the right ventricle to the pulmonary artery', 'a');

-- Insert sample quiz for Brain model
INSERT IGNORE INTO `quizzes` (`model_name`, `title`, `description`) VALUES
('brain', 'Brain Anatomy Quiz', 'Test your knowledge of the human brain');

-- Insert sample questions for the Brain quiz
INSERT IGNORE INTO `quiz_questions` (`quiz_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`) VALUES
(2, 'Which part of the brain is responsible for balance and coordination?', 'Cerebrum', 'Cerebellum', 'Medulla oblongata', 'Hypothalamus', 'b'),
(2, 'The frontal lobe is responsible for which function?', 'Vision', 'Hearing', 'Problem solving', 'Balance', 'c'),
(2, 'What protects the brain from physical damage?', 'Blood-brain barrier', 'Skull', 'Dura mater', 'All of the above', 'd'); 