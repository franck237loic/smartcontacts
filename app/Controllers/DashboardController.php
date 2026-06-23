<?php

namespace App\Controllers;

use App\Models\Country;
use App\Models\Operator;
use App\Models\Prefix;

/**
 * Dashboard Controller
 * Handles dashboard and analytics views
 */
class DashboardController extends Controller
{
    private $countryModel;
    private $operatorModel;
    private $prefixModel;

    public function __construct()
    {
        parent::__construct();
        $this->countryModel = new Country();
        $this->operatorModel = new Operator();
        $this->prefixModel = new Prefix();
    }

    /**
     * Display main dashboard
     */
    public function index()
    {
        $stats = [
            'countries' => $this->countryModel->getStatistics(),
            'operators' => $this->operatorModel->getStatistics(),
            'prefixes' => $this->prefixModel->getStatistics()
        ];

        $recentOperators = $this->operatorModel->getAll(10);
        $countriesByOperatorCount = $this->operatorModel->getCountByCountry();
        $operatorsByPrefixCount = $this->prefixModel->getCountByOperator();

        return $this->render('dashboard/index', [
            'stats' => $stats,
            'recentOperators' => $recentOperators,
            'countriesByOperatorCount' => $countriesByOperatorCount,
            'operatorsByPrefixCount' => $operatorsByPrefixCount
        ]);
    }

    /**
     * Display analytics page
     */
    public function analytics()
    {
        $countries = $this->countryModel->getAll();
        $operators = $this->operatorModel->getAll();
        $continents = $this->countryModel->getContinents();

        return $this->render('dashboard/analytics', [
            'countries' => $countries,
            'operators' => $operators,
            'continents' => $continents
        ]);
    }

    /**
     * Display analyze page
     */
    public function analyze()
    {
        $countries = $this->countryModel->getAll();
        $brands = $this->operatorModel->getBrands();

        return $this->render('dashboard/analyze', [
            'countries' => $countries,
            'brands' => $brands
        ]);
    }

    /**
     * API endpoint for dashboard statistics
     */
    public function apiStats()
    {
        $stats = [
            'countries' => $this->countryModel->getStatistics(),
            'operators' => $this->operatorModel->getStatistics(),
            'prefixes' => $this->prefixModel->getStatistics()
        ];

        return $this->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * API endpoint for country distribution chart
     */
    public function apiCountryDistribution()
    {
        $data = $this->operatorModel->getCountByCountry();
        
        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * API endpoint for operator distribution chart
     */
    public function apiOperatorDistribution()
    {
        $data = $this->prefixModel->getCountByOperator();
        
        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
