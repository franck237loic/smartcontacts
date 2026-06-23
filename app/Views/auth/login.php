<?php
$current_page = 'login';
$title = $title ?? 'Connexion - GlobalPhone Analytics';

ob_start();
?>

<!-- Login Section -->
<section class="min-h-screen gradient-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <i class="fas fa-globe text-6xl text-white mb-4"></i>
            <h2 class="text-3xl font-bold text-white">Connexion</h2>
            <p class="mt-2 text-gray-200">Connectez-vous à votre compte GlobalPhone Analytics</p>
        </div>
        
        <form id="login-form" class="mt-8 bg-white rounded-xl shadow-lg p-8 space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Se souvenir de moi</label>
                </div>
                <a href="?auth/forgot-password" class="text-sm text-purple-600 hover:text-purple-500">Mot de passe oublié ?</a>
            </div>
            
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                Se connecter
            </button>
            
            <div class="text-center">
                <p class="text-sm text-gray-600">Pas encore de compte ? <a href="?auth/register" class="font-medium text-purple-600 hover:text-purple-500">S'inscrire</a></p>
            </div>
        </form>
        
        <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
    </div>
</section>

<script>
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    try {
        const response = await fetch('?auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
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
        errorDiv.textContent = 'Erreur de connexion';
        errorDiv.classList.remove('hidden');
    }
});
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
