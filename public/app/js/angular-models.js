function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * API model
 */
app.service('api', ['$http', '$rootScope', function ($http, $rootScope) {
  var self = this;
  this.token = null;

  this.reset = function () {
    // api token
    self.removeApiToken();
  };

  $rootScope.$on('reset', self.reset);

  this.getApiToken = function () {
    // get the api token
    if (localStorage.getItem('bee_api_token')) {
      var checkToken = self.token == null ? true : false;
      self.token = localStorage.getItem('bee_api_token');
      if (checkToken) self.checkAuthentication();
    }

    return self.token;
  };

  this.setApiToken = function (token) {
    // set the api token
    if (token != null) {
      localStorage.setItem('bee_api_token', token);
      self.token = token;
    }
  };

  this.setLocalStoreValue = function (name, value) {
    if (typeof name != 'undefined' && name != null && typeof value != 'undefined' && value != null) {
      //console.log('setLocalStoreValue', name, value);
      localStorage.setItem(name, value);
    }
  };

  this.getLocalStoreValue = function (name) {
    if (localStorage.getItem(name)) {
      var value = localStorage.getItem(name); //console.log('getLocalStoreValue', name, value);

      return value;
    }

    return null;
  };

  this.removeApiToken = function () {
    // remove from the storage
    localStorage.removeItem('bee_api_token'); // remove from memory

    self.token = null;
  };

  this.registerUser = function (password, email, policy_accepted) {
    var data = {
      password: password,
      email: email,
      policy_accepted: policy_accepted
    };
    self.postApiRequest('register', 'register', data);
  };

  this.checkAuthentication = function () {
    self.postApiRequest('checkAuthentication', 'authenticate');
  };

  this.login = function (email, password) {
    var credentials = {
      email: email,
      password: password
    };
    self.postApiRequest('authenticate', 'login', credentials);
  };

  this.cache = {};

  this.passwordReminder = function (email) {
    self.cache.email = email;
    self.postApiRequest('passwordReminder', 'user/reminder', {
      email: email
    });
  };

  this.passwordReset = function (email, password, password_confirm, token) {
    var credentials = {
      email: email,
      password: password,
      password_confirm: password_confirm,
      token: token
    };
    self.postApiRequest('passwordReset', 'user/reset', credentials);
  };

  this.handleAuthentication = function (result) {
    $rootScope.user = result;
    $rootScope.user.img = API_URL + "../uploads/avatars/" + result.avatar; // token

    if (result.api_token != null) self.setApiToken(result.api_token);
    $rootScope.$broadcast('userUpdated');
  };

  this.handleAuthenticationError = function (error) {
    self.reAuthenticate();
  };

  this.reAuthenticate = function () {
    if (self.token != null) {
      $rootScope.showMessage($rootScope.lang.no_valid_authentication, null, $rootScope.lang.login_title);
      $rootScope.doLogout(); // will broadcast reset
    }
  };

  this.handleResponses = function (type, result, status) {
    console.info(type, status, result != undefined ? _typeof(result) == 'object' ? result.length == undefined ? Object.keys(result).length : result.length : '' : '');
    $rootScope.$broadcast(type, result, status);

    switch (type) {
      case "authenticateLoaded":
      case "checkAuthenticationLoaded":
        self.handleAuthentication(result);
        break;

      case "authenticateError":
      case "checkAuthenticationError":
        self.handleAuthenticationError(result);
        break;
    }
  };

  this.deleteApiRequest = function (type, request, data, params) {
    self.postApiRequest(type, request, data, params, 'DELETE');
  };

  this.putApiRequest = function (type, request, data, params) {
    self.postApiRequest(type, request, data, params, 'PUT');
  };

  this.patchApiRequest = function (type, request, data, params) {
    self.postApiRequest(type, request, data, params, 'PATCH');
  };

  this.postApiRequest = function (type, request, data, params, method) {
    var params = typeof params !== 'undefined' ? params + '&' : '';
    var method = typeof method !== 'undefined' ? method : 'POST';
    var url = API_URL + request;
    url += params == '' ? '' : '?' + params; // set the request

    var req = {
      method: method,
      headers: {
        'Content-Type': 'application/json'
      },
      data: data,
      url: url
    }; // check if it has to be authorized

    if (type != 'authenticate' && type != 'register' && self.getApiToken() != null) {
      req.headers['Authorization'] = 'Bearer ' + self.getApiToken() + '';
    }

    req.headers['Accept-Language'] = $rootScope.locale; // do the request

    self.doApiRequest(type, req);
  };

  this.getApiRequest = function (type, request, params) {
    if (_typeof(params) === 'object') {
      var paramArray = [];

      for (p in params) {
        var name = p;
        var value = params[p];
        paramArray.push(name + '=' + value);
      }

      params = paramArray.join('&');
    }

    var params = typeof params !== 'undefined' ? params + '&' : ''; // var count  = (typeof count !== 'undefined') ? count : 0;
    // var offset = (typeof offset !== 'undefined') ? offset : 0;

    var url = API_URL + request + '?' + params + ''; // set the request

    var req = {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      },
      url: url
    }; // check if it has to be authorized

    if (type != 'register' && self.getApiToken() != null) {
      req.headers['Authorization'] = 'Bearer ' + self.getApiToken() + '';
    }

    req.headers['Accept-Language'] = $rootScope.locale; // do the request

    self.doApiRequest(type, req);
  };

  this.doApiRequest = function (type, req) {
    // start loading
    $rootScope.$broadcast('startLoading'); // set a request timeout
    //req.timeout = (PING_FREQ_CONNECTED-1000);
    // do the request

    $http(req).then(function (response) // success
    {
      // set the data
      var status = typeof response != 'undefined' ? response.status : 0;
      var result = response.data != undefined ? response.data : response; // set the listeners

      self.handleResponses(type + 'Loaded', result, status);
      $rootScope.$broadcast('endLoading');
    }, function (response) // error
    {
      var error = typeof response != 'undefined' ? typeof response.data != 'undefined' ? typeof response.data.errors != 'undefined' ? response.data.errors : typeof response.data.message != 'undefined' ? response.data.message : response.data : response : 'error';
      var status = typeof response != 'undefined' ? response.status : 0; // set the listeners

      self.handleResponses(type + 'Error', {
        'message': error,
        'status': status
      }, status);
      $rootScope.$broadcast('endLoading');

      if (status == 401 || type == 'checkAuthentication' && status == 302) // re-authenticate
        {
          self.reAuthenticate();
        }
    });
  };

  self.getApiToken();
}]);
/*
 * Bee Monitor
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */

