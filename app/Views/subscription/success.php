<?php
$current_page = 'subscription';
$title = $title ?? 'Abonnement réussi - GlobalPhone Analytics';

ob_start();
?>

<!-- Success Section -->
<section class="min-h-screen gradient-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-4xl text-green-600"></i>
            </div>
            
            <h1 class="text-3xl font-bold mb-4">Abonnement réussi !</h1>
            <p class="text-gray-600 mb-8">Votre abonnement est maintenant actif. Vous pouvez profiter de toutes les fonctionnalités de votre plan.</p>
            
            <div class="space-y-4">
                <a href="?dashboard" class="block w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition">
                    Aller au Dashboard
                </a>
                <a href="?subscription/manage" class="block w-full bg-gray-200 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Gérer mon abonnement
                </a>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
