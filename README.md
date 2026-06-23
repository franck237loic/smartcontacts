# GlobalPhone Analytics

Plateforme SaaS d'analyse téléphonique mondiale - Architecture MVC professionnelle et scalable.

## 🏗️ Architecture MVC

L'application suit une architecture MVC (Model-View-Controller) stricte pour une meilleure maintenabilité et scalabilité.

### Structure des dossiers

```
smartcontacts/
├── app/
│   ├── Config/          # Fichiers de configuration
│   │   └── config.php   # Configuration de l'application
│   ├── Controllers/    # Contrôleurs (logique métier)
│   ├── Core/           # Classes core du framework
│   ├── Middleware/     # Middleware (authentification, rate limiting, etc.)
│   ├── Models/         # Modèles (accès aux données)
│   ├── Routes/         # Définition des routes
│   ├── Services/       # Services externes (Stripe, Cache, etc.)
│   └── Views/          # Vues (templates)
├── public/             # Point d'entrée public
│   ├── index.php       # Entry point
│   └── assets/         # Assets statiques
├── database/           # Fichiers SQL
└── storage/            # Stockage (cache, logs)
```

## 🚀 Installation

### Prérequis
- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Apache avec mod_rewrite activé (ou serveur PHP intégré)
- Composer

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone https://github.com/franck237loic/smartcontacts.git
cd smartcontacts
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Importer la base de données**
```bash
mysql -u root -p smartcontacts < database/smartcontacts_complete_updated.sql
```

4. **Configurer la base de données**
Les variables d'environnement sont utilisées. Créez un fichier `.env` ou configurez-les directement :
- `DB_HOST` - Hôte de la base de données
- `DB_NAME` - Nom de la base de données
- `DB_USER` - Utilisateur de la base de données
- `DB_PASS` - Mot de passe de la base de données

5. **Démarrer le serveur**

Option 1: Serveur PHP intégré (développement)
```bash
php -S localhost:8000 -t public
```

Option 2: Apache (production)
- Configurez votre VirtualHost pour pointer vers le dossier `public/`
- Assurez-vous que mod_rewrite est activé

6. **Accéder à l'application**
Ouvrez votre navigateur sur `http://localhost:8000`

## 🚀 Déploiement sur Render

### Prérequis
- Un compte Render (render.com)
- Un compte Stripe (pour les paiements)
- Une base de données PostgreSQL ou MySQL (Render Database)

### Étapes de déploiement

1. **Créer un nouveau service Web sur Render**
   - Connectez votre dépôt GitHub
   - Render détectera automatiquement le fichier `render.yaml`

2. **Configurer les variables d'environnement**
   Dans le dashboard Render, ajoutez les variables suivantes :
   
   ```
   APP_ENV=production
   APP_URL=https://votre-app.onrender.com
   DB_HOST=votre-db-host.render.com
   DB_NAME=votre-db-name
   DB_USER=votre-db-user
   DB_PASS=votre-db-password
   STRIPE_SECRET_KEY=sk_live_votre_clé_secrète
   STRIPE_PUBLISHABLE_KEY=pk_live_votre_clé_publique
   STRIPE_WEBHOOK_SECRET=whsec_votre_webhook_secret
   REDIS_HOST=votre-redis-host.render.com
   REDIS_PORT=6379
   REDIS_PASSWORD=votre-redis-password
   ```

3. **Créer les services Render**
   - **Web Service** : Application PHP
   - **PostgreSQL/MySQL** : Base de données
   - **Redis** : Cache (optionnel, recommandé)

4. **Importer la base de données**
   ```bash
   # Connectez-vous à votre base de données Render
   psql -h votre-db-host.render.com -U votre-db-user -d votre-db-name
   
   # Importez le fichier SQL
   \i database/smartcontacts_complete_updated.sql
   ```

5. **Configurer le webhook Stripe**
   - Allez dans votre dashboard Stripe
   - Ajoutez le webhook : `https://votre-app.onrender.com/stripe/webhook`
   - Sélectionnez les événements : `checkout.session.completed`, `customer.subscription.deleted`

6. **Déployer**
   - Render déploiera automatiquement à chaque push sur GitHub

## 🎯 Fonctionnalités

