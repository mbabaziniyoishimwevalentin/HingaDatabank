<?php
// agrifinance-platform/api/get_interest_rates.php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_POST['institution_id']) || empty($_POST['institution_id'])) {
    echo json_encode(['success' => false, 'message' => 'Institution ID is required']);
    exit();
}

try {
    $db = (new Database())->getConnection();
    $institution_id = $_POST['institution_id'];
    
    // Fetch interest rates for the selected institution
    $stmt = $db->prepare("SELECT min_amount, max_amount, interest_rate_permonth, created_at 
                          FROM loan_interest_calculations 
                          WHERE financial_institutions_id = ? 
                          ORDER BY min_amount ASC");
    $stmt->execute([$institution_id]);
    $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rates)) {
        echo json_encode(['success' => false, 'message' => 'No interest rates found for this institution']);
        exit();
    }
    
    // Format the data for response
    $formattedRates = [];
    foreach ($rates as $rate) {
        $formattedRates[] = [
            'min_amount' => (float)$rate['min_amount'],
            'max_amount' => (float)$rate['max_amount'],
            'interest_rate_permonth' => (float)$rate['interest_rate_permonth'],
            'created_at' => $rate['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'rates' => $formattedRates,
        'count' => count($formattedRates)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>