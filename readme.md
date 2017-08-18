# BEEP - Open source bee monitoring


BEEP is a combination of a bee monitoring (Laravel PHP) framework API + an (Angular JS) app and a (Influx) time series sensor data database. There are also first steps of creating cost efficient measurement hardware.

It's key feature is to integrate a user friendly responsive app for manual inspections with automatically measured sensor data.

Create a login and check the live app at: https://app.beep.nl
You can just make use of the BEEP app, it's free and it will be developed further in the near future. If you would like to install it on your own server, or contribute; please read on below.

# System overview
![BEEP System overview](https://github.com/pvgennip/BEEP/raw/master/system-overview.png)


# Installation of API and APP (on your own server)

## 1. Clone this repo anywhere you like
```git clone https://github.com/pvgennip/BEEP.git```

## 2. Install required vendor libraries by running 

Make sure these dependencies are installed on your system:

* [Composer](https://getcomposer.org/download/) - Installation tool for PHP/Laravel dependencies for API
* [npm](https://www.npmjs.com/get-npm) - Installation tool for Javascript/Angular dependencies for App
* [Bower](https://bower.io/) ```npm install -g bower``` - Installation tool for front-end dependencies for App

Make the run_actions.sh executable by ```chmod +x run_actions.sh```

Then run it: ```./run_actions.sh```

This will install all the packages and vendor dependencies that you need at once.

## 3. If you would like to easily deploy your fork (or this repo), 

a. Make sure to add your repo to git remote: ```git remote set url https://github.com/pvgennip/BEEP.git```

b. Run ```./deploy.sh``` to update your clone on any server

## 4. Make sure your server has 2 different virtual hosts for the API and the APP
API
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

APP
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


# Contributing

Thank you for considering contributing to the BEEP framework! If you would like to contribute, please fork this repository, edit on your Github account, and finally send Pull Requests to this repository to include new features.

### Adding a language
a. Fork this repo

b. Copy ```/public/webapp/js/lang/en.js``` to the abbreviation of the new language

c. Translate the part after the colons (:) only: ```static_language_var: 'this is the translation',```

d. Add the language to ```/public/webapp/index.js``` supported locales:
```
$rootScope.supportedLocales = {
    "nl":"Nederlands", 
    "en":"English",
    "xx":"New language",
};
```
e. Create a pull request to merge the language into this repo

# Roadmap

Please request access to https://trello.com/b/Eb3CcKES/beep-hive-check-app if you would like to see the roadmap and cooperate.

### In short on our roadmap until 2018:

1. Manual inspection improvement 

2. Inspection item list dynamically assignable

3. Sharing of data amongst bee keepers (in vicinity)

4. Hardware weighing scale + audio measurement kit development

5. Integration sensor data / hive inspections


# Documentation

Documentation and manual of the app can be found at http://beep.nl. 


# Security Vulnerabilities

If you discover a security vulnerability within BEEP, please send an e-mail to beep@iconize.nl.

# License

The BEEP framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
