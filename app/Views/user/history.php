<?php
$current_page = 'user-history';
$title = $title ?? 'Historique des recherches - GlobalPhone Analytics';
$history = $history ?? [];

ob_start();
?>

<!-- Search History Section -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold">Historique des recherches</h1>
                <p class="text-gray-600">Consultez toutes vos recherches précédentes</p>
            </div>
            <div class="flex gap-3">
                <a href="?api/export/csv" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition">
                    Exporter CSV
                </a>
                <button onclick="clearHistory()" class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                    Effacer tout
                </button>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <?php if (empty($history)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-search text-4xl mb-4"></i>
                    <p>Aucune recherche dans l'historique</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Opérateur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Pays</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($history as $item): ?>
                                <?php $result = json_decode($item['result'], true); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($item['phone']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                        <div class="text-gray-600"><?= $result['data']['operator_name'] ?? 'N/A' ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                        <div class="text-gray-600"><?= $result['data']['country_name'] ?? 'N/A' ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($result['success']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Succès
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Échec
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-6">
            <a href="?user/dashboard" class="text-purple-600 hover:text-purple-700">
                <i class="fas fa-arrow-left mr-2"></i>Retour au dashboard
            </a>
        </div>
    </div>
</section>

<script>
function clearHistory() {
    if (confirm('Êtes-vous sûr de vouloir effacer tout l\'historique ?')) {
        fetch('?user/clear-history', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la suppression');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