app.service('settings', ['$http', '$rootScope', 'api', function ($http, $rootScope, api) {
  var self = this;

  this.reset = function () {
    // user type (readonly)
    this.type = null; // admin, user

    this.fetchedSettings = false;
    this.updated_at = null;
    this.created_at = null;
    this.settings = {};
    this.hives = {};
    this.beeraces = [];
    this.hivetypes = [];
    this.hivedimensions = {};
    this.sensortypes = [];
    this.sensormeasurements = [];
    this.taxonomy = [];
    this.settings_array = [];
  };

  self.reset();
  $rootScope.$on('reset', self.reset); // Inspection Lists

  this.loadTaxonomy = function () {
    api.getApiRequest('taxonomyLists', 'taxonomy/lists'); //api.getApiRequest('taxonomyItems', 'taxonomy/taxonomy', 'order=1&flat=0');
  };

  this.taxonomyHandler = function (e, data) {
    if (typeof data.taxonomy != 'undefined') {
      self.taxonomy = data.taxonomy;
      $rootScope.$broadcast('taxonomyItemsUpdated');
    }

    if (typeof data.beeraces != 'undefined') self.beeraces = data.beeraces;
    if (typeof data.sensortypes != 'undefined') self.sensortypes = data.sensortypes;
    if (typeof data.hivetypes != 'undefined') self.hivetypes = data.hivetypes;
    if (typeof data.hivedimensions != 'undefined') self.hivedimensions = data.hivedimensions;
    if (typeof data.sensormeasurements != 'undefined') self.sensormeasurements = data.sensormeasurements;
    if (typeof data.hivetypes != 'undefined' || typeof data.beeraces != 'undefined') $rootScope.$broadcast('taxonomyListsUpdated');
  };

  $rootScope.$on('taxonomyListsLoaded', self.taxonomyHandler);
  $rootScope.$on('taxonomyItemsLoaded', self.taxonomyHandler);

  this.getSensormeasurementById = function (id) {
    for (var i in this.sensormeasurements) {
      var sm = this.sensormeasurements[i];
      if (sm.id == id) return sm;
    }

    return null;
  };

  this.saveSettings = function (settings) {
    if (typeof settings != 'undefined') {
      api.postApiRequest('saveSettings', 'settings', settings);
      console.log('settings.saveSettings', settings);
    }
  };

  this.fetchSettings = function () {
    console.log('start loading the settings via API'); // start loading the settings

    api.getApiRequest('settings', 'settings');
    self.loadTaxonomy();
  };

  this.handleSettings = function (e, data) //, status)
  {
    self.fetchedSettings = true;
    self.settings_array = data;
    self.settings = convertSettingJsonToObject(self.settings_array); //console.log(self.settings);
  };

  this.settingsError = function (e, error) {
    self.fetchedSettings = false;
  }; // listen to the setting changes


  $rootScope.$on('saveSettingsLoaded', self.handleSettings);
  $rootScope.$on('settingsLoaded', self.handleSettings);
  $rootScope.$on('settingsError', self.settingsError);
}]);
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */

