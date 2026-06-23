<?php

namespace App\Models;

use App\Models\Database;

/**
 * Spam Detection Model
 * Handles spam and fraud detection for phone numbers
 */
class SpamDetection
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Check if a phone number is flagged as spam
     */
    public function isSpam($phone)
    {
        $normalizedPhone = $this->normalizePhone($phone);
        
        $sql = "SELECT * FROM spam_numbers WHERE phone = ? AND is_active = 1";
        $result = $this->db->fetchOne($sql, [$normalizedPhone]);
        
        return $result !== false;
    }

    /**
     * Get spam information for a phone number
     */
    public function getSpamInfo($phone)
    {
        $normalizedPhone = $this->normalizePhone($phone);
        
        $sql = "SELECT * FROM spam_numbers WHERE phone = ?";
        return $this->db->fetchOne($sql, [$normalizedPhone]);
    }

    /**
     * Add a spam report
     */
    public function reportSpam($phone, $reporterId, $reason, $category = 'spam')
    {
        $normalizedPhone = $this->normalizePhone($phone);
        
        $sql = "INSERT INTO spam_reports (phone, reporter_id, reason, category, status) 
                VALUES (?, ?, ?, ?, 'pending')";
        
        $result = $this->db->query($sql, [$normalizedPhone, $reporterId, $reason, $category]);
        
        if ($result) {
            return ['success' => true, 'report_id' => $this->db->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'Erreur lors du signalement'];
    }

    /**
     * Analyze phone number for fraud indicators
     */
    public function analyzeForFraud($phone)
    {
        $indicators = [
            'is_suspicious' => false,
            'risk_score' => 0,
            'indicators' => []
        ];

        // Check for premium rate numbers
        if ($this->isPremiumRate($phone)) {
            $indicators['is_suspicious'] = true;
            $indicators['risk_score'] += 30;
            $indicators['indicators'][] = 'Numéro surtaxé détecté';
        }

        // Check for known spam patterns
        if ($this->matchesSpamPattern($phone)) {
            $indicators['is_suspicious'] = true;
            $indicators['risk_score'] += 40;
            $indicators['indicators'][] = 'Correspond à un pattern de spam connu';
        }

        // Check if number is in spam database
        if ($this->isSpam($phone)) {
            $indicators['is_suspicious'] = true;
            $indicators['risk_score'] += 50;
            $indicators['indicators'][] = 'Numéro signalé comme spam';
        }

        // Check for virtual number patterns
        if ($this->isVirtualNumber($phone)) {
            $indicators['is_suspicious'] = true;
            $indicators['risk_score'] += 20;
            $indicators['indicators'][] = 'Numéro virtuel détecté';
        }

        // Cap risk score at 100
        $indicators['risk_score'] = min($indicators['risk_score'], 100);

        return $indicators;
    }

    /**
     * Normalize phone number
     */
    private function normalizePhone($phone)
    {
        // Remove all non-numeric characters except +
        $normalized = preg_replace('/[^0-9+]/', '', $phone);
        
        // Remove leading + for comparison
        if (strpos($normalized, '+') === 0) {
            $normalized = substr($normalized, 1);
        }
        
        return $normalized;
    }

    /**
     * Check if number is premium rate
     */
    private function isPremiumRate($phone)
    {
        $premiumPrefixes = ['089', '0899', '0892', '0891', '090', '0900', '0901', '0906', '0907', '0908', '0909'];
        
        foreach ($premiumPrefixes as $prefix) {
            if (strpos($phone, $prefix) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if number matches known spam patterns
     */
    private function matchesSpamPattern($phone)
    {
        // Common spam patterns
        $patterns = [
            '/^00\d{10,14}$/', // International format without +
            '/^\d{10}$/', // 10-digit numbers (common for spam)
            '/^1\d{10}$/', // US/Canada format
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if number is virtual
     */
    private function isVirtualNumber($phone)
    {
        // Common virtual number prefixes
        $virtualPrefixes = ['070', '075', '076', '077', '078', '079'];
        
        foreach ($virtualPrefixes as $prefix) {
            if (strpos($phone, $prefix) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get spam statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_reports,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_reports,
                    COUNT(CASE WHEN status = 'verified' THEN 1 END) as verified_reports,
                    COUNT(CASE WHEN category = 'spam' THEN 1 END) as spam_reports,
                    COUNT(CASE WHEN category = 'fraud' THEN 1 END) as fraud_reports
                FROM spam_reports";
        
        return $this->db->fetchOne($sql);
    }
}