### Dashboard
- Vue d'ensemble des statistiques
- Recherche rapide de numéros
- Graphiques de distribution
- Liste des opérateurs récents

### Analytics
- Distribution par continent
- Distribution par pays
- Distribution par marque d'opérateur
- Visualisations interactives avec Chart.js

### Analyse
- Analyse individuelle de numéros
- Analyse par lot (batch)
- Filtres par pays et marque
- Export des résultats

### Authentification Utilisateurs
- Inscription et connexion
- Réinitialisation de mot de passe
- Gestion du profil utilisateur
- Middleware d'authentification
- Protection des routes sensibles

### Système d'Abonnement
- Plans d'abonnement (Free, Pro, Enterprise)
- Intégration Stripe pour les paiements
- Gestion des abonnements utilisateur
- Suivi du quota API
- Annulation d'abonnement

### API Avancée
- Authentification par clé API
- Rate limiting (60 requêtes/minute, 1000/heure)
- Documentation OpenAPI/Swagger
- Headers de rate limit
- Validation des clés API

### Dashboard Utilisateur Personnalisé
- Statistiques d'utilisation
- Historique des recherches
- Gestion des clés API
- Export CSV des résultats
- Vue d'ensemble de l'abonnement

### Détection de Spam/Fraude
- Analyse de risque pour les numéros
- Détection de numéros surtaxés
- Détection de numéros virtuels
- Système de signalement

### Géolocalisation
- Géolocalisation par indicateur
- Données de pays et régions
- Recherche par continent
- Coordonnées GPS

### Notifications et Alertes
- Système de notifications en temps réel
- Types : info, success, warning, error
- Badge de compteur non lus
- Dropdown de notifications

### Logs d'Audit
- Traçage des actions utilisateurs
- Stockage des anciennes/nouvelles valeurs
- IP address et user agent
- Historique complet

### Workspaces Collaboratifs
- Création de workspaces
- Gestion des membres (owner, admin, member)
- Permissions par rôle
- Collaboration en équipe

### Cache Redis
- Support Redis avec fallback fichiers
- Méthodes : get, set, delete, clear, has, remember
- Nettoyage automatique des caches expirés

### Intégrations Zapier/CRM
- Support Zapier (webhooks)
- Support HubSpot
- Support Salesforce
- Support Pipedrive
- Configuration JSON flexible

## 🔐 Sécurité

- Validation des entrées
- Protection contre les injections SQL (PDO prepared statements)
- Configuration Apache pour protéger les fichiers sensibles
- Gestion des erreurs en production
- Rate limiting pour l'API
- Authentification par clé API

## 📈 Scalabilité

L'architecture MVC permet une scalabilité facile :

1. **Ajout de nouveaux contrôleurs** : Créez un nouveau contrôleur dans `app/Controllers/`
2. **Ajout de nouveaux modèles** : Créez un nouveau modèle dans `app/Models/`
3. **Ajout de nouvelles routes** : Ajoutez vos routes dans `app/Routes/web.php`
4. **Middleware** : Ajoutez des middleware pour l'authentification, rate limiting, etc.

## 🔄 Prochaines étapes

- [x] Système d'authentification utilisateurs
- [x] API REST avec authentification par token
- [x] Système d'abonnement et paiement
- [x] Rate limiting pour l'API
- [x] Intégration Stripe pour les paiements réels
- [x] Cache des requêtes (Redis)
- [ ] Tests unitaires
- [x] Documentation API (Swagger/OpenAPI)
- [ ] Dockerisation
- [x] Notifications et alertes
- [x] Workspaces collaboratifs
- [x] Logs d'audit
- [x] Intégrations Zapier/CRM

## 📝 Conventions de code

- **PSR-12** pour le style de code
- **Namespaces** pour l'organisation des classes
- **Type hinting** pour les paramètres et retours
- **Documentation** PHPDoc pour les classes et méthodes

## 🤝 Contribution

1. Fork le projet
2. Créez une branche pour votre fonctionnalité
3. Commit vos changements
4. Push vers la branche
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est propriétaire. Tous droits réservés.

## 👥 Auteurs

- GlobalPhone Analytics Team

## 📞 Support

Pour toute question ou problème, contactez le support via :
- Email : support@globalphone-analytics.com
