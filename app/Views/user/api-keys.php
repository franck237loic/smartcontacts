<?php
$current_page = 'user-api-keys';
$title = $title ?? 'Clés API - GlobalPhone Analytics';
$apiKeys = $apiKeys ?? [];

ob_start();
?>

<!-- API Keys Section -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold">Clés API</h1>
            <p class="text-gray-600">Gérez vos clés API pour accéder à l'application</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Créer une nouvelle clé API</h2>
            <form id="create-key-form" class="flex flex-col sm:flex-row gap-4">
                <input type="text" id="key-name" name="name" placeholder="Nom de la clé (ex: Application Mobile)" required
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                <input type="date" id="key-expires" name="expires_at"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700 transition">
                    Créer
                </button>
            </form>
            <div id="new-key-display" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-700 mb-2">Votre nouvelle clé API (copiez-la maintenant, elle ne sera plus affichée) :</p>
                <code id="new-key-value" class="block bg-white p-2 rounded border break-all"></code>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold">Vos clés API</h2>
            </div>
            <?php if (empty($apiKeys)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-key text-4xl mb-4"></i>
                    <p>Aucune clé API créée</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Créée le</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Dernier usage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($apiKeys as $key): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($key['name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                        <?= date('d/m/Y', strtotime($key['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                        <?= $key['last_used_at'] ? date('d/m/Y H:i', strtotime($key['last_used_at'])) : 'Jamais' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($key['is_active']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="deleteKey(<?= $key['id'] ?>)" class="text-red-600 hover:text-red-900">
                                            Supprimer
                                        </button>
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
document.getElementById('create-key-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const name = document.getElementById('key-name').value;
    const expiresAt = document.getElementById('key-expires').value;
    
    try {
        const response = await fetch('?user/create-api-key', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ name, expires_at: expiresAt })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('new-key-value').textContent = data.key;
            document.getElementById('new-key-display').classList.remove('hidden');
            document.getElementById('create-key-form').reset();
            setTimeout(() => location.reload(), 5000);
        } else {
            alert('Erreur lors de la création: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur lors de la création');
    }
});

function deleteKey(keyId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette clé API ?')) {
        fetch('?user/delete-api-key', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ key_id: keyId })
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
