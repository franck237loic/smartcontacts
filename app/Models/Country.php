<?php

namespace App\Models;

/**
 * Country Model
 * Handles country-related database operations
 */
class Country
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all countries
     */
    public function getAll()
    {
        $sql = "SELECT * FROM countries ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get country by ISO code
     */
    public function getByIso($iso)
    {
        $sql = "SELECT * FROM countries WHERE iso = ?";
        return $this->db->fetchOne($sql, [$iso]);
    }

    /**
     * Get countries by continent
     */
    public function getByContinent($continent)
    {
        $sql = "SELECT * FROM countries WHERE continent = ? ORDER BY name ASC";
        return $this->db->fetchAll($sql, [$continent]);
    }

    /**
     * Get country by dial code
     */
    public function getByDialCode($dialCode)
    {
        $sql = "SELECT * FROM countries WHERE dialCode = ?";
        return $this->db->fetchOne($sql, [$dialCode]);
    }

    /**
     * Search countries by name
     */
    public function search($query)
    {
        $sql = "SELECT * FROM countries WHERE name LIKE ? ORDER BY name ASC LIMIT 10";
        return $this->db->fetchAll($sql, ["%$query%"]);
    }

    /**
     * Get unique continents
     */
    public function getContinents()
    {
        $sql = "SELECT DISTINCT continent FROM countries ORDER BY continent ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get country statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_countries,
                    COUNT(DISTINCT continent) as total_continents,
                    COUNT(DISTINCT dialCode) as total_dial_codes
                FROM countries";
        return $this->db->fetchOne($sql);
    }
}
