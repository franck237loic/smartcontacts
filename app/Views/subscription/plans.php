<?php
$current_page = 'subscription';
$title = $title ?? 'Plans d\'abonnement - GlobalPhone Analytics';
$plans = $plans ?? [];
$currentSubscription = $currentSubscription ?? null;
$stripePublishableKey = $stripePublishableKey ?? '';

ob_start();
?>

<!-- Plans Section -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold mb-4">Choisissez votre plan</h1>
            <p class="text-gray-600 text-lg">Des tarifs adaptés à vos besoins</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($plans as $plan): ?>
                <div class="bg-white rounded-xl shadow-lg p-8 <?= $plan['slug'] === 'pro' ? 'border-2 border-purple-500 transform scale-105' : '' ?>">
                    <?php if ($plan['slug'] === 'pro'): ?>
                        <div class="text-center mb-4">
                            <span class="bg-purple-500 text-white px-4 py-1 rounded-full text-sm font-semibold">Populaire</span>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="text-2xl font-bold text-center mb-2"><?= htmlspecialchars($plan['name']) ?></h3>
                    <div class="text-center mb-6">
                        <span class="text-4xl font-bold"><?= number_format($plan['price'], 2) ?></span>
                        <span class="text-gray-600">€/<?= $plan['interval'] === 'monthly' ? 'mois' : 'an' ?></span>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <?php 
                        $features = json_decode($plan['features'], true);
                        foreach ($features as $feature): ?>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span><?= htmlspecialchars($feature) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <?php if ($currentSubscription && $currentSubscription['slug'] === $plan['slug']): ?>
                        <button disabled class="w-full bg-gray-300 text-gray-600 py-3 rounded-lg font-semibold cursor-not-allowed">
                            Plan actuel
                        </button>
                    <?php elseif ($plan['price'] == 0): ?>
                        <a href="?subscription/process-payment" 
                           class="block w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition text-center"
                           onclick="activateFreePlan('<?= $plan['slug'] ?>'); return false;">
                            Commencer gratuitement
                        </a>
                    <?php else: ?>
                        <button onclick="subscribeToPlan('<?= $plan['slug'] ?>', '<?= $plan['interval'] ?>')"
                                class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition">
                            Choisir ce plan
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($currentSubscription): ?>
            <div class="mt-8 text-center">
                <a href="?subscription/manage" class="text-purple-600 hover:text-purple-700 font-semibold">
                    Gérer mon abonnement actuel
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function activateFreePlan(planSlug) {
    fetch('?subscription/process-payment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            plan: planSlug,
            interval: 'monthly'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect || '?subscription/success';
        } else {
            alert(data.message || 'Erreur lors de l\'activation du plan');
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function subscribeToPlan(planSlug, interval) {
    fetch('?subscription/process-payment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            plan: planSlug,
            interval: interval
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.checkout_url) {
            window.location.href = data.checkout_url;
        } else {
            alert(data.message || 'Erreur lors de la création de la session de paiement');
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
