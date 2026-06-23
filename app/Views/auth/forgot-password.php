<?php
$current_page = 'forgot-password';
$title = $title ?? 'Mot de passe oublié - GlobalPhone Analytics';

ob_start();
?>

<!-- Forgot Password Section -->
<section class="min-h-screen gradient-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <i class="fas fa-globe text-6xl text-white mb-4"></i>
            <h2 class="text-3xl font-bold text-white">Mot de passe oublié</h2>
            <p class="mt-2 text-gray-200">Entrez votre email pour recevoir un lien de réinitialisation</p>
        </div>
        
        <form id="forgot-form" class="mt-8 bg-white rounded-xl shadow-lg p-8 space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                Envoyer le lien
            </button>
            
            <div class="text-center">
                <a href="?auth/login" class="text-sm text-purple-600 hover:text-purple-500">Retour à la connexion</a>
            </div>
        </form>
        
        <div id="message" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
        <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
    </div>
</section>

<script>
document.getElementById('forgot-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    
    try {
        const response = await fetch('?auth/forgot-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = data.message;
            messageDiv.classList.remove('hidden');
            document.getElementById('error-message').classList.add('hidden');
        } else {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = data.message;
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = 'Erreur lors de la demande';
        errorDiv.classList.remove('hidden');
    }
});
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
