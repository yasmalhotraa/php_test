<?php
// Basic Authentication (you can customize with real user authentication later)
$valid_username = 'admin';  // Set a default username
$valid_password = 'password123';  // Set a default password

function authenticate() {
    global $valid_username, $valid_password;
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        if (preg_match('/Basic (.+)/', $auth, $matches)) {
            $credentials = base64_decode($matches[1]);
            list($username, $password) = explode(':', $credentials);

            if ($username === $valid_username && $password === $valid_password) {
                return true;
            }
        }
    }
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

// Connect to the database
$host = '127.0.0.1';
$db = 'hellofresh';
$user = 'hellofresh';
$pass = 'hellofresh';
$charset = 'utf8mb4';

$dsn = "pgsql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle different request types
$requestMethod = $_SERVER["REQUEST_METHOD"];
$requestUri = $_SERVER["REQUEST_URI"];
$uriParts = explode('/', $requestUri);

// Apply authentication for sensitive routes (POST, PUT, DELETE)
if (in_array($requestMethod, ['POST', 'PUT', 'DELETE'])) {
    authenticate();
}

if ($uriParts[1] == 'recipes') {
    switch ($requestMethod) {
        case 'GET':
            if (isset($uriParts[2])) {
                // Fetch single recipe by ID
                $stmt = $pdo->prepare('SELECT * FROM recipes WHERE id = ?');
                $stmt->execute([$uriParts[2]]);
                $recipe = $stmt->fetch();
                echo json_encode($recipe);
            } else {
                // Fetch all recipes
                $stmt = $pdo->query('SELECT * FROM recipes');
                $recipes = $stmt->fetchAll();
                echo json_encode($recipes);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['name'], $data['prep_time'], $data['difficulty'], $data['vegetarian'])) {
                echo json_encode(['message' => 'Invalid or missing data']);
                break;
            }
            
            // Debugging: Check the data being sent
            var_dump($data);

            // Prepare the INSERT statement
            $stmt = $pdo->prepare('INSERT INTO recipes (name, prep_time, difficulty, vegetarian) VALUES (?, ?, ?, ?)');

            // Debugging: Check the SQL and data being inserted
            echo "Inserting: " . json_encode([$data['name'], $data['prep_time'], $data['difficulty'], $data['vegetarian']]);

            $executeResult = $stmt->execute([$data['name'], $data['prep_time'], $data['difficulty'], $data['vegetarian']]);
            
            if ($executeResult) {
                echo "Insert Successful!";
            } else {
                echo "Insert Failed!";
            }
            break;

        case 'PUT':
            if (isset($uriParts[2])) {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!$data || !isset($data['name'], $data['prep_time'], $data['difficulty'], $data['vegetarian'])) {
                    echo json_encode(['message' => 'Invalid or missing data']);
                    break;
                }
                
                // Prepare the UPDATE statement
                $stmt = $pdo->prepare('UPDATE recipes SET name = ?, prep_time = ?, difficulty = ?, vegetarian = ? WHERE id = ?');
                $stmt->execute([$data['name'], $data['prep_time'], $data['difficulty'], $data['vegetarian'], $uriParts[2]]);
                
                if ($stmt->rowCount()) {
                    echo json_encode(['message' => 'Recipe updated successfully!']);
                } else {
                    echo json_encode(['message' => 'No recipe found with the given ID or no changes made.']);
                }
            }
            break;

        case 'DELETE':
            if (isset($uriParts[2])) {
                // Delete the recipe by ID
                $stmt = $pdo->prepare('DELETE FROM recipes WHERE id = ?');
                $stmt->execute([$uriParts[2]]);
                
                if ($stmt->rowCount()) {
                    echo json_encode(['message' => 'Recipe deleted successfully!']);
                } else {
                    echo json_encode(['message' => 'No recipe found with the given ID.']);
                }
            }
            break;

        default:
            echo json_encode(['message' => 'Method Not Allowed']);
            break;
    }
} else {
    echo json_encode(['message' => 'Invalid Endpoint']);
}