app.service('hives', ['$http', '$rootScope', 'api', 'settings', function ($http, $rootScope, api, settings) {
  var self = this;

  this.reset = function () {
    this.refreshCount = 0;
    this.hives_inspected = [];
    this.hives = [];
    this.hives_owned = [];
    this.locations = [];
    this.locations_owned = [];
    this.frame_width = 11;
    this.hive_width_start = 30;
    this.frame_width_mobile = 3;
    this.hive_width_start_mobile = 10;
    this.open_loc_ids = [];
  };

  this.toggle_open_loc = function (id) {
    var loc = self.getHiveLocationById(id);

    if (loc) {
      loc.open = !loc.open; // set new state

      if (loc.open && self.open_loc_ids.indexOf(loc.id) == -1) {
        self.open_loc_ids.push(loc.id);
      } else if (self.open_loc_ids.indexOf(loc.id) > -1) {
        var index = self.open_loc_ids.indexOf(loc.id);
        self.open_loc_ids.splice(index, 1);
      }

      self.open_loc_ids = self.open_loc_ids.getUnique(); //console.log((loc.open ? 'open' : 'close') + ' loc', loc.id, loc.name, self.open_loc_ids);

      api.setLocalStoreValue('open_loc_ids', self.open_loc_ids.join(','));
    }

    self.refresh();
  };

  this.getHiveById = function (id) {
    for (var i = 0; i < self.hives.length; i++) {
      var hive = self.hives[i];
      if (hive.id == id) return hive;
    }

    return null;
  };

  this.getHiveOwnedById = function (id) {
    for (var i = 0; i < self.hives_owned.length; i++) {
      var hive = self.hives_owned[i];
      if (hive.id == id) return hive;
    }

    return null;
  };

  this.getHiveIndex = function (hiveId) {
    for (var i = 0; i < self.hives.length; i++) {
      var hive = self.hives[i];
      if (hive.id == hiveId) return i;
    }

    return null;
  };

  this.getHiveOwnedIndex = function (hiveId) {
    for (var i = 0; i < self.hives_owned.length; i++) {
      var hive = self.hives_owned[i];
      if (hive.id == hiveId) return i;
    }

    return null;
  };

  this.getHiveNameById = function (id) {
    var hive = self.getHiveById(id);
    return hive != null ? hive.name : null;
  };

  this.getHiveInspectedIndex = function (hiveId) {
    for (var i = 0; i < self.hives_inspected.length; i++) {
      var hive = self.hives_inspected[i];
      if (hive.id == hiveId) return i;
    }

    return null;
  }; // NB: Watch out with undefined variables called 'location' because they affect the url location!!


  this.getHiveLocationById = function (id) {
    for (var i = 0; i < self.locations.length; i++) {
      var loc = self.locations[i];
      if (loc.id == id) return loc;
    }

    return null;
  };

  this.getHiveLocationOwnedById = function (id) {
    for (var i = 0; i < self.locations_owned.length; i++) {
      var loc = self.locations_owned[i];
      if (loc.id == id) return loc;
    }

    return null;
  }; // Locations


  this.loadRemoteLocations = function () {
    api.getApiRequest('locations', 'locations');
  };

  this.calculateHiveWidth = function (hive) {
    if (hive.frames != undefined && hive.frames > 0) {
      if ($rootScope.mobile) {
        hive.width = self.hive_width_start_mobile + self.frame_width_mobile * hive.frames;
      } else {
        hive.width = self.hive_width_start + self.frame_width * hive.frames;
      } //console.log(hive.name, hive.width);

    }

    return hive;
  };

  this.addHiveCalculations = function (hive) {
    hive.brood_layers = 0;
    hive.honey_layers = 0;

    if (hive.layers.length > 0) {
      hive.frames = hive.layers.length > 0 && hive.layers[0].framecount != undefined ? hive.layers[0].framecount : 10;
      hive = self.calculateHiveWidth(hive);

      for (var i = 0; i < hive.layers.length; i++) {
        l = hive.layers[i];
        if (typeof l.frames == 'undefined') l.frames = new Array(hive.frames);
        hive.brood_layers += l.type == 'brood' ? 1 : 0;
        hive.honey_layers += l.type == 'honey' ? 1 : 0;
      }
    } // Queen


    if (hive.queen == null) {
      hive.queen = {
        'created_at': null,
        'name': '',
        'age': '',
        'color': ''
      };
    } else {
      hive.queen.created_at = hive.queen.created_at != null ? hive.queen.created_at.substr(0, 10) : null; // YYYY-MM-DD

      hive.queen.clipped = parseInt(hive.queen.clipped);
      hive.queen.fertilized = parseInt(hive.queen.fertilized);
    }

    return hive;
  };

  this.locationsHandler = function (e, data) {
    // get the result
    var result = data.locations; //console.log(result);

    self.locations = result;
    self.locations_owned = [];

    if (self.locations.length > 0) {
      self.hives = [];
      self.hives_owned = [];
    }

    var loc_ids = [];
    var open_loc_ids = api.getLocalStoreValue('open_loc_ids'); //console.log('open_loc_ids', open_loc_ids);

    if (open_loc_ids != null) {
      loc_ids = open_loc_ids.split(',');

      for (var i = loc_ids.length - 1; i >= 0; i--) {
        loc_ids[i] = parseInt(loc_ids[i]);
      }
    }

    for (var i = 0; i < self.locations.length; i++) {
      loc = self.locations[i];

      if (self.locations.length == 1) {
        loc.open = true;
        if (self.open_loc_ids.indexOf(loc.id) == -1) self.open_loc_ids.push(loc.id);
      } else if (loc_ids.indexOf(loc.id) > -1) {
        loc.open = true;
        if (self.open_loc_ids.indexOf(loc.id) == -1) self.open_loc_ids.push(loc.id);
      } else {
        loc.open = false;
      }

      if (typeof loc.coordinate_lat != 'undefined') loc.lat = parseFloat(loc.coordinate_lat);
      if (typeof loc.coordinate_lon != 'undefined') loc.lon = parseFloat(loc.coordinate_lon);
      if (loc.owner) self.locations_owned.push(loc); // Get hives from locations

      for (var j = 0; j < loc.hives.length; j++) {
        var h = loc.hives[j];

        if (typeof h != 'undefined') {
          var hive = self.addHiveCalculations(h);
          self.hives.push(hive);
          if (hive.inspection_count > 0) self.hives_inspected.push(hive);
          if (hive.owner) self.hives_owned.push(hive);
        } // Get sensors from hives
        // for (var k = 0; k < h.sensors.length; k++) 
        // {
        // 	var s = h.sensors[k];
        // 	self.sensors.push(s);
        // 	if (s.owner)
        // 		self.sensors_owned.push(s);
        // }

      } // // Get sensors from locations
      // if (typeof loc.sensors != 'undefined')
      // {
      // 	for (var k = 0; k < loc.sensors.length; k++) 
      // 	{
      // 		var s = loc.sensors[k];
      // 		self.sensors.push(s);
      // 		if (s.owner)
      // 			self.sensors_owned.push(s);
      // 	}
      // }

    } //console.table(self.hives);


    self.refresh();
  };

  this.locationsError = function (e, error) {
    console.log('locations error ' + error.message + ' status: ' + error.status);
  };

  $rootScope.$on('locationsLoaded', self.locationsHandler);
  $rootScope.$on('locationsError', self.locationsError);

  this.refresh = function () {
    //update refresh count
    self.refreshCount++; // announce the update

    $rootScope.$broadcast('hivesUpdated');
  };

  self.reset();
  $rootScope.$on('reset', self.reset);
  self.loadRemoteLocations();
}]);
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */

