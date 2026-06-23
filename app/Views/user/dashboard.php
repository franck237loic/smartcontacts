<?php
$current_page = 'user-dashboard';
$title = $title ?? 'Mon Dashboard - GlobalPhone Analytics';
$user = $user ?? [];
$subscription = $subscription ?? null;
$recentSearches = $recentSearches ?? [];
$apiKeys = $apiKeys ?? [];

ob_start();
?>

<!-- User Dashboard Section -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold">Bienvenue, <?= htmlspecialchars($user['first_name'] ?? 'Utilisateur') ?> !</h1>
            <p class="text-gray-600">Voici un aperçu de votre activité</p>
        </div>
        
        <!-- User Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-purple-600"><?= $user['api_quota_used'] ?? 0 ?></div>
                    <div class="text-gray-600 text-sm sm:text-base">Requêtes ce mois</div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-green-600"><?= ($user['api_quota_limit'] ?? 1000) - ($user['api_quota_used'] ?? 0) ?></div>
                    <div class="text-gray-600 text-sm sm:text-base">Requêtes restantes</div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-blue-600"><?= count($apiKeys) ?></div>
                    <div class="text-gray-600 text-sm sm:text-base">Clés API</div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-orange-600"><?= $subscription ? ucfirst($subscription['name']) : 'Free' ?></div>
                    <div class="text-gray-600 text-sm sm:text-base">Plan actuel</div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Searches -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold">Recherches récentes</h2>
                    <a href="?user/history" class="text-purple-600 hover:text-purple-700 text-sm">Voir tout</a>
                </div>
                
                <?php if (empty($recentSearches)): ?>
                    <p class="text-gray-500 text-center py-8">Aucune recherche récente</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach (array_slice($recentSearches, 0, 5) as $search): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($search['phone']) ?></div>
                                    <div class="text-sm text-gray-600"><?= date('d/m/Y H:i', strtotime($search['created_at'])) ?></div>
                                </div>
                                <span class="text-xs text-gray-500">
                                    <?= json_decode($search['result'], true)['success'] ? '✓' : '✗' ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Actions -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Actions rapides</h2>
                    <div class="space-y-3">
                        <a href="?dashboard/analyze" class="block w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition text-center">
                            Analyser un numéro
                        </a>
                        <a href="?user/api-keys" class="block w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition text-center">
                            Gérer les clés API
                        </a>
                        <a href="?api/export/csv" class="block w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition text-center">
                            Exporter en CSV
                        </a>
                    </div>
                </div>
                
                <!-- Subscription Info -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Votre abonnement</h2>
                    <?php if ($subscription): ?>
                        <div class="mb-4">
                            <div class="text-lg font-semibold"><?= htmlspecialchars($subscription['name']) ?></div>
                            <div class="text-gray-600"><?= number_format($subscription['price'], 2) ?>€/<?= $subscription['interval'] === 'monthly' ? 'mois' : 'an' ?></div>
                        </div>
                        <div class="text-sm text-gray-600 mb-4">
                            Expire le: <?= date('d/m/Y', strtotime($subscription['current_period_end'])) ?>
                        </div>
                        <a href="?subscription/manage" class="block w-full bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-300 transition text-center">
                            Gérer l'abonnement
                        </a>
                    <?php else: ?>
                        <p class="text-gray-600 mb-4">Vous utilisez le plan gratuit</p>
                        <a href="?subscription/plans" class="block w-full bg-purple-600 text-white py-2 rounded-lg font-semibold hover:bg-purple-700 transition text-center">
                            Voir les plans
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
