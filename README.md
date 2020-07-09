Play Online Housie 
==================

Download Repository
-------------------

    git clone https://github.com/lyrixx/Silex-Kitchen-Edition
    cd Silex-Kitchen-Edition


Donload and install composer
----------------------------

    https://getcomposer.org/download/

### Update Dependancies via composer

    composer update
   
Import Database
---------------
 
    Create table housie and import db_import.sql file in database

.env file setup
---------------

    Rename .env_default file to .env  
    
    Update file details such as base url , app env , mysql database credentials and firebase credentials file path (saved in firebase_service_account_cred.json file) 

Firebase Credentials
--------------------

    Update firebase_config.js file and firebase_service_account_cred.json
