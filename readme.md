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

NB: To stick to a certain PHP version (on the server e.g. 7.4.33), use ```composer install --ignore-platform-reqs``` or ```composer update --ignore-platform-reqs```

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
- NB: If you have influx already installed, use ```./influx``` for the commands from inside the installation folder

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

A simple setup for small installations can be achived with [docker-compose](https://docs.docker.com/compose/). The tool will spin up a webserver and initialize the beep mysql and influxdb database.

1. [Install docker-compose](https://docs.docker.com/compose/install/)
2. Checkout BEEP and switch into the code repository
3. Make your own .env file: run `cp .env.example .env`
4. [Create a free mailtrap.io account](https://mailtrap.io/register/signup)
5. Fill in your mailtrap username & password in your .env file for `MAIL_USERNAME` and `MAIL_PASSWORD` (find it in your mailtrap account via Email Testing -> My inbox -> Integration)
6. Run 
```bash
`php artisan key:generate`
`composer install && sudo chmod -R 777 storage && sudo chmod -R 777 bootstrap/cache`
`docker-compose up -d --build`
`docker-compose run --rm artisan migrate --seed`
```
7. Optional: verify whether the docker containers are running: run `docker ps`
8. [Check whether backend login page is working](http://localhost:8087/login)
9. If everything is running smoothly, create a login via the frontend webapp [BEEP VUE (v3) app](https://github.com/beepnl/beep-vue-app).
To connect the BEEP VUE app with the BEEP backend, make sure to specify the following .env variables in your local BEEP VUE app installation:
VUE_APP_API_URL = http://localhost:8087/api/
VUE_APP_BASE_API_URL = http://localhost:8087/
(Psssst there is a shortcut for creating a login: just use the old [BEEP Angular JS (v2) app](https://github.com/beepnl/BEEP/tree/master/resources/assets) app directly, via http://localhost:8087/webapp#!/login/create
but beware to only use it to create your login, as the rest of the app is deprecated! (The v3 app replaced the v2 app in 2021))
10. With your newly created login, [login to the backend](http://localhost:8087/login). N.B. verification email from the previous step will be send to your mailtrap inbox
11. Give your newly created user the superadmin role, via [phpmyadmin](http://localhost:8088/index.php) (server: mysql, user: root, password: secret):
Go to bee_data db, users table, check your user id. Then in the role_user table, edit the role_id for that user_id to '1 - superadmin' and save. 
12. Now setup Influxdb for the measurements data. [Create a login via the Influxdb onboarding UI](http://localhost:8086/) (choose your username and password, organization: beep, database: bee_data). Then copy and store the API token.
13. Add your Influxdb credentials to your .env file for `INFLUXDB_USER` and `INFLUXDB_PASSWORD` and the influx-configs file at storage/influxdb2/config like so:
[yourusername]
  url = "http://localhost:8086"
  token = "your API token from step 15"
  org = "beep"
  active = true
14. Run `docker-compose run --rm artisan config:clear` to clear the config cache
15. Make your bee_data database writable inside the influxdb docker container
```bash
docker exec -t influxdb bash
influx bucket list (-> copy bee_data bucket id (or obtain it via the UI at http://localhost:8086 instead))
influx v1 dbrp create --db bee_data --rp autogen --bucket-id [your bucket id]  --default
```
16. Optional: test write to your influxdb via the docker container:
```bash
docker exec -t influxdb bash (skip if moving on from previous step directly)
influx write --bucket bee_data --host http://localhost:8086 "m,host=host1 field1=1.2"
```
Check the data you posted via the Influxdb UI at http://localhost:8086
17. Test posting a measurement via your dockerized BEEP backend: 
    - Create a new device in the BEEP VUE app (see step 9) in the /devices page, copy the 'Device unique identifier', this is your 'key'. (Hive is not required, but also possible to first create an apiary and hive to attach to your device)
    - Create at least 1 measurement type at http://localhost:8087/en/measurement, with 'Influx Database' as the Data Source type. For example Abbreviation 't_i', Physical Quantity Id: Temperature (C)
    - Post a measurement via Postman or curl, see [API documentation](https://api.beep.nl/docs/#apimeasurementcontroller-POSTapi-sensors). For example:
      POST http://localhost:8087/api/sensors?key=[your device key]&t_i=34.5 (where 't_i' is your newly created measurement abbreviation from above, the value can be anything).
    - Response should be ‘saved’. Datapoint should be visible in the Influxdb bee_data.
    - In order to view your datapoint in the BEEP VUE app /data tab, first run `docker-compose run --rm artisan cache:clear` to clear the cache. Then hard refresh the app in order to load your newly created measurement type, otherwise the datapoint will not be visible in the frontend.


As the setup is based on docker containers, code changes inside the repository will not have an effect until the underlying docker image is updated. 

### Start/Stop the project

```sh
docker-compose stop
`docker-compose start` OR `docker-compose up -d`
```


### Clear/Clean the project

```sh
docker-compose stop
docker-compose down -v
docker-compose up -d --build

docker-compose run --rm artisan clear:data
docker-compose run --rm artisan cache:clear
docker-compose run --rm artisan config:clear
docker-compose run --rm artisan view:clear
docker-compose run --rm artisan route:clear
docker-compose run --rm artisan clear-compiled
docker-compose run --rm artisan config:cache
docker-compose run --rm artisan storage:link
docker-compose run --rm artisan migrate
```

### View logs

```sh
docker logs [CONTAINER-NAME]
```

## Ports

Ports used in the project:
| Software        | Port |
|---------------- | ---- |
| **nginx**       | 8087 |
| **phpmyadmin**  | 8088 |
| **mysql**       | 3306 |
| **influxdb**    | 8086 |
| **php**         | 9000 |
| **redis**       | 6379 |

## Phpmyadmin

In case you need to, login via http://localhost:8088/index.php -> Server: mysql, username: root, password: secret


# Get your BEEP base measurement data into the BEEP app

1. Create a TTN account at https://console.thethingsnetwork.org/ (or other LoRa network) and create an HTTP integration pointing to https://api.beep.nl/api/lora_sensors, or point your own measurement device data stream HTTP POST to https://api.beep.nl/api/sensors (please refer to our [Slack channel](beep-global.slack.com) for API description)
2. Use the native [Android](https://play.google.com/store/apps/details?id=appinventor.ai_app_beep_nl.BEEP_commissioning_V06&gl=NL) / [iOS](https://apps.apple.com/us/app/beep-base/id1495605010) app to configure your BEEP base by Bluetooth. 
3. With the app, connect the BEEP base to the BEEP TTN network (auto configuration), or connect the BEEP base (manually) to your own LoRa network
4. The native app adds a BEEP base measurement device to your BEEP account
5. If you are still with a fresh installation, login to your api (```api.[your_domain]/login´´´) and got to Devices -> Sensor Measurements. Add sensor definitions here.
6. You can see the measurement data at the Data menu item of the webapp.



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

# For Developers - Getting started

Here we describe the first steps to setup a programming environment, in case you want to contribute to the app but are not yet a professional laravel developer.

If you setup your own BEEP instance, you might still want to have a local version running, so that you can test changes immedieatly. 

* Clone the repository to your Laptop or Computer.
* We recomend using php version 8.0
* We recommend to install and use Laravel Valet.

  For Mac: https://laravel.com/docs/10.x/valet#installation (you might want to browse to the newest version)

  For Linux: https://cpriego.github.io/valet-linux/

  For Linux, make sure that you meet the requirements: https://cpriego.github.io/valet-linux/requirements
  
* Install following additional php extensions: fpm, intl, gd, mysql

* Make sure valet uses the correct php version

  For Mac: valet use php@8.0 

  For Linux: valet use 8.0

* fill out the .env file

  Set `APP_URL=beep.test` or choose an other url

  Set the information for the mysql, influx and redis connections. If you connect to your remote server, make sure the ports are open on your server. You might also need to set a password for redis in this case.

  Use `php artisan key:generate` to generate a key and add it to your env file.

* navigate to /public and run `valet link`

* run `valet secure` to add an ssl certificate

* run `composer install` and `valet start`

* You should be able to access the app, e.g. on https://beep.test

Tips and Troubleshooting:

* If you get an error like "Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.1.0"." you might need to run `composer global update`

* You can also link your local vue app to this url in the .env file. If you do this and you get cors errors, this might be because of one of the following reasons:

* The url you set is wrong.
You have to open beep.test first (or your url) and add a security exception for your browser.

* If you want to test your new api functions, we recommend to use Postman: https://www.postman.com/downloads/

* In some cases, e.g. in case you made changes to the measurements database, you need to run `php artisan cache:clear` to make the changes effective in your api calls.

# Security Vulnerabilities

If you discover a security vulnerability within BEEP, please send an e-mail to support@beep.nl.

# License

The BEEP framework is open-sourced software licensed under the [GNU AGPLv3 license](http://www.gnu.org/licenses/agpl-3.0.txt).
