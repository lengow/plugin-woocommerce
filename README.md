# Installation WooCommerce #

## Installation du module ##

### Cloner le repository de Bitbucket dans votre espace de travail ###

Attention la version 3.5 de wordpress ne fonctionne pas avec les liens symboliques.
On va alors installer le module directement dans le dossier plugin de la 3.5 et faire des liens symboliques vers les autres versions.

    cd ~/Documents/sites/w352-w200/wordpress/wp-content/plugins/
    git clone git@bitbucket.org:lengow-dev/woocommerce-v3.git lengow-woocommerce
    chmod 777 -R ~/Documents/sites/w352-w200/wordpress/wp-content/plugins/lengow-woocommerce

### Installation dans Wordpress ###

    cd ~/Documents/sites/w352-w200/wordpress/wp-content/plugins/lengow-woocommerce/tools
    sh install.sh ~/Documents/docker_images/w461-w264

Le script va créer des liens symboliques vers les sources du module (Dans notre cas vers la version w461-w264)
Activer l'extension Lengow for WooCommerce dans le menu Extensions / Extensions installées

## Gestion des Traductions ##

Pour traduire le projet il faut modifier les fichier *.yml dans le répertoire : Documents/sites/w352-w200/wordpress/wp-content/plugins/lengow-woocommerce/translations/yml/

### Installation de Yaml Parser ###

    sudo apt-get install php5-dev libyaml-dev
    sudo pecl install yaml

### Mise à jour des traductions ###

Une fois les traductions terminées, il suffit de lancer le script de mise à jour de traduction :

    cd ~/Documents/sites/w352-w200/wordpress/wp-content/plugins/lengow-woocommerce/tools
    php translate.php

## Mise à jour du fichier d'intégrité des données ##

    cd ~/Documents/sites/w352-w200/wordpress/wp-content/plugins/lengow-woocommerce/tools
    php checkmd5.php

Le fichier checkmd5.csv sera directement créé dans le dossier /toolbox

## Compiler le module ##

    cd ~/Documents/sites/w352-w200/wordpress/wp-content/plugins/lengow-woocommerce/tools
    sh build.sh x.x.x

Le x.x.x représente la version du module qu'il faudra modifier.
Le module est alors directement compilé et copier sur le bureau avec le bon nom de version.

## Versionning GIT ##

1 - Prendre un ticket sur JIRA et cliquer sur Créer une branche dans le bloc développement à droite

2 - Sélectionner en "Repository" lengow-dev/woocommerce-v3, pour "Branch from" prendre dev et laisser le nom du ticket pour "Branch name"

3 - Créer la nouvelle branche

4 - Exécuter le script suivant pour changer de branche 

    cd ~/Documents/sites/w352-w200/wordpress/wp-content/plugins/lengow-woocommerce/
    git fetch
    git checkout "Branch name"

5 - Faire le développement spécifique

6 - Lorsque que le développement est terminé, faire un push sur la branche du ticket

    git add .
    git commit -m 'My ticket is finished'
    git pull origin "Branch name"
    git push origin "Branch name"

7 - Dans Bitbucket, dans l'onglet Pull Requests créer une pull request

8 - Sélectionner la branche du tiket et l'envoyer sur la branche de dev de lengow-dev/woocommerce-v3

9 - Bien nommer la pull request et mettre toutes les informations nécessaires à la vérification

10 - Mettre tous les Reviewers nécessaires à la vérification et créer la pull request

11 - Lorsque la pull request est validée, elle sera mergée sur la branche de dev