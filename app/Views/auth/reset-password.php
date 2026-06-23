<?php
$current_page = 'reset-password';
$title = $title ?? 'Réinitialiser le mot de passe - GlobalPhone Analytics';
$token = $token ?? '';

ob_start();
?>

<!-- Reset Password Section -->
<section class="min-h-screen gradient-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <i class="fas fa-globe text-6xl text-white mb-4"></i>
            <h2 class="text-3xl font-bold text-white">Réinitialiser le mot de passe</h2>
            <p class="mt-2 text-gray-200">Entrez votre nouveau mot de passe</p>
        </div>
        
        <form id="reset-form" class="mt-8 bg-white rounded-xl shadow-lg p-8 space-y-6">
            <input type="hidden" id="token" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" required minlength="8"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                <p class="mt-1 text-xs text-gray-500">Minimum 8 caractères</p>
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                Réinitialiser
            </button>
            
            <div class="text-center">
                <a href="?auth/login" class="text-sm text-purple-600 hover:text-purple-500">Retour à la connexion</a>
            </div>
        </form>
        
        <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
    </div>
</section>

<script>
document.getElementById('reset-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const token = document.getElementById('token').value;
    const password = document.getElementById('password').value;
    const confirm_password = document.getElementById('confirm_password').value;
    
    if (password !== confirm_password) {
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = 'Les mots de passe ne correspondent pas';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    try {
        const response = await fetch('?auth/reset-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ token, password, confirm_password })
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
        errorDiv.textContent = 'Erreur lors de la réinitialisation';
        errorDiv.classList.remove('hidden');
    }
});
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
