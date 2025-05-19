<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$identifiant = $data['identifiant_prof'] ?? null;
$prenomEleve = $data['prenom_etudiant'] ?? null;
$nomEleve = $data['nom_etudiant'] ?? null;
$valeurNote = $data['valeur_note'] ?? null;

if (!$prenomEleve || !$nomEleve  || !$valeurNote) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants.']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=poulpy2;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  
    $stmt = $pdo->prepare("SELECT id_etudiant FROM etudiant WHERE prenom_etudiant = ? AND nom_etudiant = ?");
    $stmt->execute([$prenomEleve, $nomEleve]);
    $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$etudiant) {
        echo json_encode(['success' => false, 'message' => "Élève non trouvé."]);
        exit;
    }

    $idEtudiant = $etudiant['id_etudiant'];
    $idProf = $identifiant;

    $stmt = $pdo->query("SELECT MAX(id_note) AS max_id FROM notes");
    $row = $stmt->fetch();
    $newIdNote = $row['max_id'] + 1;

    $stmt = $pdo->prepare("INSERT INTO notes (id_note, id_etudiant, id_prof, valeur_note) VALUES (?, ?, ?, ?)");
    $stmt->execute([$newIdNote, $idEtudiant, $idProf, $valeurNote]);

    echo json_encode(['success' => true]);
    exit;

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>
