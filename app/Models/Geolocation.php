<?php

namespace App\Models;

use App\Models\Database;

/**
 * Geolocation Model
 * Handles geolocation data for phone dial codes
 */
class Geolocation
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get geolocation by dial code
     */
    public function getByDialCode($dialCode)
    {
        $sql = "SELECT * FROM geolocation WHERE dial_code = ?";
        return $this->db->fetchOne($sql, [$dialCode]);
    }

    /**
     * Get all countries with geolocation
     */
    public function getAll()
    {
        $sql = "SELECT * FROM geolocation ORDER BY country_name ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get countries by continent
     */
    public function getByContinent($continent)
    {
        $sql = "SELECT * FROM geolocation WHERE continent = ? ORDER BY country_name ASC";
        return $this->db->fetchAll($sql, [$continent]);
    }

    /**
     * Get continents list
     */
    public function getContinents()
    {
        $sql = "SELECT DISTINCT continent FROM geolocation ORDER BY continent ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get geolocation statistics by continent
     */
    public function getStatisticsByContinent()
    {
        $sql = "SELECT 
                    continent,
                    COUNT(*) as country_count,
                    COUNT(DISTINCT region) as region_count
                FROM geolocation
                GROUP BY continent
                ORDER BY country_count DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Search countries by name
     */
    public function search($query)
    {
        $sql = "SELECT * FROM geolocation 
                WHERE country_name LIKE ? OR country_iso LIKE ? 
                ORDER BY country_name ASC 
                LIMIT 20";
        $searchTerm = "%{$query}%";
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm]);
    }

    /**
     * Get nearby countries (by region)
     */
    public function getNearbyCountries($dialCode)
    {
        $geo = $this->getByDialCode($dialCode);
        
        if (!$geo || !$geo['region']) {
            return [];
        }

        $sql = "SELECT * FROM geolocation 
                WHERE region = ? AND dial_code != ? 
                ORDER BY country_name ASC";
        
        return $this->db->fetchAll($sql, [$geo['region'], $dialCode]);
    }

    /**
     * Add or update geolocation data
     */
    public function save($dialCode, $countryName, $countryIso, $continent = null, $region = null, $latitude = null, $longitude = null)
    {
        $sql = "INSERT INTO geolocation (dial_code, country_name, country_iso, continent, region, latitude, longitude) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                country_name = VALUES(country_name),
                country_iso = VALUES(country_iso),
                continent = VALUES(continent),
                region = VALUES(region),
                latitude = VALUES(latitude),
                longitude = VALUES(longitude)";
        
        $this->db->query($sql, [$dialCode, $countryName, $countryIso, $continent, $region, $latitude, $longitude]);
        
        return ['success' => true];
    }
}