app.service('measurements', ['$http', '$rootScope', '$interval', 'api', 'settings', function ($http, $rootScope, $interval, api, settings) {
  var self = this;

  this.reset = function () {
    this.sensors = [];
    this.sensors_owned = [];
    this.lastSensorValues = {};
    this.lastSensorDate = null;
    this.sensorId = null;
    this.lightAmounts = ['sun_lux_dark', 'sun_lux_dusk', 'sun_lux_low', 'sun_lux_cloudy', 'sun_lux_half', 'sun_lux_sunny']; // power of the amount, 0=index:0, 10=index:1, 100=index:2, etc. 

    this.weightSensors = {
      'w_fl': 0,
      'w_fr': 0,
      'w_bl': 0,
      'w_br': 0,
      'w_v': 0
    }; // sensorMeasurements

    this.interval = 'day';
    this.timeIndex = 0;
    this.timeGroup = 'day';
    this.timeZone = 'Europe/Amsterdam';
  };

  self.reset();
  $rootScope.$on('reset', self.reset);

  this.updateWeightSensors = function (data) {
    var updated = false;

    for (var s in data) {
      var val = parseFloat(data[s]);

      if (typeof self.weightSensors[s] != 'undefined' && !isNaN(val)) {
        self.weightSensors[s] = val;
        updated = true;
      }
    }

    if (updated) $rootScope.$broadcast('weightSensorsUpdated');
  };

  this.getSensorOwnedById = function (id) {
    for (var i in this.sensors_owned) {
      var sensor = this.sensors_owned[i];
      if (sensor.id == id) return sensor;
    }

    return null;
  };

  this.getSensorById = function (id) {
    for (var i in this.sensors) {
      var sensor = this.sensors[i];
      if (sensor.id == id) return sensor;
    }

    return null;
  };

  this.getSensorOwnedByIndex = function (i) {
    return typeof this.sensors_owned[i] != 'undefined' ? this.sensors_owned[i] : null;
  };

  this.getSensorByIndex = function (i) {
    return typeof this.sensors[i] != 'undefined' ? this.sensors[i] : null;
  }; // Data from one sensor


  this.loadRemoteSensorMeasurements = function (interval, timeIndex, timeGroup, timeZone, sensorId) {
    // start loading the measurements
    //api.getApiRequest('sensorMeasurements', 'sensors/measurements');
    if (typeof interval != 'undefined') self.interval = interval;
    if (typeof timeIndex != 'undefined') self.timeIndex = timeIndex;
    if (typeof timeGroup != 'undefined') self.timeGroup = timeGroup;
    if (typeof timeZone != 'undefined') self.timeZone = timeZone;
    if (typeof sensorId != 'undefined') self.sensorId = sensorId;
    self.startLoadingMeasurements();
  };

  this.sensorMeasurementRequest = function () {
    api.getApiRequest('dataRequest', 'sensors/measurements', 'id=' + self.sensorId + '&interval=' + self.interval + '&index=' + self.timeIndex + '&timeGroup=' + self.timeGroup + '&timezone=' + self.timeZone);
    if (self.timeIndex > 0) self.stopLoadingMeasurements(); // no need to refresh, because no new values
  };

  this.loadRemoteDevices = function () {
    api.getApiRequest('devices', 'devices');
  };

  this.handleDevices = function (e, result) {
    if (result.length > 0) {
      self.sensors = result;
      self.sensors_owned = [];

      for (var i = 0; i < result.length; i++) {
        var s = result[i];
        if (s.owner) self.sensors_owned.push(s);
      }

      $rootScope.hasSensors = true;
      $rootScope.$broadcast('devicesUpdated');
    } //console.log(self.sensors);

  };

  this.loadLastSensorValues = function () {
    var sensorId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : self.sensorId;
    api.getApiRequest('lastSensorValues', 'sensors/lastvalues', 'id=' + sensorId);
  };

  this.loadLastWeightSensorValues = function () {
    var sensorId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : self.sensorId;
    api.getApiRequest('lastSensorValues', 'sensors/lastweight', 'id=' + sensorId);
  };

  this.handleLastSensorValues = function (e, result) {
    //console.log('measurements handleLastSensorValues', result);
    if (result != null) {
      self.lastSensorValues = result;
      self.lastSensorDate = result.time;
      self.updateWeightSensors(result);
      $rootScope.$broadcast('lastSensorValuesUpdated');
    }
  };

  $rootScope.$on('lastSensorValuesLoaded', self.handleLastSensorValues);

  this.weightCalibration = function (data) {
    api.postApiRequest('weightCalibration', 'sensors/calibrateweight', data);
  };

  this.weightOffset = function (data) {
    api.postApiRequest('weightOffset', 'sensors/offsetweight', data);
  };

  this.devicesError = function (e, error) {
    console.log('measurements sensorsError ' + error.message + ' status: ' + error.status);

    if (error.status == 404) {
      $rootScope.hasSensors = false;
      self.stopLoadingMeasurements();
    }
  };

  $rootScope.$on('devicesLoaded', self.handleDevices);
  $rootScope.$on('saveDevicesLoaded', self.handleDevices);
  $rootScope.$on('devicesError', self.devicesError);
  this.measurementLoadTimer = null;

  this.startLoadingMeasurements = function () {
    if (self.sensorId === null) return;
    if (angular.isDefined(self.measurementLoadTimer)) $interval.cancel(self.measurementLoadTimer); // Start loading interval

    self.measurementLoadTimer = $interval(function () {
      self.sensorMeasurementRequest();
    }, CONNECTION_FREQ_REMOTE);
    self.sensorMeasurementRequest();
  };

  this.stopLoadingMeasurements = function () {
    if (angular.isDefined(self.measurementLoadTimer)) {
      $interval.cancel(self.measurementLoadTimer);
    }
  }; // Check if measurements are available


  self.loadRemoteDevices();
}]);
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */

