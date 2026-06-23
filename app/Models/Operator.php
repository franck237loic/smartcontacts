<?php

namespace App\Models;

/**
 * Operator Model
 * Handles operator-related database operations
 */
class Operator
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all operators
     */
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT o.*, c.name as country_name 
                FROM operators o 
                LEFT JOIN countries c ON o.country = c.iso 
                ORDER BY o.name ASC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, [$limit, $offset]);
        }
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Get operator by ID
     */
    public function getById($id)
    {
        $sql = "SELECT o.*, c.name as country_name 
                FROM operators o 
                LEFT JOIN countries c ON o.country = c.iso 
                WHERE o.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Get operators by country
     */
    public function getByCountry($countryIso)
    {
        $sql = "SELECT o.*, c.name as country_name 
                FROM operators o 
                LEFT JOIN countries c ON o.country = c.iso 
                WHERE o.country = ? 
                ORDER BY o.name ASC";
        return $this->db->fetchAll($sql, [$countryIso]);
    }

    /**
     * Get operators by brand
     */
    public function getByBrand($brand)
    {
        $sql = "SELECT o.*, c.name as country_name 
                FROM operators o 
                LEFT JOIN countries c ON o.country = c.iso 
                WHERE o.brand = ? 
                ORDER BY o.name ASC";
        return $this->db->fetchAll($sql, [$brand]);
    }

    /**
     * Search operators by name
     */
    public function search($query)
    {
        $sql = "SELECT o.*, c.name as country_name 
                FROM operators o 
                LEFT JOIN countries c ON o.country = c.iso 
                WHERE o.name LIKE ? OR o.brand LIKE ? 
                ORDER BY o.name ASC 
                LIMIT 20";
        return $this->db->fetchAll($sql, ["%$query%", "%$query%"]);
    }

    /**
     * Get operator statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_operators,
                    COUNT(DISTINCT country) as total_countries,
                    COUNT(DISTINCT brand) as total_brands
                FROM operators";
        return $this->db->fetchOne($sql);
    }

    /**
     * Get operators count by country
     */
    public function getCountByCountry()
    {
        $sql = "SELECT c.name as country_name, c.iso, COUNT(o.id) as operator_count
                FROM countries c
                LEFT JOIN operators o ON c.iso = o.country
                GROUP BY c.iso, c.name
                ORDER BY operator_count DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get unique brands
     */
    public function getBrands()
    {
        $sql = "SELECT DISTINCT brand FROM operators ORDER BY brand ASC";
        return $this->db->fetchAll($sql);
    }
}
