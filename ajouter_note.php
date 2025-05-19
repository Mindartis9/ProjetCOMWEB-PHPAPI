<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type"); //Méthodes implémenté pour garantir certains types de fonctionement pour le type et l'origine
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$identifiant = $data['identifiant_prof'] ?? null;
$prenomEleve = $data['prenom_etudiant'] ?? null;
$nomEleve = $data['nom_etudiant'] ?? null;
$valeurNote = $data['valeur_note'] ?? null; // Recupération des 4 valeurs envoyé depuis le front

if (!$prenomEleve || !$nomEleve  || !$valeurNote) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants.']);
    exit;
}

$host = 'localhost';
$dbname = 'mmarchais002';
$user = 'mmarchais002';
$pass = 'IWILLSEEKMYTRUTH12*'; // Identifiants de la base de données

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  
    $stmt = $pdo->prepare("SELECT id_etudiant FROM etudiant WHERE prenom_etudiant = ? AND nom_etudiant = ?"); // Préparation de la requête pour récupérer l'id de l'élève
    $stmt->execute([$prenomEleve, $nomEleve]);
    $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$etudiant) {
        echo json_encode(['success' => false, 'message' => "Élève non trouvé."]);
        exit;
    }

    $idEtudiant = $etudiant['id_etudiant'];
    $idProf = $identifiant;

    $stmt = $pdo->query("SELECT MAX(id_note) AS max_id FROM notes"); // Préparation de la requête pour récupérer l'id des notes pour créer une note unique
    $row = $stmt->fetch();
    $newIdNote = $row['max_id'] + 1;

    $stmt = $pdo->prepare("INSERT INTO notes (id_note, id_etudiant, id_prof, valeur_note) VALUES (?, ?, ?, ?)"); // Préparation de la requête pour insérer la note
    $stmt->execute([$newIdNote, $idEtudiant, $idProf, $valeurNote]);

    echo json_encode(['success' => true]);
    exit;

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]); // En cas d'erreur de connexion ou d'exécution de la requête
    exit;
}
?>
