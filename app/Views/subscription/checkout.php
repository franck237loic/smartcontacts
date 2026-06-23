<?php
$current_page = 'subscription';
$title = $title ?? 'Paiement - GlobalPhone Analytics';
$plan = $plan ?? [];

ob_start();
?>

<!-- Checkout Section -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="?subscription/plans" class="text-purple-600 hover:text-purple-700">
                <i class="fas fa-arrow-left mr-2"></i>Retour aux plans
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Order Summary -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-6">Récapitulatif</h2>
                
                <div class="border-b pb-4 mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-lg"><?= htmlspecialchars($plan['name']) ?></h3>
                            <p class="text-gray-600"><?= $plan['interval'] === 'monthly' ? 'Facturation mensuelle' : 'Facturation annuelle' ?></p>
                        </div>
                        <div class="text-2xl font-bold">
                            <?= number_format($plan['price'], 2) ?>€
                        </div>
                    </div>
                </div>
                
                <div class="space-y-2 mb-4">
                    <?php 
                    $features = json_decode($plan['features'], true);
                    foreach ($features as $feature): ?>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span><?= htmlspecialchars($feature) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="border-t pt-4">
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span><?= number_format($plan['price'], 2) ?>€</span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Form -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-6">Paiement</h2>
                
                <form id="payment-form" class="space-y-6">
                    <input type="hidden" name="plan" value="<?= $plan['slug'] ?>">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Numéro de carte</label>
                        <input type="text" id="card-number" placeholder="4242 4242 4242 4242"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date d'expiration</label>
                            <input type="text" id="card-expiry" placeholder="MM/YY"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CVC</label>
                            <input type="text" id="card-cvc" placeholder="123"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition">
                        Payer <?= number_format($plan['price'], 2) ?>€
                    </button>
                    
                    <p class="text-xs text-gray-500 text-center">
                        <i class="fas fa-lock mr-1"></i>Paiement sécurisé par Stripe
                    </p>
                </form>
                
                <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('payment-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const plan = document.querySelector('input[name="plan"]').value;
    const cardNumber = document.getElementById('card-number').value;
    const cardExpiry = document.getElementById('card-expiry').value;
    const cardCvc = document.getElementById('card-cvc').value;
    
    if (!cardNumber || !cardExpiry || !cardCvc) {
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = 'Veuillez remplir tous les champs';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    try {
        const response = await fetch('?subscription/process-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ plan, payment_method: 'card' })
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = data.message;
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = 'Erreur lors du paiement';
        errorDiv.classList.remove('hidden');
    }
});
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
   
