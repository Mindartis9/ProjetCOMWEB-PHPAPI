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

$role = $data['role'] ?? '';
$identifiant = $data['identifiant'] ?? '';
$motDePasse = $data['motDePasse'] ?? '';

if (!$role || !$identifiant || !$motDePasse) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants.']);
    exit;
}

$host = 'localhost';
$dbname = 'poulpy2';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($role === "eleve") {
        $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_etudiant = :identifiant AND mdp_etudiant = :mot_de_passe");
        $stmt->execute([
            ':identifiant' => $identifiant,
            ':mot_de_passe' => $motDePasse
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $stmtNotes = $pdo->prepare("SELECT n.valeur_note AS note, p.nom_matiere FROM notes n JOIN professeur p JOIN etudiant e ON n.id_prof = p.id_prof WHERE n.id_etudiant = ?");
            $stmtNotes->execute([$user['id_etudiant']]);
            $notes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'nom_etudiant' => $user['nom_etudiant'],
                'notes' => $notes
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Identifiants élève incorrects.']);
            exit;
        }
    }

    if ($role === "professeur") {
        $stmt = $pdo->prepare("SELECT * FROM professeur WHERE id_prof = :identifiant AND mdp_prof = :mot_de_passe");
        $stmt->execute([
            ':identifiant' => $identifiant,
            ':mot_de_passe' => $motDePasse
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $stmtNotes = $pdo->prepare("
            SELECT e.nom_etudiant AS nomEleve, e.prenom_etudiant AS prenomEleve, n.valeur_note AS note, e.id_classe AS classe 
            FROM notes n
            JOIN etudiant e ON n.id_etudiant = e.id_etudiant
            JOIN professeur p ON n.id_prof = p.id_prof
            WHERE n.id_prof = ?
            ");
            $stmtNotes->execute([$user['id_prof']]);
            $notes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'nom_prof' => $user['nom_prof'],
                'notes' => $notes
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Identifiants professeur incorrects.']);
            exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'Rôle invalide.']);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données : ' . $e->getMessage()
    ]);
    exit;
}
?>
