# Daily-movies-2 : 
- Blog de review de films avec articles et categories
- Un film peut avoir plusieurs catégories
- Une catégorie peut contenir plusieurs films
- Plusieurs rôles pour les utilisateurs
- Un utilisateur a automatiquement le rôle User et un rôle Auteur
- Un Administrateur a le rôle Admin ainsi que les rôles User et Auteur
- Un administrateur peut ajouter, modifier ou supprimer un utilisateur
- Chaque utilisateur peut modifier ou supprimer un de ses aritcles ajoutés
- Un administrateur peut modifier ou supprimer tout les articles de tout les utilisateurs
- Un administrateur peut modifier ou supprimer une ou des catégories

  ## ***Attention***
  
- Aucune catégorie ne peut être supprimer si elle contient des articles
- Aucun utilisateur ne peut être supprimer si il a des articles
- Le password par defaut des User est `password123`




Prérequis : 
 >- PHP
 >- Composer
 >- Symfony CLI (en option, mais recommandé)
 > - Symfony ( 6.3 )
 >- Un serveur de base de données ( MySQL )

## Toutes les étapes sont à suivre dans l'ordre indiqué.

Comment installer ? :

**1. Ouvrir le terminal de votre choix et cloner le projet depuis Github avec cette commande ci-dessous :**

	>git clone https://github.com/FMCParadise/dailymovies2_beta.git
 
**2. Aller dans le dossier du projet cloner**
	
	>cd <NOM_DU_DOSSIER_DU_PROJET>

**3. Initialisation de symfony :**
	
	>symfony init

**4. Installation des dépendances a l'aide de `composer`**
	
	>composer install

**5. Création et Mise à jour de la base de données**

Pour créer la base de données il faut executer cette commande :

   	>symfony console doctrine:database:create

Ensuite : 

 	>symfony console make:migration

Pour finir :

	>symfony console doctrine:migration:migrate

**6. Charger les DataFixtures :**
Toujours dans la console, taper : 

	>symfony console doctrine:datafixtures:load 

**7. Lancement du serveur symfony**
	

	>symfony serve
