<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Configuration de la base de données
$host = 'localhost';
$dbname = 'smartcontacts';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur de connexion à la base de données: ' . $e->getMessage()]);
    exit;
}

// Récupérer l'endpoint demandé
$endpoint = $_GET['endpoint'] ?? '';

switch ($endpoint) {
    case 'countries':
        getCountries($pdo);
        break;
    case 'operators':
        getOperators($pdo);
        break;
    case 'prefixes':
        getPrefixes($pdo);
        break;
    case 'search':
        searchByPhone($pdo);
        break;
    default:
        echo json_encode(['error' => 'Endpoint non trouvé', 'available_endpoints' => ['countries', 'operators', 'prefixes', 'search']]);
        break;
}

function getCountries($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM countries ORDER BY name ASC");
        $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'count' => count($countries), 'data' => $countries]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur lors de la récupération des pays: ' . $e->getMessage()]);
    }
}

function getOperators($pdo) {
    try {
        $country = $_GET['country'] ?? null;
        $sql = "SELECT o.*, c.name as country_name FROM operators o LEFT JOIN countries c ON o.country = c.iso";
        $params = [];
        
        if ($country) {
            $sql .= " WHERE o.country = ?";
            $params[] = $country;
        }
        
        $sql .= " ORDER BY o.name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'count' => count($operators), 'data' => $operators]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur lors de la récupération des opérateurs: ' . $e->getMessage()]);
    }
}

function getPrefixes($pdo) {
    try {
        $country = $_GET['country'] ?? null;
        $operator = $_GET['operator'] ?? null;
        
        $sql = "SELECT p.*, o.name as operator_name, o.brand, c.name as country_name 
                FROM prefixes p 
                LEFT JOIN operators o ON p.operatorId = o.id 
                LEFT JOIN countries c ON o.country = c.iso";
        $params = [];
        $conditions = [];
        
        if ($country) {
            $conditions[] = "o.country = ?";
            $params[] = $country;
        }
        
        if ($operator) {
            $conditions[] = "p.operatorId = ?";
            $params[] = $operator;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY p.dialCode ASC, p.prefix ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $prefixes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'count' => count($prefixes), 'data' => $prefixes]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur lors de la récupération des préfixes: ' . $e->getMessage()]);
    }
}

function searchByPhone($pdo) {
    try {
        $phone = $_GET['phone'] ?? '';
        
        if (empty($phone)) {
            echo json_encode(['error' => 'Numéro de téléphone requis']);
            return;
        }
        
        // Nettoyer le numéro (ne garder que les chiffres)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) < 3) {
            echo json_encode(['error' => 'Numéro de téléphone trop court']);
            return;
        }
        
        // Chercher le préfixe correspondant
        $sql = "SELECT p.*, o.name as operator_name, o.brand, o.logo, o.color, c.name as country_name, c.iso as country_iso
                FROM prefixes p 
                LEFT JOIN operators o ON p.operatorId = o.id 
                LEFT JOIN countries c ON o.country = c.iso
                WHERE ? LIKE CONCAT(p.dialCode, p.prefix, '%')
                ORDER BY LENGTH(p.dialCode) + LENGTH(p.prefix) DESC
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$phone]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'phone' => $phone,
                'data' => $result
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'phone' => $phone,
                'message' => 'Aucun opérateur trouvé pour ce numéro'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur lors de la recherche: ' . $e->getMessage()]);
    }
}
