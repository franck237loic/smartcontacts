<?php

namespace App\Models;

/**
 * Prefix Model
 * Handles prefix-related database operations
 */
class Prefix
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all prefixes
     */
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT p.*, o.name as operator_name, o.brand, o.logo, o.color, c.name as country_name, c.iso as country_iso
                FROM prefixes p 
                LEFT JOIN operators o ON p.operatorId = o.id 
                LEFT JOIN countries c ON o.country = c.iso
                ORDER BY p.dialCode ASC, p.prefix ASC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, [$limit, $offset]);
        }
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Get prefixes by country
     */
    public function getByCountry($countryIso)
    {
        $sql = "SELECT p.*, o.name as operator_name, o.brand, o.logo, o.color, c.name as country_name, c.iso as country_iso
                FROM prefixes p 
                LEFT JOIN operators o ON p.operatorId = o.id 
                LEFT JOIN countries c ON o.country = c.iso
                WHERE o.country = ?
                ORDER BY p.dialCode ASC, p.prefix ASC";
        return $this->db->fetchAll($sql, [$countryIso]);
    }

    /**
     * Get prefixes by operator
     */
    public function getByOperator($operatorId)
    {
        $sql = "SELECT p.*, o.name as operator_name, o.brand, o.logo, o.color, c.name as country_name, c.iso as country_iso
                FROM prefixes p 
                LEFT JOIN operators o ON p.operatorId = o.id 
                LEFT JOIN countries c ON o.country = c.iso
                WHERE p.operatorId = ?
                ORDER BY p.dialCode ASC, p.prefix ASC";
        return $this->db->fetchAll($sql, [$operatorId]);
    }

    /**
     * Search operator by phone number
     */
    public function searchByPhone($phone)
    {
        // Clean phone number (keep only digits)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) < 3) {
            return null;
        }
        
        $sql = "SELECT p.*, o.name as operator_name, o.brand, o.logo, o.color, c.name as country_name, c.iso as country_iso
                FROM prefixes p 
                LEFT JOIN operators o ON p.operatorId = o.id 
                LEFT JOIN countries c ON o.country = c.iso
                WHERE ? LIKE CONCAT(p.dialCode, p.prefix, '%')
                ORDER BY LENGTH(p.dialCode) + LENGTH(p.prefix) DESC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$phone]);
    }

    /**
     * Get prefix statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_prefixes,
                    COUNT(DISTINCT operatorId) as total_operators,
                    COUNT(DISTINCT dialCode) as total_dial_codes
                FROM prefixes";
        return $this->db->fetchOne($sql);
    }

    /**
     * Get prefixes count by operator
     */
    public function getCountByOperator()
    {
        $sql = "SELECT o.name as operator_name, o.brand, COUNT(p.operatorId) as prefix_count
                FROM operators o
                LEFT JOIN prefixes p ON o.id = p.operatorId
                GROUP BY o.id, o.name, o.brand
                ORDER BY prefix_count DESC
                LIMIT 20";
        return $this->db->fetchAll($sql);
    }

    /**
     * Batch search multiple phone numbers
     */
    public function batchSearch($phones)
    {
        $results = [];
        foreach ($phones as $phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($cleanPhone) >= 3) {
                $result = $this->searchByPhone($cleanPhone);
                $results[$phone] = $result;
            } else {
                $results[$phone] = null;
            }
        }
        return $results;
    }
}
