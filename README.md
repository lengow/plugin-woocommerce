# Module WooCommerce Lengow #

## Installation ##

### Installation de WooCommerce ###

1 - Aller sur le site de Wordpress : https://fr.wordpress.org/

2 - Télecharger la dernière version de Wordpress au format zip (ex: wordpress-4.6.zip)

3 - Aller récupérer l'extension WooCommerce : https://fr.wordpress.org/plugins/woocommerce/

4 - Télecharger la dernière version de WooCommerce (ex: woocommerce-2.6.4.zip)

5 - Décompresser l'archive Wordpress dans le projet dans /var/www/woocommerce/woocommerce264

5 - Décompresser l'archive WooCommerce dans les dossier plugins du projet dans /var/www/woocommerce/woocommerce264/wp-content/plugins

6 - Modification du fichier /etc/hosts

    echo "127.0.0.1 woocommerce264.local" >> /etc/hosts

7 - Création du fichier virtualhost d'apache

    sudo vim /etc/apache2/sites-enabled/woocommerce264.conf 
    <VirtualHost *:80>
    DocumentRoot /var/www/woocommerce/woocommerce264/
    ServerName prestashop-1-6.local
    <Directory /var/www/woocommerce/woocommerce264/>
        Options FollowSymLinks Indexes MultiViews
        AllowOverride All
    </Directory>
        ErrorLog /var/log/apache2/woocommerce264-error_log
        CustomLog /var/log/apache2/woocommerce264-access_log common
    </VirtualHost>

8 - Rédémarrer apache

    sudo service apache2 restart
    
9 - Creation de la base de données
    
    mysql -u root -p -e "CREATE DATABASE woocommerce_264"; 
        
10 - Se connecter sur Wordpress pour lancer l'installation
    
    http://woocommerce264.local

11 - Activer l'extension WooCommerce dans le menu Extensions / Extensions installées

### Récupération des sources ###

Cloner le repo dans votre espace de travail :

    cd /var/www/woocommerce/
    git clone git@bitbucket.org:lengow-dev/woocommerce-v3.git lengow-woocommerce

### Installation dans Wordpress ###

Exécuter le script suivant :

    cd /var/www/woocommerce/lengow-woocommerce/tools
    ./install.sh /var/www/woocommerce/woocommerce264/wp-content/plugins

Le script va créer des liens symboliques vers les sources du module

Activer l'extension Lengow for WooCommerce 2.x dans le menu Extensions / Extensions installées

## Versionning GIT ##

1 - Prendre un ticket sur JIRA et cliquer sur Créer une branche dans le bloc développement à droite

2 - Sélectionner en "Repository" lengow-dev/woocommerce-v3, pour "Branch from" prendre dev et laisser le nom du ticket pour "Branch name"

3 - Créer la nouvelle branche

4 - Exécuter le script suivant pour changer de branche 

    cd /var/www/woocommerce/lengow-woocommerce/
    git checkout -b "Branch name"

5 - Faire le développement spécifique

6 - Lorsque que le développement est terminé, faire un push sur la branche du ticket

git add .
git commit -m 'My ticket is finished'
git push -u origin "Branch name"

7 - Dans Bitbucket, dans l'onglet Pull Requests créer une pull request

8 - Sélectionner la branche du tiket et l'envoyer sur la branche de dev de lengow-de/woocommerce-v3

9 - Bien nommer la pull request et mettre toutes les informations nécessaires à la vérification

10 - Mettre tous les Reviewers nécessaires à la vérification et créer la pull request

11 - Lorsque la pull request est validée, elle sera mergée sur la branche de dev

## Traduction ##

Pour traduire le projet il faut modifier les fichier *.yml dans le répertoire : /var/www/woocommerce/lengow-woocommerce/translations/yml/

### Installation de Yaml Parser ###

    sudo apt-get install php5-dev libyaml-dev
    sudo pecl install yaml

### Mise à jour des traductions ###

Une fois les traductions terminées, il suffit de lancer le script de mise à jour de traduction :

    cd /var/www/woocommerce/lengow-woocommerce/tools
    php translate.php

## Mise à jour du fichier d'intégrité des données ##

Une fois le développement terminé et avant de publier une version du module, il faut regénérer le fichier checkmd5.csv :

    cd /var/www/woocommerce/lengow-woocommerce/tools
    php checkmd5.php
