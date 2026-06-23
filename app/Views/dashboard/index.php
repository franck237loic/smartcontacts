<?php
$current_page = 'dashboard';
$title = 'Dashboard - GlobalPhone Analytics';

ob_start();
?>

<!-- Hero Section -->
<section class="gradient-bg text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold mb-4">GlobalPhone Analytics</h1>
        <p class="text-xl text-gray-200 mb-6">Identifiez instantanément les opérateurs mobiles du monde entier</p>
        <p class="text-lg text-gray-300 max-w-3xl mx-auto">
            Notre plateforme vous permet d'analyser les numéros de téléphone pour identifier l'opérateur, 
            le pays et les informations techniques associées. Créez un compte pour accéder à des fonctionnalités 
            avancées et suivre votre historique de recherches.
        </p>
    </div>
</section>

<!-- Stats Section -->
<section class="py-8 sm:py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-4 sm:p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl sm:text-3xl font-bold"><?= $stats['countries']['total_countries'] ?? 0 ?></div>
                        <div class="text-purple-100 text-sm sm:text-base">Pays</div>
                    </div>
                    <i class="fas fa-globe text-3xl sm:text-4xl opacity-50"></i>
                </div>
            </div>
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-4 sm:p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl sm:text-3xl font-bold"><?= $stats['operators']['total_operators'] ?? 0 ?></div>
                        <div class="text-blue-100 text-sm sm:text-base">Opérateurs</div>
                    </div>
                    <i class="fas fa-broadcast-tower text-3xl sm:text-4xl opacity-50"></i>
                </div>
            </div>
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 sm:p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl sm:text-3xl font-bold"><?= $stats['prefixes']['total_prefixes'] ?? 0 ?></div>
                        <div class="text-green-100 text-sm sm:text-base">Préfixes</div>
                    </div>
                    <i class="fas fa-phone text-3xl sm:text-4xl opacity-50"></i>
                </div>
            </div>
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-4 sm:p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl sm:text-3xl font-bold"><?= $stats['countries']['total_continents'] ?? 0 ?></div>
                        <div class="text-orange-100 text-sm sm:text-base">Continents</div>
                    </div>
                    <i class="fas fa-map-marked-alt text-3xl sm:text-4xl opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Search -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6"><i class="fas fa-search mr-2"></i>Recherche Rapide</h2>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 mb-4">
                <input type="text" id="phone-input" placeholder="+33612345678" 
                       class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <button onclick="analyzePhone()" class="bg-purple-600 text-white px-6 sm:px-8 py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
                    Analyser
                </button>
            </div>
            <div id="quick-result" class="hidden bg-gray-50 rounded-lg p-4"></div>
        </div>
    </div>
</section>

<!-- Charts Section -->
<section class="py-8 sm:py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold mb-4"><i class="fas fa-chart-bar mr-2"></i>Top 10 Pays par Opérateurs</h3>
                <canvas id="countryChart" height="250"></canvas>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold mb-4"><i class="fas fa-chart-pie mr-2"></i>Top 10 Opérateurs par Préfixes</h3>
                <canvas id="operatorChart" height="250"></canvas>
            </div>
        </div>
    </div>
</section>

<!-- Recent Operators -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6"><i class="fas fa-broadcast-tower mr-2"></i>Opérateurs Récents</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <?php foreach ($recentOperators as $operator): ?>
                    <div class="flex items-center space-x-3 sm:space-x-4 p-3 sm:p-4 bg-gray-50 rounded-lg card-hover">
                        <img src="/LOGO/<?= htmlspecialchars($operator['logo']) ?>" 
                             class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg object-contain" 
                             onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 48 48%22><rect fill=%22%23667eea%22 width=%2248%22 height=%2248%22/></svg>'">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm sm:text-base truncate"><?= htmlspecialchars($operator['name']) ?></div>
                            <div class="text-xs sm:text-sm text-gray-600 truncate"><?= htmlspecialchars($operator['country_name']) ?></div>
                            <div class="text-xs text-gray-500 truncate"><?= htmlspecialchars($operator['brand']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Country Chart
    const countryCtx = document.getElementById('countryChart').getContext('2d');
    const countryData = <?= json_encode(array_slice($countriesByOperatorCount, 0, 10)) ?>;
    
    new Chart(countryCtx, {
        type: 'bar',
        data: {
            labels: countryData.map(item => item.country_name),
            datasets: [{
                label: 'Opérateurs',
                data: countryData.map(item => item.operator_count),
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
                }
            }
        }
    });

    // Operator Chart
    const operatorCtx = document.getElementById('operatorChart').getContext('2d');
    const operatorData = <?= json_encode(array_slice($operatorsByPrefixCount, 0, 10)) ?>;
    
    new Chart(operatorCtx, {
        type: 'doughnut',
        data: {
            labels: operatorData.map(item => item.operator_name),
            datasets: [{
                data: operatorData.map(item => item.prefix_count),
                backgroundColor: [
                    '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe',
                    '#00f2fe', '#43e97b', '#38f9d7', '#fa709a', '#fee140'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});

async function analyzePhone() {
    const phone = document.getElementById('phone-input').value;
    if (!phone) {
        alert('Veuillez entrer un numéro de téléphone');
        return;
    }

    // Vérifier si l'utilisateur est connecté
    const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    
    if (!isLoggedIn) {
        // Afficher une modal pour demander de se connecter ou s'inscrire
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl p-8 max-w-md mx-4 shadow-2xl">
                <div class="text-center">
                    <i class="fas fa-user-lock text-5xl text-purple-600 mb-4"></i>
                    <h3 class="text-2xl font-bold mb-4">Connexion requise</h3>
                    <p class="text-gray-600 mb-6">
                        Pour analyser des numéros de téléphone, vous devez être connecté. 
                        Créez un compte gratuitement pour accéder à cette fonctionnalité.
                    </p>
                    <div class="space-y-3">
                        <a href="?auth/login" class="block w-full bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
                            Se connecter
                        </a>
                        <a href="?auth/register" class="block w-full bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold">
                            Créer un compte
                        </a>
                        <button onclick="this.closest('.fixed').remove()" class="block w-full text-gray-500 hover:text-gray-700 transition">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
        return;
    }

    try {
        const response = await fetch(`?api/phone/analyze&phone=${encodeURIComponent(phone)}`);
        const data = await response.json();
        
        const resultDiv = document.getElementById('quick-result');
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            const operator = data.data;
            resultDiv.innerHTML = `
                <div class="flex items-center space-x-4">
                    <img src="/LOGO/${operator.logo}" class="w-16 h-16 rounded-lg object-contain" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 64 64%22><rect fill=%22%23667eea%22 width=%2264%22 height=%2264%22/></svg>'">
                    <div class="flex-1">
                        <div class="font-bold text-lg">${operator.operator_name}</div>
                        <div class="text-gray-600">${operator.country_name} (${operator.country_iso})</div>
                        <div class="text-sm text-gray-500">Préfixe: ${operator.dialCode}${operator.prefix}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-green-600 font-semibold">Identifié</div>
                        <div class="text-sm text-gray-500">${operator.brand}</div>
                    </div>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="text-red-600">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    ${data.message || 'Opérateur non trouvé'}
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur lors de l\'analyse');
    }
}

document.getElementById('phone-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        analyzePhone();
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
