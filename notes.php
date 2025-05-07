<?php
// Allow requests from Vite dev server (adjust if needed)
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Respond early to preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

$role = $input['role'] ?? '';
$identifiant = $input['identifiant'] ?? '';
$motDePasse = $input['motDePasse'] ?? '';

// Validate input
if (!$role || !$identifiant || !$motDePasse) {
    echo json_encode(['status' => 'error', 'message' => 'Paramètres manquants.']);
    exit;
}

// DB config
$host = 'localhost';
$dbname = 'poulpy2';
$user = 'root';
$pass = ''; // update if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $table = ($role === 'professeur') ? 'professeurs' : 'eleves';

    $stmt = $pdo->prepare("SELECT * FROM $table WHERE identifiant = :identifiant AND mot_de_passe = :mot_de_passe");
    $stmt->execute([
        ':identifiant' => $identifiant,
        ':mot_de_passe' => $motDePasse
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['status' => 'success', 'message' => "Bienvenue $role!", 'user' => $user]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identifiant ou mot de passe invalide.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données.', 'details' => $e->getMessage()]);
}
?>
