<?php

namespace App\Controllers;

use App\Models\Prefix;
use App\Models\Country;
use App\Models\Operator;
use App\Models\SearchHistory;
use App\Models\User;

/**
 * Phone Controller
 * Handles phone number analysis and lookup
 */
class PhoneController extends Controller
{
    private $prefixModel;
    private $countryModel;
    private $operatorModel;
    private $searchHistoryModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->prefixModel = new Prefix();
        $this->countryModel = new Country();
        $this->operatorModel = new Operator();
        $this->searchHistoryModel = new SearchHistory();
        $this->userModel = new User();
    }

    /**
     * Analyze a single phone number
     */
    public function analyze()
    {
        $phone = $this->get('phone');

        if (empty($phone)) {
            return $this->json([
                'success' => false,
                'error' => 'Numéro de téléphone requis'
            ], 400);
        }

        // Check API quota if user is logged in
        if (isset($_SESSION['user_id'])) {
            $quotaCheck = $this->userModel->checkApiQuota($_SESSION['user_id']);
            
            if (!$quotaCheck['allowed']) {
                return $this->json([
                    'success' => false,
                    'error' => 'Quota API dépassé. Veuillez mettre à niveau votre abonnement.',
                    'quota_used' => $quotaCheck['used'],
                    'quota_limit' => $quotaCheck['limit']
                ], 429);
            }
        }

        $result = $this->prefixModel->searchByPhone($phone);

        $response = [
            'success' => true,
            'phone' => $phone,
            'data' => $result
        ];

        // Save to search history if user is logged in
        if (isset($_SESSION['user_id'])) {
            $this->searchHistoryModel->add($_SESSION['user_id'], $phone, $response, $_SERVER['REMOTE_ADDR'] ?? null);
            
            // Increment API quota
            $this->userModel->incrementApiQuota($_SESSION['user_id']);
        }

        if ($result) {
            return $this->json($response);
        }

        return $this->json([
            'success' => false,
            'phone' => $phone,
            'message' => 'Aucun opérateur trouvé pour ce numéro'
        ]);
    }

    /**
     * Batch analyze multiple phone numbers
     */
    public function batchAnalyze()
    {
        // Read JSON input
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);
        
        $phones = $data['phones'] ?? null;

        if (empty($phones) || !is_array($phones)) {
            return $this->json([
                'success' => false,
                'error' => 'Liste de numéros requise'
            ], 400);
        }

        // Check API quota if user is logged in
        if (isset($_SESSION['user_id'])) {
            $quotaCheck = $this->userModel->checkApiQuota($_SESSION['user_id']);
            
            // Check if user has enough quota for all phones
            $remainingQuota = $quotaCheck['limit'] - $quotaCheck['used'];
            if (count($phones) > $remainingQuota) {
                return $this->json([
                    'success' => false,
                    'error' => 'Quota API insuffisant pour traiter tous les numéros. Quota restant: ' . $remainingQuota,
                    'quota_used' => $quotaCheck['used'],
                    'quota_limit' => $quotaCheck['limit'],
                    'remaining' => $remainingQuota,
                    'requested' => count($phones)
                ], 429);
            }
        }

        $results = $this->prefixModel->batchSearch($phones);

        $stats = [
            'total' => count($phones),
            'identified' => 0,
            'not_identified' => 0
        ];

        foreach ($results as $result) {
            if ($result) {
                $stats['identified']++;
            } else {
                $stats['not_identified']++;
            }
        }

        $response = [
            'success' => true,
            'statistics' => $stats,
            'results' => $results
        ];

        // Save to search history if user is logged in
        if (isset($_SESSION['user_id'])) {
            foreach ($phones as $phone) {
                $phoneResult = $results[$phone] ?? null;
                $this->searchHistoryModel->add($_SESSION['user_id'], $phone, $phoneResult, $_SERVER['REMOTE_ADDR'] ?? null);
            }
            
            // Increment API quota for each phone analyzed
            $this->userModel->incrementApiQuota($_SESSION['user_id'], count($phones));
        }

        return $this->json($response);
    }

    /**
     * Get countries list
     */
    public function countries()
    {
        $countries = $this->countryModel->getAll();
        return $this->json([
            'success' => true,
            'count' => count($countries),
            'data' => $countries
        ]);
    }

    /**
     * Get operators list
     */
    public function operators()
    {
        $country = $this->get('country');
        
        if ($country) {
            $operators = $this->operatorModel->getByCountry($country);
        } else {
            $operators = $this->operatorModel->getAll();
        }

        return $this->json([
            'success' => true,
            'count' => count($operators),
            'data' => $operators
        ]);
    }

    /**
     * Get prefixes list
     */
    public function prefixes()
    {
        $country = $this->get('country');
        $operator = $this->get('operator');

        if ($country) {
            $prefixes = $this->prefixModel->getByCountry($country);
        } elseif ($operator) {
            $prefixes = $this->prefixModel->getByOperator($operator);
        } else {
            $prefixes = $this->prefixModel->getAll();
        }

        return $this->json([
            'success' => true,
            'count' => count($prefixes),
            'data' => $prefixes
        ]);
    }

    /**
     * Search operators
     */
    public function search()
    {
        $query = $this->get('q');

        if (empty($query)) {
            return $this->json([
                'success' => false,
                'error' => 'Terme de recherche requis'
            ], 400);
        }

        $operators = $this->operatorModel->search($query);

        return $this->json([
            'success' => true,
            'count' => count($operators),
            'data' => $operators
        ]);
    }

    /**
     * Get statistics
     */
    public function statistics()
    {
        $countryStats = $this->countryModel->getStatistics();
        $operatorStats = $this->operatorModel->getStatistics();
        $prefixStats = $this->prefixModel->getStatistics();

        return $this->json([
            'success' => true,
            'data' => [
                'countries' => $countryStats,
                'operators' => $operatorStats,
                'prefixes' => $prefixStats
            ]
        ]);
    }

    /**
     * Export search results to CSV
     */
    public function exportCsv()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $history = $this->searchHistoryModel->getUserHistory($_SESSION['user_id'], 1000);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="phone_analysis_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, ['Phone', 'Operator', 'Country', 'Brand', 'Dial Code', 'Prefix', 'Date']);

        // CSV Data
        foreach ($history as $item) {
            $result = json_decode($item['result'], true);
            if ($result && isset($result['data'])) {
                fputcsv($output, [
                    $item['phone'],
                    $result['data']['operator_name'] ?? 'N/A',
                    $result['data']['country_name'] ?? 'N/A',
                    $result['data']['brand'] ?? 'N/A',
                    $result['data']['dialCode'] ?? 'N/A',
                    $result['data']['prefix'] ?? 'N/A',
                    $item['created_at']
                ]);
            }
        }

        fclose($output);
        exit;
    }
}
