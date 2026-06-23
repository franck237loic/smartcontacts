<?php
$current_page = 'subscription';
$title = $title ?? 'Gérer mon abonnement - GlobalPhone Analytics';
$subscription = $subscription ?? null;
$user = $user ?? [];

ob_start();
?>

<!-- Manage Subscription Section -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold">Gérer mon abonnement</h1>
            <p class="text-gray-600">Consultez et gérez votre abonnement actuel</p>
        </div>
        
        <?php if ($subscription): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Current Subscription -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                        <h2 class="text-2xl font-bold mb-6">Abonnement actuel</h2>
                        
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-semibold"><?= htmlspecialchars($subscription['name']) ?></h3>
                                <p class="text-gray-600">Statut: 
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        <?= $subscription['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ucfirst($subscription['status']) ?>
                                    </span>
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold"><?= number_format($subscription['price'], 2) ?>€</div>
                                <div class="text-gray-600"><?= $subscription['interval'] === 'monthly' ? '/mois' : '/an' ?></div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-6 space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Début de période</span>
                                <span class="font-semibold"><?= date('d/m/Y', strtotime($subscription['current_period_start'])) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Fin de période</span>
                                <span class="font-semibold"><?= date('d/m/Y', strtotime($subscription['current_period_end'])) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Quota API</span>
                                <span class="font-semibold"><?= $user['api_quota_used'] ?> / <?= $user['api_quota_limit'] ?></span>
                            </div>
                        </div>
                        
                        <?php if ($subscription['status'] === 'active'): ?>
                            <div class="mt-6 pt-6 border-t">
                                <button onclick="cancelSubscription()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                                    Annuler l'abonnement
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Usage Statistics -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold mb-6">Utilisation</h2>
                        
                        <div class="mb-4">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Quota API utilisé</span>
                                <span class="font-semibold"><?= $user['api_quota_used'] ?> / <?= $user['api_quota_limit'] ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-purple-600 h-3 rounded-full" style="width: <?= min(($user['api_quota_used'] / $user['api_quota_limit']) * 100, 100) ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mt-6">
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-purple-600"><?= $user['api_quota_used'] ?></div>
                                <div class="text-gray-600 text-sm">Requêtes ce mois</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-green-600"><?= $user['api_quota_limit'] - $user['api_quota_used'] ?></div>
                                <div class="text-gray-600 text-sm">Requêtes restantes</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Upgrade/Downgrade -->
                <div>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold mb-6">Changer de plan</h2>
                        <p class="text-gray-600 mb-6">Vous pouvez changer de plan à tout moment.</p>
                        <a href="?subscription/plans" class="block w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition text-center">
                            Voir les plans disponibles
                        </a>
                    </div>
                    
                    <!-- Billing Info -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mt-6">
                        <h2 class="text-xl font-bold mb-6">Facturation</h2>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <i class="fas fa-credit-card text-gray-400 mr-3"></i>
                                <div>
                                    <div class="font-semibold">**** **** **** 4242</div>
                                    <div class="text-gray-600 text-sm">Expire 12/25</div>
                                </div>
                            </div>
                            <a href="#" class="text-purple-600 hover:text-purple-700 text-sm">
                                Mettre à jour les informations de paiement
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                <i class="fas fa-crown text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-bold mb-4">Aucun abonnement actif</h2>
                <p class="text-gray-600 mb-6">Vous utilisez actuellement le plan gratuit.</p>
                <a href="?subscription/plans" class="inline-block bg-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-purple-700 transition">
                    Voir les plans disponibles
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function cancelSubscription() {
    if (confirm('Êtes-vous sûr de vouloir annuler votre abonnement ?')) {
        fetch('?subscription/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Abonnement annulé avec succès');
                location.reload();
            } else {
                alert('Erreur lors de l\'annulation: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de l\'annulation');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
