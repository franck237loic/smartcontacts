<?php
$current_page = 'register';
$title = $title ?? 'Inscription - GlobalPhone Analytics';

ob_start();
?>

<!-- Register Section -->
<section class="min-h-screen gradient-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <i class="fas fa-globe text-6xl text-white mb-4"></i>
            <h2 class="text-3xl font-bold text-white">Créer un compte</h2>
            <p class="mt-2 text-gray-200">Rejoignez GlobalPhone Analytics</p>
        </div>
        
        <form id="register-form" class="mt-8 bg-white rounded-xl shadow-lg p-8 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">Prénom</label>
                    <input type="text" id="first_name" name="first_name"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Nom</label>
                    <input type="text" id="last_name" name="last_name"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div>
                <label for="company" class="block text-sm font-medium text-gray-700">Entreprise (optionnel)</label>
                <input type="text" id="company" name="company"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
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
                S'inscrire
            </button>
            
            <div class="text-center">
                <p class="text-sm text-gray-600">Déjà inscrit ? <a href="?auth/login" class="font-medium text-purple-600 hover:text-purple-500">Se connecter</a></p>
            </div>
        </form>
        
        <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
    </div>
</section>

<script>
document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirm_password = document.getElementById('confirm_password').value;
    const first_name = document.getElementById('first_name').value;
    const last_name = document.getElementById('last_name').value;
    const company = document.getElementById('company').value;
    
    if (password !== confirm_password) {
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = 'Les mots de passe ne correspondent pas';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    try {
        const response = await fetch('?auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password, confirm_password, first_name, last_name, company })
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
        errorDiv.textContent = 'Erreur lors de l\'inscription';
        errorDiv.classList.remove('hidden');
    }
});
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
