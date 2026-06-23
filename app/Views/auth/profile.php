<?php
$current_page = 'profile';
$title = $title ?? 'Mon Profil - GlobalPhone Analytics';
$user = $user ?? [];

ob_start();
?>

<!-- Profile Section -->
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold">Mon Profil</h1>
            <p class="text-gray-600">Gérez vos informations personnelles</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Information -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold mb-6"><i class="fas fa-user mr-2"></i>Informations personnelles</h2>
                <form id="profile-form" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">Prénom</label>
                            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-500">
                    </div>
                    
                    <div>
                        <label for="company" class="block text-sm font-medium text-gray-700">Entreprise</label>
                        <input type="text" id="company" name="company" value="<?= htmlspecialchars($user['company'] ?? '') ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Mettre à jour
                    </button>
                </form>
                
                <div id="profile-message" class="hidden mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
            </div>
            
            <!-- Change Password -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold mb-6"><i class="fas fa-lock mr-2"></i>Changer le mot de passe</h2>
                <form id="password-form" class="space-y-6">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 caractères</p>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Changer le mot de passe
                    </button>
                </form>
                
                <div id="password-message" class="hidden mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
                <div id="password-error" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
            </div>
        </div>
        
        <!-- Account Info -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold mb-6"><i class="fas fa-info-circle mr-2"></i>Informations du compte</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="text-sm text-gray-500">Rôle</div>
                    <div class="font-semibold"><?= ucfirst($user['role'] ?? 'user') ?></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Email vérifié</div>
                    <div class="font-semibold"><?= $user['email_verified'] ? 'Oui' : 'Non' ?></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Membre depuis</div>
                    <div class="font-semibold"><?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const first_name = document.getElementById('first_name').value;
    const last_name = document.getElementById('last_name').value;
    const company = document.getElementById('company').value;
    
    try {
        const response = await fetch('?auth/profile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ first_name, last_name, company })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const messageDiv = document.getElementById('profile-message');
            messageDiv.textContent = data.message;
            messageDiv.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
    }
});

document.getElementById('password-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const current_password = document.getElementById('current_password').value;
    const new_password = document.getElementById('new_password').value;
    const confirm_password = document.getElementById('confirm_password').value;
    
    if (new_password !== confirm_password) {
        const errorDiv = document.getElementById('password-error');
        errorDiv.textContent = 'Les mots de passe ne correspondent pas';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    try {
        const response = await fetch('?auth/change-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ current_password, new_password, confirm_password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const messageDiv = document.getElementById('password-message');
            messageDiv.textContent = data.message;
            messageDiv.classList.remove('hidden');
            document.getElementById('password-error').classList.add('hidden');
            document.getElementById('password-form').reset();
        } else {
            const errorDiv = document.getElementById('password-error');
            errorDiv.textContent = data.message;
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
    }
});
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layout.php';
