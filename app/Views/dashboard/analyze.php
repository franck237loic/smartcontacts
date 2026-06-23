<?php
$current_page = 'analyze';
$title = 'Analyser - GlobalPhone Analytics';

ob_start();
?>

<!-- Hero Section -->
<section class="gradient-bg text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl sm:text-4xl font-bold mb-2 sm:mb-4">Analyse de Numéros</h1>
        <p class="text-lg sm:text-xl text-gray-200">Identifiez les opérateurs de vos contacts</p>
    </div>
</section>

<!-- Single Analysis -->
<section class="py-8 sm:py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6"><i class="fas fa-search mr-2"></i>Analyse Individuelle</h2>
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

<!-- Batch Analysis -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6"><i class="fas fa-file-upload mr-2"></i>Analyse par Lot</h2>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Collez vos numéros (un par ligne)</label>
                <textarea id="batch-input" rows="8 sm:rows-10" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm sm:text-base"
                          placeholder="+33612345678&#10;+4915212345678&#10;+12125551234"></textarea>
            </div>
            <button onclick="batchAnalyze()" class="w-full sm:w-auto bg-blue-600 text-white px-6 sm:px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                Analyser le Lot
            </button>
            <div id="batch-result" class="hidden mt-4 bg-gray-50 rounded-lg p-4"></div>
        </div>
    </div>
</section>

<!-- Filters -->
<section class="py-8 sm:py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6"><i class="fas fa-filter mr-2"></i>Filtres</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                    <select id="country-filter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm sm:text-base">
                        <option value="">Tous les pays</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= htmlspecialchars($country['iso']) ?>">
                                <?= htmlspecialchars($country['name']) ?> (<?= htmlspecialchars($country['dialCode']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Marque d'opérateur</label>
                    <select id="brand-filter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm sm:text-base">
                        <option value="">Toutes les marques</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?= htmlspecialchars($brand['brand']) ?>">
                                <?= htmlspecialchars($brand['brand']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function showAuthModal() {
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
}

async function analyzePhone() {
    const phone = document.getElementById('phone-input').value;
    if (!phone) {
        alert('Veuillez entrer un numéro de téléphone');
        return;
    }

    // Vérifier si l'utilisateur est connecté
    const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    
    if (!isLoggedIn) {
        showAuthModal();
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

async function batchAnalyze() {
    const input = document.getElementById('batch-input').value;
    const phones = input.split('\n').map(p => p.trim()).filter(p => p);
    
    if (phones.length === 0) {
        alert('Veuillez entrer au moins un numéro de téléphone');
        return;
    }

    // Vérifier si l'utilisateur est connecté
    const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    
    if (!isLoggedIn) {
        showAuthModal();
        return;
    }

    try {
        const response = await fetch('?api/phone/batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ phones: phones })
        });
        const data = await response.json();
        
        const resultDiv = document.getElementById('batch-result');
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            let html = `
                <div class="mb-4">
                    <div class="font-semibold">Statistiques:</div>
                    <div>Total: ${data.statistics.total}</div>
                    <div class="text-green-600">Identifiés: ${data.statistics.identified}</div>
                    <div class="text-red-600">Non identifiés: ${data.statistics.not_identified}</div>
                </div>
                <div class="max-h-96 overflow-y-auto">
            `;
            
            for (const [phone, result] of Object.entries(data.results)) {
                if (result) {
                    html += `
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded mb-2">
                            <div class="flex items-center">
                                <img src="/LOGO/${result.logo}" class="w-8 h-8 rounded mr-3" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22><rect fill=%22%23667eea%22 width=%2232%22 height=%2232%22/></svg>'">
                                <div>
                                    <div class="font-semibold">${phone}</div>
                                    <div class="text-sm text-gray-600">${result.operator_name}</div>
                                </div>
                            </div>
                            <span class="text-green-600 text-sm">Identifié</span>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded mb-2">
                            <div class="font-semibold">${phone}</div>
                            <span class="text-red-600 text-sm">Non identifié</span>
                        </div>
                    `;
                }
            }
            
            html += '</div>';
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = `
                <div class="text-red-600">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    ${data.error || 'Erreur lors de l\'analyse'}
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
