# Déploiement automatique sur Infinity Free

## Structure d upload pour Infinity Free

Votre site doit être organisé comme ceci dans `htdocs` :

- `app/`
- `vendor/`
- `storage/`
- `public/`
- `index.php`
- `.htaccess`
- `INFINITY_FREE_DEPLOYMENT.md`

> Ne copiez pas seulement le contenu de `public/`. Le fichier `index.php` de la racine redirige correctement vers `public/index.php`.

## Fichiers importants ajoutés

- `index.php` : point d entrée racine qui charge `public/index.php`
- `.htaccess` : redirection des requêtes vers `public/index.php`
- `.github/workflows/deploy.yml` : workflow GitHub Actions pour déployer par FTP

## Utilisation GitHub Actions

1. Créez le dépôt GitHub et poussez votre projet sur la branche `main`.
2. Dans GitHub, allez dans `Settings` > `Secrets` > `Actions`.
3. Créez ces secrets :
   - `FTP_SERVER` : votre hôte FTP InfinityFree (ex: `ftpupload.net` ou l’hôte donné par InfinityFree)
   - `FTP_USERNAME` : votre utilisateur FTP
   - `FTP_PASSWORD` : votre mot de passe FTP
4. Le workflow `deploy.yml` se déclenchera à chaque push sur `main`.

## Exemple de workflow

Le workflow utilise l’action `SamKirkland/FTP-Deploy-Action` pour envoyer tous les fichiers au serveur.

## Important

- Le dossier `.github/` n’est pas envoyé sur le serveur.
- Le workflow supprime (`--delete`) les fichiers distants qui n’existent plus localement.
- Vérifiez que `public/.htaccess` et le `.htaccess` racine sont bien présents avant de pousser.

## Si vous uploadz tout le dossier `smartcontacts`

Si vous mettez le dossier `smartcontacts` dans `htdocs`, votre site sera accessible à :

- `https://votresite.infinityfreeapp.com/smartcontacts/`

### Ce qu’il faut modifier

1. Dans `app/Config/config.php`, mettez :

```php
'app' => [
    'url' => 'https://votresite.infinityfreeapp.com/smartcontacts'
],
```

2. Si vous utilisez des routes ou des liens qui pointent vers `/`, sachez que l’URL de base est maintenant `/smartcontacts/`.

3. Votre dossier `htdocs` sur le serveur doit contenir exactement :
   - `smartcontacts/`
   - pas d’autres fichiers au même niveau si vous voulez garder cette structure

### Si vous voulez accéder au site sans `/smartcontacts`

Il faut alors uploader le contenu du dossier `smartcontacts` directement dans `htdocs` (et non pas le dossier lui-même).
