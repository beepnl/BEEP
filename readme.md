# BEEP - Open source bee monitoring


BEEP is a combination of a bee monitoring (Laravel PHP) framework API + an (Angular JS) app and a (Influx) time series sensor data database. There are also first steps of creating cost efficient measurement hardware.

It's key feature is to integrate a user friendly responsive app for manual inspections with automatically measured sensor data.

# System overview
![BEEP System overview](https://github.com/pvgennip/BEEP/system-overview.png)

# Installation

### 1. Clone this repo anywhere you like
```git clone https://github.com/pvgennip/BEEP.git```

### 2. Install required vendor libraries by running 

Make sure these dependencies are installed on your system:

* [Composer](https://getcomposer.org/download/) - Installation tool for PHP/Laravel dependencies for API
* [npm](https://www.npmjs.com/get-npm) - Installation tool for Javascript/Angular dependencies for App
* [Bower](npm install -g bower) - Installation tool for front-end dependencies for App

Make the run_actions.sh executable by ```chmod +x run_actions.sh```

Then run it: ```./run_actions.sh```

This will install all the packages and vendor dependencies that you need at once.

### 3. If you would like to easily deploy your fork (or this repo), 

a. Make sure to add your repo to git remote: ```git remote set url https://github.com/pvgennip/BEEP.git```

b. Run ```./deploy.sh``` to update your clone on any server


## Documentation

Documentation and manual of the app can be found at http://beep.nl. 

## Contributing

Thank you for considering contributing to the BEEP framework! If you would like to contribute, please fork this repository, edit on your Github account, and finally send Pull Requests to this repository to include new features.

## Security Vulnerabilities

If you discover a security vulnerability within BEEP, please send an e-mail to beep@iconize.nl.

## License

The BEEP framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