app.service('inspections', ['$http', '$rootScope', 'api', 'settings', function ($http, $rootScope, api, settings) {
  var self = this;

  this.reset = function () {
    this.refreshCount = 0;
    this.inspections = [];
    this.inspection = {};
    this.checklists = [];
    this.checklistTree = [];
    this.checklist = null; // use for filling

    this.checklistNull = null; // clean loaded checklist

    this.lastUsedChecklistId = null; // last loaded checklist id

    this.saveObject = {}; // hold inspection items for saving

    this.DATE_FORMAT_API = 'YYYY-MM-DD HH:mm';
  };

  self.reset();
  $rootScope.$on('reset', self.reset);
  this.STD_VALUES = {
    'default': null,
    'list_item': -1,
    'boolean': -1,
    'boolean_yes_red': -1,
    'date': "",
    'number': null,
    'number_percentage': -1,
    'number_degrees': 0,
    'number_positive': null,
    'number_negative': null,
    'number_0_decimals': null,
    'number_1_decimals': null,
    'number_2_decimals': null,
    'number_3_decimals': null,
    'square_25cm2': null,
    'text': "",
    'select': "",
    'options': "",
    'list': null,
    'bee_subspecies': -1,
    'select_country': "",
    'select_apiary': -1,
    'select_hive': -1,
    'select_hive_type': -1,
    'score': -1,
    'score_amount': 0,
    'score_quality': 0,
    'smileys_3': -1,
    'slider': 0,
    'grade': 0,
    'file': null,
    'image': null
  };

  this.newSaveObject = function (data) {
    var init = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    self.saveObject = {
      impression: -1,
      attention: -1,
      reminder: '',
      reminder_date: '',
      notes: '',
      date: moment().format(self.DATE_FORMAT_API),
      // always save time in UTC, display in local time in views
      items: {},
      valid: true,
      unfilled_required_item_names: []
    };

    if (self.checklistNull != null) {
      self.checklist = angular.copy(self.checklistNull); //console.log('newSaveObject checklist cloned');
    }

    if (init) // initialize first
      {
        self.createInspectionObject('impression', null, -1, false);
        self.createInspectionObject('attention', null, -1, false);
        self.createInspectionObject('reminder', null, '', false);
        self.createInspectionObject('notes', null, '', false);
      }

    if (typeof data != 'undefined' && data != null) {
      //console.log('newSaveObject filling checklist with available data');
      if (typeof data.impression != 'undefined' && data.impression != null) self.saveObject.impression = parseInt(data.impression);
      if (typeof data.attention != 'undefined' && data.attention != null) self.saveObject.attention = parseInt(data.attention);
      if (typeof data.notes != 'undefined' && data.notes != null && data.notes != '') self.saveObject.notes = data.notes;
      if (typeof data.reminder != 'undefined' && data.reminder != null && data.reminder != '') self.saveObject.reminder = data.reminder; //console.log('data.reminder_date', data.reminder_date);

      if (typeof data.reminder_date != 'undefined' && data.reminder_date != null) self.saveObject.reminder_date = moment(data.reminder_date, [self.DATE_FORMAT_API, moment.ISO_8601]).format(self.DATE_FORMAT_API);
      if (typeof data.created_at != 'undefined') self.saveObject.date = moment(data.created_at, [self.DATE_FORMAT_API, moment.ISO_8601]).format(self.DATE_FORMAT_API);

      if (typeof data.items != 'undefined' && data.items.length > 0) {
        for (var i = data.items.length - 1; i >= 0; i--) {
          var item = data.items[i];
          var val = self.parseTypeValueForChecklistInput(item.type, item.value);
          self.createInspectionObject(item.type, item.category_id, val, true, item.unit); // fill inspection item input fields (convert selected id for select dropdowns)

          switch (item.type) {
            case 'bee_subspecies':
            case 'select_apiary':
            case 'select_hive':
            case 'select_hive_type':
              val = {
                'id': val
              };
          }

          var set = self.setSelectedInspectionItem(item.category_id, val); //if (set) console.log('setSelectedInspectionItem', item);
        }

        $rootScope.$broadcast('checklistUpdated');
      }
    } //console.log('newSaveObject', self.saveObject);


    return self.saveObject;
  };

  this.validateChecklist = function () {
    // make sure required elements are set, if checlist has_required
    if (self.checklist != null && typeof self.checklist.required_ids != 'undefined' && self.checklist.required_ids.length > 0) {
      var filled_required_items = 0;
      var unfilled_item_names = [];

      for (var i = self.checklist.required_ids.length - 1; i >= 0; i--) {
        var id = self.checklist.required_ids[i]; //console.log('validateChecklist', id, self.saveObject.items[id]);

        if (typeof self.saveObject.items != 'undefined' && Object.keys(self.saveObject.items).length > 0 && typeof self.saveObject.items[id] !== 'undefined' && self.saveObject.items[id] !== null && self.saveObject.items[id] !== -1 && self.saveObject.items[id] !== '') {
          filled_required_items++;
        } else {
          var name = recurseGet(self.checklist.categories, id, 'trans');
          unfilled_item_names.push(name);
        }
      }

      self.saveObject.valid = filled_required_items == self.checklist.required_ids.length;
      self.saveObject.unfilled = unfilled_item_names;
    }

    return self.saveObject;
  };

  this.geChecklistById = function (id) {
    for (var i = 0; i < self.checklists.length; i++) {
      var checklist = self.checklists[i];
      if (checklist.id == id) return checklist;
    }

    return null;
  };

  this.typeIsNonNumeric = function (type) {
    switch (type) {
      case 'default':
      case 'date':
      case 'text':
      case 'list':
      case 'select':
      case 'select_country':
      case 'file':
      case 'image':
        return true;
    }

    return false;
  };

  this.parseTypeValueForChecklistInput = function (type, value) {
    console.log(type, value);

    switch (type) {
      case 'list_item':
      case 'boolean':
      case 'boolean_yes_red':
      case 'options':
      case 'score':
      case 'score_amount':
      case 'score_quality':
      case 'smileys_3':
      case 'slider':
      case 'grade':
      case 'number':
      case 'number_percentage':
      case 'number_degrees':
      case 'number_positive':
      case 'number_negative':
      case 'bee_subspecies':
      case 'select_apiary':
      case 'select_hive':
      case 'select_hive_type':
        return parseInt(value);

      case 'number_0_decimals':
      case 'number_1_decimals':
      case 'number_2_decimals':
      case 'number_3_decimals':
      case 'square_25cm2':
        return parseFloat(value);
    }

    return value;
  }; // Inspection Lists


  this.loadChecklist = function (id) {
    var suffix = '';

    if (typeof id != 'undefined' && id != null) {
      suffix = 'id=' + id;
      api.setLocalStoreValue('open_checklist_id', id);
    }

    api.getApiRequest('checklist', 'inspections/lists', suffix);
  };

  this.checklistHandler = function (e, data) {
    self.checklist = data.checklist;
    self.checklistNull = data.checklist;
    if (self.inspection) self.newSaveObject(self.inspection);
    $rootScope.$broadcast('checklistUpdated');
  };

  $rootScope.$on('checklistLoaded', self.checklistHandler);

  this.loadChecklistTree = function (id) {
    var suffix = '';
    if (typeof id != 'undefined' && id != null) suffix = '/' + id;
    api.getApiRequest('checklistTree', 'checklists' + suffix);
  };

  this.checklistTreeHandler = function (e, data) {
    self.checklistTree = data;
    $rootScope.$broadcast('checklistTreeUpdated');
  };

  $rootScope.$on('checklistTreeLoaded', self.checklistTreeHandler);

  this.getChecklists = function () {
    api.getApiRequest('checklists', 'checklists');
  };

  this.checklistsHandler = function (e, data) {
    self.checklists = data;
    $rootScope.$broadcast('checklistsUpdated');
  };

  $rootScope.$on('checklistsLoaded', self.checklistsHandler);

  this.setSelectedInspectionItem = function (id, value) {
    if (self.checklist != null && self.checklist.categories.length > 0) return recurseSet(self.checklist.categories, id, value);
  };

  function recurseSet(node, id, value) {
    //console.log('recurseSet', id, value, typeof node == 'object' ? node.name : node);
    if (node.id == id) {
      node.value = value;
      return true;
    } else if (_typeof(node.children) == 'object') {
      for (var i in node.children) {
        var ret = recurseSet(node.children[i], id, value);
        if (ret) return ret;
      }
    } else if (_typeof(node) == 'object') {
      for (var j in node) {
        var ret = recurseSet(node[j], id, value);
        if (ret) return ret;
      }
    }

    return false;
  }

  function recurseGet(node, id, field) {
    var anc = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';

    if (node.id == id) {
      //console.log('recurseGet id', id, field, anc, typeof node == 'object' ? node.trans : node);
      if (field == 'trans') return anc + (typeof node.trans != 'undefined' && typeof node.trans[$rootScope.locale] != 'undefined' ? node.trans[$rootScope.locale] : '');
      return node[field];
    } else if (_typeof(node.children) == 'object') {
      //console.log('recurseGet chi', id, field, anc, typeof node == 'object' ? node.trans : node);
      anc = anc + (typeof node.trans != 'undefined' && typeof node.trans[$rootScope.locale] != 'undefined' ? node.trans[$rootScope.locale] + ' > ' : '');

      for (var i in node.children) {
        var ret = recurseGet(node.children[i], id, field, anc);
        if (ret) return ret;
      }
    } else if (_typeof(node) == 'object') {
      for (var j in node) {
        var ret = recurseGet(node[j], id, field, anc);
        if (ret) return ret;
      }
    }

    return null;
  }

  this.createInspectionObject = function (type, id, value) {
    var items = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : true;
    var name = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : '';

    if (typeof type != 'undefined' && typeof value != 'undefined' && (items == false && typeof self.saveObject[type] != 'undefined' || (self.typeIsNonNumeric(type) || isNaN(value) == false) && typeof self.STD_VALUES[type] != 'undefined')) {
      if (items == false) {
        console.log('Changed ' + type + ' = ' + value, name);
        self.saveObject[type] = value;
      } else {
        if (self.STD_VALUES[type] != value) {
          console.log('Added ' + type + ' (' + id + ') = ' + value, name);
          self.saveObject.items[id] = value;
        } else if (typeof self.saveObject.items[id] != 'undefined') {
          console.log('Removed ' + type + ' (' + id + ') = ' + self.saveObject.items[id], name);
          if (type == 'image') api.deleteApiRequest('imageDeleteInspection', 'images', {
            'image_url': self.saveObject.items[id]
          });
          delete self.saveObject.items[id];
        }
      }

      $rootScope.$broadcast('inspectionItemUpdated');
    } else {
      console.log('NOT createInspectionObject', type, id, value, name);
    }
  }; // Inspections


  this.loadRemoteInspections = function (hive_id) {
    api.getApiRequest('inspections', 'inspections/hive/' + hive_id);
  };

  this.inspectionsHandler = function (e, data) {
    // get the result
    self.inspections = data;
    self.refresh(); //console.table(self.inspections);
  };

  this.inspectionsError = function (e, error) {
    console.log('inspections error ' + error.message + ' status: ' + error.status);
  };

  $rootScope.$on('inspectionsLoaded', self.inspectionsHandler);
  $rootScope.$on('inspectionsError', self.inspectionsError);

  this.loadRemoteInspection = function (inspection_id) {
    api.getApiRequest('inspection', 'inspections/' + inspection_id);
  };

  this.inspectionHandler = function (e, data) {
    self.inspection = data;
    $rootScope.$broadcast('inspectionUpdated', data);
  };

  $rootScope.$on('inspectionLoaded', self.inspectionHandler);

  this.refresh = function () {
    //update refresh count
    self.refreshCount++; // announce the update

    $rootScope.$broadcast('inspectionsUpdated');
  }; // Init


  self.getChecklists();
}]);
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */

