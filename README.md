
# Symfony Project - Installation Guide

Bienvenue dans le projet Symfony **Stubborn** en mode développement. Ce guide vous expliquera comment configurer et lancer l'application.

## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre machine :

- PHP >= 8.0
- Composer
- Une base de données (MySQL, PostgreSQL, etc.)
- Symfony CLI (optionnel, mais recommandé pour le développement)
- Node.js et npm (pour gérer les assets avec Webpack Encore)

## Installation

### 1. Cloner le projet

Commencez par cloner le dépôt Git dans votre répertoire local :

```bash
git clone https://github.com/devlivepro/Stubborn.git
```

```bash
cd Stubborn
```


### 2. Installer les dépendances PHP

Utilisez Composer pour installer toutes les dépendances PHP nécessaires au projet :

```bash
composer install
```

### 3. Configurer les variables d'environnement

Créez un fichier `.env.local` en copiant le fichier `.env`, puis configurez-le avec vos informations de base de données et autres paramètres :

```bash
cp .env .env.local
```

Ensuite, modifiez le fichier `.env.local` avec vos paramètres :

```bash
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"
```

### 4. Créer la base de données, commande standard ou plus de détails en fin de page

Créez la base de données avec la commande suivante :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Si vous avez déjà une base de données existante et souhaitez lancer uniquement les migrations, exécutez :

```bash
php bin/console doctrine:migrations:migrate
```

### 5. Installer les dépendances JavaScript et compiler les assets

Si vous utilisez **Webpack Encore** pour gérer les assets, installez les dépendances JavaScript avec **npm** et compilez les assets :

```bash
npm install
npm run dev
```

### 6. Lancer le serveur de développement

Pour lancer le serveur Symfony en mode développement, utilisez la commande suivante :

```bash
symfony server:start
```

Si vous ne souhaitez pas utiliser le **Symfony CLI**, vous pouvez également lancer le serveur PHP intégré avec :

```bash
php -S localhost:8000 -t public/
```

### 7. Consommer les messages pour la validation par email

Utilisez cette commande pour démarrer le consommateur de messages (seulement en mode dev) :

```bash
php bin/console messenger:consume async --time-limit=3600
```

### 8. Compte Utilisateur et Administrateur

#### Compte utilisateur pour la démo (URL : `/login`)

```bash
Email: johndoe@hotmail.com
Mot de passe: johndoe1234
```

#### Compte administrateur pour la démo (URL : `/admin`)

```bash
Email: johndoeadmin@hotmail.com
Mot de passe: johndoe1234
```

#### Compte Utilisateur non vérifier pour la démo (URL : `/login`)

```bash
Email: johndoe0@hotmail.com
Mot de passe: johndoe1234
```

## Tests

Pour lancer les tests (si des tests sont définis dans votre projet) :

```bash
php bin/phpunit
```

## Migration base de données

Pour lancer la migration :

Création de la db principale :

```bash
php bin/console doctrine:database:create
```

Charge la structure données db principale :

```bash
php bin/console doctrine:migrations:migrate
```

Migration des données :

```bash
php bin/console doctrine:fixtures:load --group=ProductDataFixtures --append
```

```bash
php bin/console doctrine:fixtures:load --group=UserFixtures --append
```

Création de la db test :

```bash
php bin/console doctrine:database:create --env=test
```

Migration des données test :


```bash
php bin/console doctrine:migrations:migrate --env=test
```

```bash
php bin/console doctrine:fixtures:load --group=TestProductDataFixtures --env=test --append
```

```bash
php bin/console doctrine:fixtures:load --group=TestUserFixtures --env=test --append
```

## Paiement

Pour effectuer un paiement en mode test, stripe fournie un numéro de carte test:

Numéro de carte : 

```bash
4242 4242 4242 4242
```

Date d'expiration : n'importe quelle date valide (par exemple : 12/34)
CVC : n'importe quel code à 3 chiffres (par exemple : 123)