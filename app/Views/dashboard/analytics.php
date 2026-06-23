<?php
$current_page = 'analytics';
$title = 'Analytics - GlobalPhone Analytics';

ob_start();
?>

<!-- Hero Section -->
<section class="gradient-bg text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl sm:text-4xl font-bold mb-2 sm:mb-4">Analytics Avancés</h1>
        <p class="text-lg sm:text-xl text-gray-200">Visualisez vos données téléphoniques</p>
    </div>
</section>

<!-- Statistics Cards -->
<section class="py-8 sm:py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white border-2 border-purple-500 rounded-xl p-4 sm:p-6 card-hover">
                <div class="text-center">
                    <div class="text-2xl sm:text-4xl font-bold text-purple-600"><?= count($countries) ?></div>
                    <div class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Pays</div>
                </div>
            </div>
            <div class="bg-white border-2 border-blue-500 rounded-xl p-4 sm:p-6 card-hover">
                <div class="text-center">
                    <div class="text-2xl sm:text-4xl font-bold text-blue-600"><?= count($operators) ?></div>
                    <div class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Opérateurs</div>
                </div>
            </div>
            <div class="bg-white border-2 border-green-500 rounded-xl p-4 sm:p-6 card-hover">
                <div class="text-center">
                    <div class="text-2xl sm:text-4xl font-bold text-green-600"><?= count($continents) ?></div>
                    <div class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Continents</div>
                </div>
            </div>
            <div class="bg-white border-2 border-orange-500 rounded-xl p-4 sm:p-6 card-hover">
                <div class="text-center">
                    <div class="text-2xl sm:text-4xl font-bold text-orange-600"><?= count(array_unique(array_column($operators, 'brand'))) ?></div>
                    <div class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Marques</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Continent Distribution -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6"><i class="fas fa-globe mr-2"></i>Distribution par Continent</h2>
            <canvas id="continentChart" height="250"></canvas>
        </div>
    </div>
</section>

<!-- Country Distribution -->
<section class="py-8 sm:py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6"><i class="fas fa-flag mr-2"></i>Distribution par Pays (Top 20)</h2>
            <canvas id="countryChart" height="300"></canvas>
        </div>
    </div>
</section>

<!-- Brand Distribution -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6"><i class="fas fa-broadcast-tower mr-2"></i>Distribution par Marque (Top 15)</h2>
            <canvas id="brandChart" height="300"></canvas>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countries = <?= json_encode($countries) ?>;
    const operators = <?= json_encode($operators) ?>;
    const continents = <?= json_encode($continents) ?>;

    // Calculate continent distribution
    const continentData = {};
    countries.forEach(country => {
        const continent = country.continent;
        continentData[continent] = (continentData[continent] || 0) + 1;
    });

    // Continent Chart
    const continentCtx = document.getElementById('continentChart').getContext('2d');
    new Chart(continentCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(continentData),
            datasets: [{
                data: Object.values(continentData),
                backgroundColor: [
                    '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe',
                    '#00f2fe', '#43e97b', '#38f9d7'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Country distribution (by operator count)
    const countryOperatorCount = {};
    operators.forEach(op => {
        countryOperatorCount[op.country] = (countryOperatorCount[op.country] || 0) + 1;
    });

    const sortedCountries = Object.entries(countryOperatorCount)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 20);

    const countryCtx = document.getElementById('countryChart').getContext('2d');
    new Chart(countryCtx, {
        type: 'bar',
        data: {
            labels: sortedCountries.map(([iso]) => {
                const country = countries.find(c => c.iso === iso);
                return country ? country.name : iso;
            }),
            datasets: [{
                label: 'Nombre d\'opérateurs',
                data: sortedCountries.map(([, count]) => count),
                backgroundColor: '#667eea'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });

    // Brand distribution
    const brandCount = {};
    operators.forEach(op => {
        brandCount[op.brand] = (brandCount[op.brand] || 0) + 1;
    });

    const sortedBrands = Object.entries(brandCount)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 15);

    const brandCtx = document.getElementById('brandChart').getContext('2d');
    new Chart(brandCtx, {
        type: 'bar',
        data: {
            labels: sortedBrands.map(([brand]) => brand),
            datasets: [{
                label: 'Nombre d\'opérateurs',
                data: sortedBrands.map(([, count]) => count),
                backgroundColor: '#764ba2'
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