app.service('groups', ['$http', '$rootScope', 'api', 'hives', function ($http, $rootScope, api, hives) {
  var self = this;

  this.reset = function () {
    this.refreshCount = 0;
    this.groups = [];
    this.invitations = [];
    this.hives = [];
    this.open_group_ids = [];
  };

  this.toggle_open_group = function (id) {
    var group = self.getGroupById(id);

    if (group) {
      group.open = !group.open;

      if (group.open && self.open_group_ids.indexOf(group.id) == -1) {
        self.open_group_ids.push(group.id);
      } else if (self.open_group_ids.indexOf(group.id) > -1) {
        var index = self.open_group_ids.indexOf(group.id);
        self.open_group_ids.splice(index, 1);
      }

      self.open_group_ids = self.open_group_ids.getUnique();
      api.setLocalStoreValue('open_group_ids', self.open_group_ids.join(','));
    }

    self.refresh();
  };

  this.getGroupById = function (id) {
    for (var i = 0; i < self.groups.length; i++) {
      var hive = self.groups[i];
      if (hive.id == id) return hive;
    }

    return null;
  };

  this.getGroupIndex = function (hiveId) {
    for (var i = 0; i < self.groups.length; i++) {
      var hive = self.groups[i];
      if (hive.id == hiveId) return i;
    }

    return null;
  };

  this.getGroupNameById = function (id) {
    var hive = self.getGroupById(id);
    return hive != null ? hive.name : null;
  };

  this.getHiveById = function (id) {
    for (var i = 0; i < self.hives.length; i++) {
      var hive = self.hives[i];
      if (hive.id == id) return hive;
    }

    return null;
  }; // Load groups (including hives, to not interfere with your own hives in hives.hives)


  this.loadRemoteGroups = function () {
    api.getApiRequest('groups', 'groups');
  };

  this.groupsHandler = function (e, data) {
    // get the result
    var result = data;
    if (result != null && typeof result.groups != 'undefined') self.groups = result.groups;
    if (result != null && typeof result.invitations != 'undefined') self.invitations = result.invitations;
    var group_ids = [];
    var open_group_ids = api.getLocalStoreValue('open_group_ids'); //console.log('open_group_ids', open_group_ids);

    if (open_group_ids != null) {
      group_ids = open_group_ids.split(',');

      for (var i = group_ids.length - 1; i >= 0; i--) {
        group_ids[i] = parseInt(group_ids[i]);
      }
    }

    for (var i = 0; i < self.groups.length; i++) {
      var group = self.groups[i];

      if (self.groups.length == 1) {
        group.open = true;
        if (self.open_group_ids.indexOf(group.id) == -1) self.open_group_ids.push(group.id);
      } else if (group_ids.indexOf(group.id) > -1) {
        group.open = true;
        if (self.open_group_ids.indexOf(group.id) == -1) self.open_group_ids.push(group.id);
      } else {
        group.open = false;
      }
    }

    self.processGroupHives();
    self.refresh();
  }; // Put all group-hives in hives array and add id's to selected and editable arrays


  this.processGroupHives = function (e, data) {
    self.hives = [];

    for (var i = 0; i < self.groups.length; i++) {
      var group = self.groups[i];

      if (typeof group.hives != 'undefined' && group.hives.length > 0) {
        group.hives_selected = [];
        group.hives_editable = [];

        for (var j = group.hives.length - 1; j >= 0; j--) {
          var hive = group.hives[j];

          if (hive != null && typeof hive.id != 'undefined') {
            if (hive.editable) group.hives_editable.push(hive.id);
            group.hives_selected.push(hive.id);
            hive = hives.addHiveCalculations(hive);
            hive.group_name = group.name;
            self.hives.push(hive);
          }
        }
      }
    } //console.log(self.hives);

  };

  this.groupsError = function (e, error) {
    console.log('groups error ' + error.message + ' status: ' + error.status);
  };

  $rootScope.$on('groupsLoaded', self.groupsHandler);
  $rootScope.$on('saveGroupLoaded', self.groupsHandler);
  $rootScope.$on('deleteGroupLoaded', self.groupsHandler);
  $rootScope.$on('groupsError', self.groupsError);

  this.refresh = function () {
    //update refresh count
    self.refreshCount++; // announce the update

    $rootScope.$broadcast('groupsUpdated');
  };

  self.reset();
  $rootScope.$on('reset', self.reset);
  self.loadRemoteGroups();
}]);
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */

