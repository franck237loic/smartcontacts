<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'GlobalPhone Analytics' ?></title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com;">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: transform 0.3s, box-shadow 0.3s; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="gradient-bg text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="?dashboard" class="flex items-center">
                        <i class="fas fa-globe text-2xl mr-3"></i>
                        <span class="font-bold text-xl">GlobalPhone Analytics</span>
                    </a>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="?dashboard" class="hover:text-gray-200 transition <?= $current_page === 'dashboard' ? 'font-semibold' : '' ?>">Dashboard</a>
                    <a href="?dashboard/analytics" class="hover:text-gray-200 transition <?= $current_page === 'analytics' ? 'font-semibold' : '' ?>">Analytics</a>
                    <a href="?dashboard/analyze" class="hover:text-gray-200 transition <?= $current_page === 'analyze' ? 'font-semibold' : '' ?>">Analyser</a>
                    <a href="?subscription/plans" class="hover:text-gray-200 transition <?= $current_page === 'subscription' ? 'font-semibold' : '' ?>">Abonnements</a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="relative">
                            <button onclick="toggleNotifications()" class="text-white hover:text-gray-200 transition">
                                <i class="fas fa-bell text-xl"></i>
                                <span id="notification-badge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                            </button>
                            <div id="notification-dropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl hidden z-50">
                                <div class="p-4 border-b">
                                    <h3 class="font-semibold text-gray-800">Notifications</h3>
                                </div>
                                <div id="notification-list" class="max-h-96 overflow-y-auto">
                                    <div class="p-4 text-gray-500 text-center">Chargement...</div>
                                </div>
                                <div class="p-4 border-t">
                                    <button onclick="markAllAsRead()" class="text-sm text-purple-600 hover:text-purple-800">Tout marquer comme lu</button>
                                </div>
                            </div>
                        </div>
                        <a href="?auth/profile" class="text-white hover:text-gray-200 transition">
                            <i class="fas fa-user mr-2"></i><?= htmlspecialchars($_SESSION['user_name'] ?? 'Mon compte') ?>
                        </a>
                        <a href="?auth/logout" class="bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                            Déconnexion
                        </a>
                    <?php else: ?>
                        <a href="?auth/login" class="text-white hover:text-gray-200 transition">Connexion</a>
                        <a href="?auth/register" class="bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                            Inscription
                        </a>
                    <?php endif; ?>
                </div>
                <div class="md:hidden">
                    <button onclick="toggleMobileMenu()" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-purple-700">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="?dashboard" class="block px-3 py-2 rounded-md hover:bg-purple-600 <?= $current_page === 'dashboard' ? 'bg-purple-600' : '' ?>">Dashboard</a>
                <a href="?dashboard/analytics" class="block px-3 py-2 rounded-md hover:bg-purple-600 <?= $current_page === 'analytics' ? 'bg-purple-600' : '' ?>">Analytics</a>
                <a href="?dashboard/analyze" class="block px-3 py-2 rounded-md hover:bg-purple-600 <?= $current_page === 'analyze' ? 'bg-purple-600' : '' ?>">Analyser</a>
                <a href="?subscription/plans" class="block px-3 py-2 rounded-md hover:bg-purple-600 <?= $current_page === 'subscription' ? 'bg-purple-600' : '' ?>">Abonnements</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="pt-2 border-t border-purple-600">
                        <a href="?auth/profile" class="block px-3 py-2 rounded-md hover:bg-purple-600">
                            <i class="fas fa-user mr-2"></i><?= htmlspecialchars($_SESSION['user_name'] ?? 'Mon compte') ?>
                        </a>
                        <a href="?auth/logout" class="block px-3 py-2 rounded-md hover:bg-purple-600">Déconnexion</a>
                    </div>
                <?php else: ?>
                    <div class="pt-2 border-t border-purple-600">
                        <a href="?auth/login" class="block px-3 py-2 rounded-md hover:bg-purple-600">Connexion</a>
                        <a href="?auth/register" class="block px-3 py-2 rounded-md hover:bg-purple-600">Inscription</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-globe text-2xl mr-3"></i>
                        <span class="font-bold text-xl">GlobalPhone Analytics</span>
                    </div>
                    <p class="text-gray-400">La plateforme d'analyse téléphonique la plus complète au monde.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Produit</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="?dashboard" class="hover:text-white">Dashboard</a></li>
                        <li><a href="?dashboard/analytics" class="hover:text-white">Analytics</a></li>
                        <li><a href="?dashboard/analyze" class="hover:text-white">Analyser</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Légal</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Confidentialité</a></li>
                        <li><a href="#" class="hover:text-white">CGU</a></li>
                        <li><a href="#" class="hover:text-white">RGPD</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2026 GlobalPhone Analytics. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        // Toggle mobile menu
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Notification functions
        let notificationsLoaded = false;

        function toggleNotifications() {
            const dropdown = document.getElementById('notification-dropdown');
            dropdown.classList.toggle('hidden');
            
            if (!dropdown.classList.contains('hidden') && !notificationsLoaded) {
                loadNotifications();
            }
        }

        function loadNotifications() {
            fetch('?notification/unread')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayNotifications(data.notifications);
                        updateNotificationBadge(data.unread_count);
                        notificationsLoaded = true;
                    }
                })
                .catch(error => console.error('Error loading notifications:', error));
        }

        function displayNotifications(notifications) {
            const list = document.getElementById('notification-list');
            
            if (notifications.length === 0) {
                list.innerHTML = '<div class="p-4 text-gray-500 text-center">Aucune notification</div>';
                return;
            }

            list.innerHTML = notifications.map(notif => `
                <div class="p-4 border-b hover:bg-gray-50 cursor-pointer ${notif.is_read ? 'opacity-60' : ''}" onclick="markAsRead(${notif.id}, '${notif.link || ''}')">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            ${getNotificationIcon(notif.type)}
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">${notif.title}</p>
                            <p class="text-sm text-gray-500 mt-1">${notif.message}</p>
                            <p class="text-xs text-gray-400 mt-1">${formatDate(notif.created_at)}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function getNotificationIcon(type) {
            const icons = {
                'info': '<i class="fas fa-info-circle text-blue-500"></i>',
                'success': '<i class="fas fa-check-circle text-green-500"></i>',
                'warning': '<i class="fas fa-exclamation-triangle text-yellow-500"></i>',
                'error': '<i class="fas fa-times-circle text-red-500"></i>'
            };
            return icons[type] || icons['info'];
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'À l\'instant';
            if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
            if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
            return date.toLocaleDateString('fr-FR');
        }

        function updateNotificationBadge(count) {
            const badge = document.getElementById('notification-badge');
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        function markAsRead(notificationId, link) {
            fetch('?notification/mark-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                    if (link) {
                        window.location.href = link;
                    }
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        }

        function markAllAsRead() {
            fetch('?notification/mark-all-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => console.error('Error marking all notifications as read:', error));
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notification-dropdown');
            if (!dropdown) return;
            
            const button = event.target.closest('button');
            
            if (!dropdown.contains(event.target) && !button?.onclick?.toString().includes('toggleNotifications')) {
                dropdown.classList.add('hidden');
            }
        });

        // Global JavaScript functions
        async function apiRequest(endpoint, data = {}) {
            try {
                const response = await fetch(`?${endpoint}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                return await response.json();
            } catch (error) {
                console.error('API Error:', error);
                return { success: false, error: error.message };
            }
        }
    </script>
</body>
</html>
