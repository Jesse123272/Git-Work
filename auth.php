<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'github_auth';
$username = 'root';
$password = '';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Create users table if it doesn't exist
$createTableQuery = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

try {
    $pdo->exec($createTableQuery);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Table creation failed: ' . $e->getMessage()]);
    exit();
}

// Process requests
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

if (isset($input['action'])) {
    if ($input['action'] === 'signin') {
        // Sign in logic
        if (!isset($input['username']) || !isset($input['password'])) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            exit();
        }
        
        $username = trim($input['username']);
        $password = $input['password'];
        $rememberMe = isset($input['remember_me']) ? (bool)$input['remember_me'] : false;
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            exit();
        }
        
        // Check if user exists
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Start session if remember me is checked
                if ($rememberMe) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Sign in successful', 
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        
    } elseif ($input['action'] === 'signup') {
        // Sign up logic
        if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit();
        }
        
        $username = trim($input['username']);
        $email = trim($input['email']);
        $password = $input['password'];
        $termsAgreement = isset($input['terms_agreement']) ? (bool)$input['terms_agreement'] : false;
        
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit();
        }
        
        if (!$termsAgreement) {
            echo json_encode(['success' => false, 'message' => 'You must agree to the Terms of Service and Privacy Policy']);
            exit();
        }
        
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
            exit();
        }
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit();
        }
        
        // Validate username (alphanumeric with underscores and hyphens)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            echo json_encode(['success' => false, 'message' => 'Username can only contain letters, numbers, underscores, and hyphens']);
            exit();
        }
        
        // Check if username or email already exists
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingUser) {
                if ($existingUser['username'] === $username) {
                    echo json_encode(['success' => false, 'message' => 'Username already taken']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Email already registered']);
                }
                exit();
            }
            
            // Hash password and create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $result = $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword
            ]);
            
            if ($result) {
                $userId = $pdo->lastInsertId();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Account created successfully',
                    'user' => [
                        'id' => $userId,
                        'username' => $username,
                        'email' => $email
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create account']);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate entry)
                echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
}
?>