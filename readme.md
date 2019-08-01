# BEEP - Open source bee monitoring (v2.1.1)


BEEP is a combination of a bee monitoring (Laravel PHP) framework API + an (Angular JS) app and a (Influx) time series sensor data database. There are also first steps of creating cost efficient measurement hardware.

It's key feature is to integrate a user friendly responsive app for manual inspections with automatically measured sensor data.

Create a login and check the live app at: https://app.beep.nl

# System overview
![BEEP System overview](https://github.com/beepnl/BEEP/raw/master/BEEP-system-overview.png)

You are free to use the BEEP app, it's free and it will be developed further in the near future. If you would like to install it on your own server, or contribute; please read on below.

# Installation of API and APP (on your own server)

## 0. Server specs

* Linux Debian (8+)
* Software installed
  * PHP 7.1+
  * MariaDB/MySQL
  * Apache 2 (or Nginx, creating Nginx 'server blocks' in step 4)
  * InfluxDB (https://docs.influxdata.com/influxdb/v1.7/introduction/installation/)
  * [Composer](https://getcomposer.org/download/) - Installation tool for PHP/Laravel dependencies for API
  * [npm](https://www.npmjs.com/get-npm) - Installation tool for Javascript/Angular dependencies for App
* Optional: Letsencrypt SSL certificate generation


**NB: You can use this gist to install all the software you need: [LAMP PHP 7.2](https://gist.github.com/pvgennip/84f935e13207db71259f1f57c2667bbd)**


## 1. Clone this repo anywhere you like
```git clone https://github.com/beepnl/BEEP.git```

## 2. Database

Create a MySQL database (type: InnoDB, encoding: UTF_8, collation: utf8_unicode_ci) called: 

```bee_data```

**NB: Make sure to pass the user and credentials to the newly made .env file after step 3.**


## 3. Install required vendor libraries 

a. Make the ```run_actions.sh``` file executable by ```chmod +x run_actions.sh```. This bash script will install all the packages and vendor dependencies that you need at once.

b. Then run it: ```./run_actions.sh```

## 4. Set up your end-points

a. Make sure your server has 2 different virtual hosts for the API and the APP

NB: On AMAzon AWS (Bitnami LAMP_7.1): 
a. Make sure your Bitnami server includes your apps/apache_vhost.conf in 
```
sudo nano /opt/bitnami/apache2/conf/bitnami/bitnami-apps-vhosts.conf
```

Contains one or more Includes:
```
# Bitnami applications installed in a Virtual Host
Include "/opt/bitnami/apps/apache/portal-vhost.conf"
```


API (replace 'beep.nl' with your own server/test domain)
```
<VirtualHost api.beep.nl:80>
    
    DocumentRoot /var/www/bee/public
    ServerName "api.beep.nl"

    <Directory /var/www/bee/public/>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
    </Directory>

</VirtualHost>
```

APP (replace 'beep.nl' with your own server)
```
<VirtualHost app.beep.nl:80>
    
    DocumentRoot /var/www/bee/public/webapp
    ServerName "app.beep.nl"

    <Directory /var/www/bee/public/webapp/>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
    </Directory>

</VirtualHost>
```

b. Optionally, install SSL certificates to your endpoints with [Let's Encrypt](https://letsencrypt.org/getting-started/)

With command: ```sudo certbot --authenticator webroot --installer apache```

## 5. Add Influx database
```
influx
> CREATE USER user_influx WITH PASSWORD 'pass_influx' WITH ALL PRIVILEGES
> CREATE DATABASE bee_data
> exit
```
**NB: Make sure to pass the user and credentials to the .env file that has been created in step 3.**
**NB: If your Influx version was < 1.1.x (no TSI support), when using backups to transfer data: first install the old version that you are currently using on a new server, import the backups, then update to the newest Influx version!**

## 6. If you would like to easily deploy your fork (or this repo), 

a. Make sure to add your repo to git remote: ```git remote set url https://github.com/beepnl/BEEP.git```

b. Run ```./deploy.sh``` to update your clone on any server


# Configuring the back-end system

## 1. Check database tables

If you did not run ```./run_actions```, please do so, to install all the database tables and set up the default categorization.

## 2. Set up valid credentials

a. Set up e-mail credentials in the ```.env``` config file

b. For the webapp to reach the API, rename the file 'public/webapp/js/constants.js.example' to 'public/webapp/js/constants.js' and edit it to change the 'api_url' to your own back-end API end-point


## 3. Register new user

a. Go to ```api.[your_domain]/webapp#!/login/create```

b. Create a new user

c. Promote the user to 'superadmin'

Open your database (with a database viewer like PhpMyadmin, Sequel Pro, MySQL Workbench, etc.), or just do a command line query: 
Define a relation in table ```role_user``` with ```(1,1)``` (linking user_id 1 to role_id 1). 

Your user has just been promoted to the super administrator of the system, so you can use the back-end management interface to configure the complete system.

d. Go to ```api.[your_domain]/admin``` to log in with the same credentials that you created your app user with in step 3b.

e. You should see the back-end dashboard, looking like this:

## Management interface
![BEEP Management interface](https://github.com/beepnl/BEEP/blob/bob-additions/BEEP-management-interface.png)


# Contributing

Thank you for considering contributing to the BEEP framework! If you would like to contribute, please fork this repository, edit on your Github account, and finally send Pull Requests to this repository to include new features.

## Adding a language

1. Create a Beep user account at https://app.beep.nl/#!/login/create
2. Fork this repo
3. Send an e-mail to info@beep.nl with you user e-mail address, asking to become a translator for a certain language
4. Log into the backend to start translating


# Roadmap

Please request access to https://trello.com/b/Eb3CcKES/beep-hive-check-app if you would like to see the roadmap and cooperate.

### In short on our roadmap:
Upcoming:
* Hardware weighing scale + audio measurement kit development (2018 Q2-Q4)
  * See https://www.openhardware.io/view/630
* Integration sensor data / hive inspections (2018 Q4)
* User feedback improvements (2019 Q1 + Q2)
* Bee keeping teacher support - Inspection list sharing (2019 Q2)
* Hardware measurement system version 3 (2019 Q3)
* Many new features for health checking and sensor (2019 Q3 - 2023 Q3)

History:
* Release v1 - June 15th 2017
  * One click creation of apiaries with multiple hives
  * Manual hive inspections
  * Display automatic measurements
* Release v2 - July 10th 2018
  * Apiary and hive overview improved
  * Dynamic inspection list 
  * Create your own inspection list and order
  * Automatic measurement view improvement


# Documentation

Documentation and manual of the app can be found at https://beep.nl/manual. 


# Security Vulnerabilities

If you discover a security vulnerability within BEEP, please send an e-mail to beep@iconize.nl.

# License

The BEEP framework is open-sourced software licensed under the [GNU AGPLv3 license](http://www.gnu.org/licenses/agpl-3.0.txt).
