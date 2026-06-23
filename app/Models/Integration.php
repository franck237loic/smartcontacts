<?php

namespace App\Models;

use App\Models\Database;

/**
 * Integration Model
 * Handles third-party integrations (Zapier, CRM, etc.)
 */
class Integration
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new integration
     */
    public function create($userId, $type, $name, $config)
    {
        $sql = "INSERT INTO integrations (user_id, type, name, config) VALUES (?, ?, ?, ?)";
        return $this->db->query($sql, [
            $userId,
            $type,
            $name,
            json_encode($config)
        ]);
    }

    /**
     * Get integration by ID
     */
    public function getById($integrationId)
    {
        $sql = "SELECT * FROM integrations WHERE id = ?";
        $result = $this->db->fetchOne($sql, [$integrationId]);
        if ($result && isset($result['config'])) {
            $result['config'] = json_decode($result['config'], true);
        }
        return $result;
    }

    /**
     * Get integrations for a user
     */
    public function getUserIntegrations($userId)
    {
        $sql = "SELECT * FROM integrations WHERE user_id = ? ORDER BY created_at DESC";
        $results = $this->db->fetchAll($sql, [$userId]);
        foreach ($results as &$result) {
            if (isset($result['config'])) {
                $result['config'] = json_decode($result['config'], true);
            }
        }
        return $results;
    }

    /**
     * Get integrations by type
     */
    public function getUserIntegrationsByType($userId, $type)
    {
        $sql = "SELECT * FROM integrations WHERE user_id = ? AND type = ? ORDER BY created_at DESC";
        $results = $this->db->fetchAll($sql, [$userId, $type]);
        foreach ($results as &$result) {
            if (isset($result['config'])) {
                $result['config'] = json_decode($result['config'], true);
            }
        }
        return $results;
    }

    /**
     * Update integration
     */
    public function update($integrationId, $name, $config)
    {
        $sql = "UPDATE integrations SET name = ?, config = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [
            $name,
            json_encode($config),
            $integrationId
        ]);
    }

    /**
     * Toggle integration active status
     */
    public function toggleActive($integrationId)
    {
        $sql = "UPDATE integrations SET is_active = NOT is_active WHERE id = ?";
        return $this->db->query($sql, [$integrationId]);
    }

    /**
     * Delete integration
     */
    public function delete($integrationId)
    {
        $sql = "DELETE FROM integrations WHERE id = ?";
        return $this->db->query($sql, [$integrationId]);
    }

    /**
     * Send webhook to Zapier
     */
    public function sendToZapier($webhookUrl, $data)
    {
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }

    /**
     * Send data to CRM (HubSpot, Salesforce, etc.)
     */
    public function sendToCRM($type, $config, $data)
    {
        switch ($type) {
            case 'hubspot':
                return $this->sendToHubSpot($config, $data);
            case 'salesforce':
                return $this->sendToSalesforce($config, $data);
            case 'pipedrive':
                return $this->sendToPipedrive($config, $data);
            default:
                return ['success' => false, 'message' => 'Type CRM non supporté'];
        }
    }

    /**
     * Send to HubSpot
     */
    private function sendToHubSpot($config, $data)
    {
        if (!isset($config['api_key'])) {
            return ['success' => false, 'message' => 'Clé API HubSpot manquante'];
        }

        $url = 'https://api.hubapi.com/crm/v3/objects/contacts?hapikey=' . $config['api_key'];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }

    /**
     * Send to Salesforce
     */
    private function sendToSalesforce($config, $data)
    {
        if (!isset($config['access_token']) || !isset($config['instance_url'])) {
            return ['success' => false, 'message' => 'Configuration Salesforce incomplète'];
        }

        $url = $config['instance_url'] . '/services/data/v52.0/sobjects/Contact/';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $config['access_token']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }

    /**
     * Send to Pipedrive
     */
    private function sendToPipedrive($config, $data)
    {
        if (!isset($config['api_token'])) {
            return ['success' => false, 'message' => 'Token API Pipedrive manquant'];
        }

        $companyDomain = $config['company_domain'] ?? 'api';
        $url = "https://{$companyDomain}.pipedrive.com/api/v1/contacts?api_token={$config['api_token']}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }
}
