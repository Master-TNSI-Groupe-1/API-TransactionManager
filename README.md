### Tutoriel installation SLIM FRAMEWORK ###

- 1 Exécuté la commande suivante :

php -v


- 2 SI PHP not install

installer PHP


- 3 Dans votre dossier cloné exécuté la commande suivante

php composer.phar require slim/slim:3.*


- 4 SI COMPOSER NOT INSTALL exécuté les commandes suivantes :
 
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"


-  5 Réexécuté la commande

php composer.phar require slim/slim:3.*

- 6 ALLER DANS LE DOSSIER src/ et exécuté les commandes suivantes :

php -S localhost:8080


- 7 TESTER LES URLS CI DESSOUS

http://localhost:8080/get/champion/*votre nom*
http://localhost:8080/get/champion



- 8 GENEGER swagger.json afin d'exporter les annotations au swagger

vendor/bin/openapi --format json --output swagger/swagger.json src