app.service('images', ['$http', '$rootScope', 'api', function ($http, $rootScope, api) {
  var self = this;

  this.reset = function () {
    this.refreshCount = 0;
    this.activeImage = null;
    this.images = [];
  };

  this.getImageByThumbUrl = function (thumbUrl) {
    for (var i = 0; i < self.images.length; i++) {
      var image = self.images[i];
      if (image.thumb_url == thumbUrl) return image;
    }

    return null;
  };

  this.getImageByImageUrl = function (imageUrl) {
    for (var i = 0; i < self.images.length; i++) {
      var image = self.images[i];
      if (image.image_url == imageUrl) return image;
    }

    return null;
  };

  this.setActiveImage = function (image) {
    self.activeImage = image;
    $rootScope.activeImage = image;
  };

  this.setActiveImageByUrl = function (imageUrl) // can be thumb. image, or blob
  {
    console.log(_typeof(imageUrl), imageUrl);
    var image = {
      'image_url': null,
      'thumb_url': null
    };

    if (_typeof(imageUrl) == 'object') // load local image
      {
        image.image_url = imageUrl.$ngfBlobUrl;
        var d = imageUrl.lastModifiedDate;
        image.date = d.getFullYear() + '-' + d.getMonth() + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();
      } else if (typeof imageUrl == 'string' && imageUrl.indexOf('/images/') > -1) {
      image = self.getImageByImageUrl(imageUrl);
    } else {
      image = self.getImageByThumbUrl(imageUrl);
    }

    self.setActiveImage(image);
  };

  this.deleteImageByUrl = function (image) // can be thumb. image, or blob
  {
    var imageUrl = image;

    if (_typeof(imageUrl) == 'object') // load local image
      {
        imageUrl = imageUrl.$ngfBlobUrl;
      }

    api.deleteApiRequest('imageDelete', 'images', {
      'image_url': imageUrl
    });
  }; // Load images


  this.loadRemoteImages = function () {
    api.getApiRequest('images', 'images');
  };

  this.imagesHandler = function (e, data) {
    // get the result
    var result = data;
    if (typeof result != 'undefined' && result != null && result.length > 0) self.images = result; // for (var i = 0; i < self.images.length; i++) 
    // {
    // 	var image = self.images[i];
    // }

    self.refresh();
  };

  this.imagesError = function (e, error) {
    console.log('images error ' + error.message + ' status: ' + error.status);
  };

  $rootScope.$on('imageDeleteLoaded', self.loadRemoteImages);
  $rootScope.$on('imagesLoaded', self.imagesHandler);
  $rootScope.$on('imagesError', self.imagesError);

  this.refresh = function () {
    // 
    self.setActiveImage(null); //update refresh count

    self.refreshCount++; // announce the update

    $rootScope.$broadcast('imagesUpdated');
  };

  self.reset();
  $rootScope.$on('reset', self.reset);
  self.loadRemoteImages();
}]);
