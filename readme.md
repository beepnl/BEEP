# BEEP - Open source bee monitoring (v3.0.1)
BEEP is open source combination of a bee monitoring app + automatic bee hive measurement device. It's key feature is to integrate a user friendly responsive web app for manual inspections with automatically measured sensor data.
Check the website https://beep.nl/index.php/home-english for more information.

## BEEP System overview
![BEEP System overview](https://github.com/beepnl/BEEP/raw/master/BEEP-system-overview.png)

You are free to use our BEEP app and API.

### BEEP App
The [BEEP app](https://beep.nl/index.php/beep-app) is a [VUE app](https://vuejs.org), communicating with a ([Laravel PHP](https://laravel.com/docs/6.x)) BEEP API (this repository).

NB: The [BEEP VUE (v3) app](https://github.com/beepnl/beep-vue-app) replaced the [BEEP Angular JS (v2) app](https://github.com/beepnl/BEEP/tree/master/resources/assets) in 2021.

The BEEP app is publicly available at: https://app.beep.nl. It has about 5000+ users worldwide and is available in 9 languages (Jan 2022).

### BEEP API / Backend
This repository. Serving both app v3 as app v2. Publicly available at https://api.beep.nl 

### BEEP base
The [BEEP base v3](https://beep.nl/index.php/home-english) is an open source ultra low power automatic hive monitoring system. Design of the device and PCB (with [accompanying firmware](https://github.com/beepnl/beep-base-firmware)) be found at [this repository]](https://github.com/beepnl/measurement-system-v3). It measures weight, temperature and sound. We produce a yearly batch of BEEP bases for the European (868MHz LoRaWAN) market, that are sold via the [BEEP webshop](https://www.beep-shop.nl/en_GB/). The BEEP base sends it's data through [The Things Network](https://www.thethingsnetwork.org) LoRaWAN to the BEEP API. 

You can also use your own measurement device with the BEEP app by POSTing your measurement data to the [BEEP API](https://api.beep.nl/docs/#apisensors-post). 

The BEEP base can be configured by Bluetooth using our native BEEP base apps for [Android](https://play.google.com/store/apps/details?id=appinventor.ai_app_beep_nl.BEEP_commissioning_V06&gl=NL) and [iOS](https://apps.apple.com/us/app/beep-base/id1495605010). 


# BEEP API Installation 
If you would like to install this BEEP API (the code in this) repo on your own server, or contribute; please read on below.

## 0. Server specs

* Linux Debian (8+)
* Software installed
  * PHP 7.4+
  * MariaDB/MySQL
  * Apache 2 (or Nginx, creating Nginx 'server blocks' in step 4)
  * InfluxDB (https://docs.influxdata.com/influxdb/v1.7/introduction/installation/)
  * [Composer](https://getcomposer.org/download/) - Installation tool for PHP/Laravel dependencies for API
* Optional: Letsencrypt SSL certificate generation


**NB: We recommend to use [Laravel Forge](https://forge.laravel.com) to easily install all the software you need on any Linux based server**


## 1. Clone this repo
```
git clone https://github.com/beepnl/BEEP.git
cd BEEP
```

## 2. Database

Create a MySQL database (type: InnoDB, encoding: UTF_8, collation: utf8mb4_unicode_ci) called: 

```bee_data```

**NB: Make sure to pass the user and credentials to the newly made .env file after step 3.**


## 3. Install dependencies 

```
if [ ! -f '.env' ]; then cp .env.example .env && php artisan key:generate; fi
composer install && sudo chmod -R 777 storage && sudo chmod -R 777 bootstrap/cache && php artisan storage:link && php artisan migrate --force
```

NB: To stick to a certain PHP version (on the server e.g. 7.1.25), use ```composer install --ignore-platform-reqs``` or ```composer update --ignore-platform-reqs```

## 4. Set up your end-points

### Laravel Forge
Simply use the default Nginx template and install Let's Encrypt SSL certificate via the interface.

### Apache
Install the desired config files from [/apache](https://github.com/beepnl/BEEP/tree/master/apache) to your ```apache/sites-available``` folder and enable them with ```a2ensite [config file name]```


Install SSL certificates to your endpoints with [Let's Encrypt](https://letsencrypt.org/getting-started/)

## 5. Add Influx database
### Installation
Install [InfluxDB](https://www.influxdata.com/) or set up an account at [InfluxCloud](https://cloud2.influxdata.com/signup)

#### Optional: migrate from local Influx v1.7.3 db to managed InfluxDB Cloud

- https://docs.influxdata.com/influxdb/cloud/upgrade/v1-to-cloud/
- NB: If you have inlfux already installed, use ```./influx``` for the commands from inside the installation folder

```bash
wget https://dl.influxdata.com/influxdb/releases/influxdb2-client-2.0.6-linux-amd64.tar.gz
tar xvfz influxdb2-client-2.0.6-linux-amd64.tar.gz
cd influxdb2-client-2.0.6-linux-amd64
./influx config create --config-name beep-cloud-test --host-url https://eu-central-1-1.aws.cloud2.influxdata.com --org [your-email] --token [copy-from-influx-cloud] --active
./influx v1 dbrp create --db [influx-v1-db-name] --rp autogen --bucket-id [copy-from-influx-cloud] --default
sudo influx_inspect export -datadir /var/lib/influxdb/data -waldir /var/lib/influxdb/wal -database test_beep_nl -retention autogen -start 2021-01-01T00:00:00Z -end 2022-01-01T00:00:00Z -out test_beep_nl_temp.lp
sudo tail -n +4 test_beep_nl_temp.lp > test_beep_nl.lp
./influx write --bucket-id [copy-from-influx-cloud] --file test_beep_nl.lp --rate-limit "300 MB / 5 min" --skipRowOnError
```

### Setup
Create a databse and user
```
influx
> CREATE USER user_influx WITH PASSWORD 'pass_influx' WITH ALL PRIVILEGES
> CREATE DATABASE bee_data
> exit
```
**NB: Make sure to pass the user and credentials to the .env file that has been created in step 3.**
**NB: If your Influx version was < 1.1.x (no TSI support), when using backups to transfer data: first install the old version that you are currently using on a new server, import the backups, then update to the newest Influx version!**


# Configuring your own back-end system

## 1. Set up valid credentials

a. Set up e-mail credentials in the ```.env``` config file

b. (Angular JS webapp v2) For the webapp to reach the API, rename the file 'public/webapp/js/constants.js.example' to 'public/webapp/js/constants.js' and edit it to change the 'api_url' to your own back-end API end-point

c. To enable schedules (e.g. for loading weather data), install a crontab with ```sudo crontab -e``` and add: ```* * * * * cd /home/bitnami/apps/appdir && /opt/bitnami/php/bin/php artisan schedule:run >> /dev/null 2>&1```

## 2. Register new user

a. Go to ```api.[your_domain]/webapp#!/login/create```

b. Create a new user

c. Promote the user to 'superadmin'

Open your database (with a database viewer like PhpMyadmin, Sequel Pro, MySQL Workbench, etc.), or just do a command line query: 
Define a relation in table ```role_user``` with ```(1,1)``` (linking user_id 1 to role_id 1). 

Your user has just been promoted to the super administrator of the system, so you can use the back-end management interface to configure the complete system.

d. Go to ```api.[your_domain]/admin``` to log in with the same credentials that you created your app user with in step 3b.

e. You should see the back-end dashboard, looking like this:

## Management interface
![BEEP Management interface](https://github.com/beepnl/BEEP/raw/master/BEEP-management-interface.png)


# Installation using docker compose

A simple setup for small installations can be achived with [docker-compose](https://docs.docker.com/compose/). The tool will spinn up a mysqldb, influd, a webserver and initialize the beep database.

1. [Install docker-compose](https://docs.docker.com/compose/install/)
2. Checkout BEEP and switch into the code repository
3. Adjust `docker-compose.yaml`(set suitable environment variables).
4. Run `docker-compose up`. During the first start, you might see some database connectivity issues. Docker compose will restart the BEEP Server component untill a database connection is available. So don't worry.
5. Register as a new user: [http://localhost:8000/webapp](http://localhost:8000/webapp).
6. Grant user administrator rights: `docker-compose exec  mysql mysql -h localhost -P 3306 -ppass -u user -D bee_data -Bse "INSERT INTO role_user (user_id,role_id) VALUES(1,1);"`
7. Login to mamagement interface: [http://localhost:8000/admin](http://localhost:8000/admin)

To upgrade beep to the latest version, simply stop and start docker-compose.

As the setup is based on docker containers, code changes inside the repository will not have an effeact till the underlying docker image is updated. 


# Get your BEEP base measurement data into the BEEP app

1. Create a TTN account at https://console.thethingsnetwork.org/ (or other LoRa network) and create an HTTP integration pointing to https://api.beep.nl/api/lora_sensors, or point your own measurement device data stream HTTP POST to https://api.beep.nl/api/sensors (please refer to our [Slack channel](beep-global.slack.com) for API description)
2. Use the native [Android](https://play.google.com/store/apps/details?id=appinventor.ai_app_beep_nl.BEEP_commissioning_V06&gl=NL) / [iOS](https://apps.apple.com/us/app/beep-base/id1495605010) app to configure your BEEP base by Bluetooth. 
3. With the app, connect the BEEP base to the BEEP TTN network (auto configuration), or connect the BEEP base (manually) to your own LoRa network
4. The native app adds a BEEP base measurement device to your BEEP account
5. You can see the measurement data at the Measurements menu item of the webapp 


# Contributing

Thank you for considering contributing to the BEEP framework! If you would like to contribute, please fork this repository, edit on your Github account, and finally send Pull Requests to this repository to include new features.

Please request access to our slack community at https://beep-global.slack.com if you would like to know more, or cooperate.

## Adding a language

1. Create a Beep user account at https://app.beep.nl/#!/login/create
2. Send an e-mail to support@beep.nl with you user e-mail address, asking to become a translator for a certain language
3. Log into the backend to start translating


# Roadmap

### In short on our roadmap:
Upcoming:

* Many new features for health checking and sensor measurements (2019 Q3 - 2023 Q3)

History:
* BEEP base v3.3 - December 2021
* App v3 (VUEjs) development - 2020-2021
  * Alerts (e-mail, app)
  * Many improvements
* BEEP base v3.2 - April 2021
* App v2.2.1 - Continuous App improvements - 2020-2021
* App v2.2 - January 2020
  * Photo addition
  * Weather data
  * Research
  * Helpdesk integration
* BEEP base v3.1 (2019 Q3-Q4)
  * See https://www.openhardware.io/view/739/BEEP-base-v3
* App v2.1.0 - May 29 2019
  * Collaborate: Hive, data and inspection list sharing (2019 Q2)
  * User feedback improvements (2019 Q1 + Q2)
* App v2.0.2 - April 30 2019
  * Integration sensor data / hive inspections (2018 Q4)
* BEEP base v2 - December 2019
  * Hardware weighing scale + temperature + audio measurement kit v3 development (2018 Q2-Q4)
    * See https://www.openhardware.io/view/630/BEEP-base-v2
* App v2.0 - July 10th 2018
  * Apiary and hive overview improved
  * Dynamic inspection list 
  * Create your own inspection list and order
  * Automatic measurement view improvement
* App v1.0 - June 15th 2017
  * One click creation of apiaries with multiple hives
  * Manual hive inspections
  * Display automatic measurements



# Documentation

Documentation and manual of the app can be found at https://beep.nl/beep-app. 

API documentation of the BEEP API can be found at https://api.beep.nl/docs/.


# Security Vulnerabilities

If you discover a security vulnerability within BEEP, please send an e-mail to support@beep.nl.

# License

The BEEP framework is open-sourced software licensed under the [GNU AGPLv3 license](http://www.gnu.org/licenses/agpl-3.0.txt).
