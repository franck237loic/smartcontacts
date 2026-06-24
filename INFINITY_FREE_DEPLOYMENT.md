# Déploiement sur Infinity Free

Infinity Free est un hébergeur gratuit qui offre PHP et MySQL. Voici comment déployer GlobalPhone Analytics sur Infinity Free.

## Prérequis

- Un compte Infinity Free (infinityfree.net)
- Un compte Stripe (pour les paiements)
- Accès FTP ou File Manager

## Étapes de déploiement

### 1. Créer un compte Infinity Free

1. Allez sur [infinityfree.net](https://infinityfree.net)
2. Créez un compte gratuit
3. Créez un nouveau compte (site) avec un nom de domaine gratuit (ex: smartcontacts.infinityfreeapp.com)

### 2. Configurer la base de données

1. Dans le panel Infinity Free, allez dans "MySQL Databases"
2. Créez une nouvelle base de données
3. Notez les informations de connexion :
   - Database Host (ex: sqlxxx.infinityfree.com)
   - Database Name
   - Database Username
   - Database Password

### 3. Importer la base de données

1. Dans le panel Infinity Free, allez dans "phpMyAdmin"
2. Sélectionnez votre base de données
3. Cliquez sur "Import"
4. Sélectionnez le fichier `database/smartcontacts_complete_updated.sql`
5. Cliquez sur "Go" pour importer

### 4. Configurer l'application

Infinity Free n'utilise pas les variables d'environnement comme Render. Vous devez modifier directement le fichier de configuration.

1. Téléchargez le fichier `app/Config/config.php`
2. Modifiez les valeurs de base de données :

```php
'database' => [
    'host' => 'votre-db-host.infinityfree.com',  // Ex: sql123.infinityfree.com
    'dbname' => 'votre-nom-db',
    'username' => 'votre-username',
    'password' => 'votre-password',
    'charset' => 'utf8mb4',
    // ...
],
```

3. Modifiez l'URL de l'application :

```php
'app' => [
    'name' => 'GlobalPhone Analytics',
    'env' => 'production',
    'debug' => false,
    'timezone' => 'Europe/Paris',
    'locale' => 'fr_FR',
    'url' => 'https://votre-site.infinityfreeapp.com'  // Votre URL Infinity Free
],
```

4. Configurez les clés Stripe (si vous avez un compte Stripe) :

```php
'stripe' => [
    'secret_key' => 'sk_live_votre_clé_secrète',
    'publishable_key' => 'pk_live_votre_clé_publique',
    'webhook_secret' => 'whsec_votre_webhook_secret',
    // ...
],
```

5. Sauvegardez le fichier

### 5. Uploader les fichiers

**Option 1: Via File Manager (recommandé)**

1. Dans le panel Infinity Free, allez dans "Online File Manager"
2. Naviguez vers le dossier `htdocs`
3. Supprimez le fichier `index2.php` par défaut
4. Uploader tous les fichiers de votre projet :
   - Le dossier `public/`
   - Le dossier `app/`
   - Le dossier `vendor/`
   - Le dossier `storage/`
   - Le fichier `composer.json` et `composer.lock`

> Important : sur Infinity Free, `htdocs` doit contenir le dossier `public/`, un `index.php` à la racine, et un fichier `.htaccess` racine qui redirige les requêtes vers `public/index.php`.

**Option 2: Via FTP**

1. Utilisez un client FTP (FileZilla, WinSCP)
2. Connectez-vous avec les informations FTP fournies par Infinity Free
3. Uploader tous les fichiers dans le dossier `htdocs`

### 6. Configurer les permissions

1. Dans le File Manager, assurez-vous que les dossiers suivants sont accessibles en écriture :
   - `storage/cache/`
   - `storage/logs/`
2. Cliquez droit sur ces dossiers et sélectionnez "Change Permissions"
3. Mettez les permissions à `755` ou `777` si nécessaire

### 7. Configurer le fichier .htaccess

Infinity Free utilise Apache, donc le fichier `.htaccess` dans `public/` devrait fonctionner. Assurez-vous que le contenu est uploadé dans le dossier `htdocs`.

### 8. Configurer le webhook Stripe (optionnel)

Si vous utilisez Stripe pour les paiements :

1. Allez dans votre dashboard Stripe
2. Ajoutez le webhook : `https://votre-site.infinityfreeapp.com/stripe/webhook`
3. Sélectionnez les événements : `checkout.session.completed`, `customer.subscription.deleted`

### 9. Tester l'application

1. Ouvrez votre navigateur sur `https://votre-site.infinityfreeapp.com`
2. Testez l'inscription et la connexion
3. Testez l'analyse de numéros de téléphone

## Limitations d'Infinity Free

Infinity Free a certaines limitations :

- **Pas de Redis** : Le cache Redis ne fonctionnera pas. Le système utilisera le fallback fichiers automatiquement.
- **Pas de variables d'environnement** : Vous devez configurer directement dans `config.php`
- **Limites de ressources** : CPU, RAM et bande passante limitées
- **Pas de HTTPS gratuit** : Vous pouvez utiliser le HTTPS fourni par Infinity Free ou utiliser Cloudflare pour un certificat SSL gratuit
- **Pas de cron jobs** : Les tâches planifiées ne fonctionneront pas automatiquement

## Alternatives pour le cache

Comme Infinity Free n'a pas Redis, le système utilisera automatiquement le cache fichiers. Pour optimiser :

1. Assurez-vous que le dossier `storage/cache/` existe et est accessible en écriture
2. Le système utilisera les fichiers comme fallback automatiquement

## Sécurité

- Ne commitez jamais vos vraies clés API dans le dépôt GitHub
- Changez le mot de passe admin par défaut
- Utilisez des mots de passe forts pour la base de données
- Activez HTTPS si possible

## Support

Pour toute question sur Infinity Free :
- Documentation : [infinityfree.net/support](https://infinityfree.net/support)
- Forum : [forum.infinityfree.net](https://forum.infinityfree.net)
