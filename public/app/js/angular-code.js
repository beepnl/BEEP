function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

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
    }; // if (method == 'PUT' || 'PATCH')
    // {
    // 	req.headers['X-HTTP-Method-Override'] = method;
    // 	req.method = 'POST';
    // }
    // check if it has to be authorized

    if (type != 'authenticate' && type != 'register' && self.getApiToken() != null) {
      req.headers['Authorization'] = 'Bearer ' + self.getApiToken() + '';
    }

    req.headers['Accept-Language'] = $rootScope.locale; // do the request

    self.doApiRequest(type, req);
  };

  this.getApiRequest = function (type, request, params) {
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
    this.sensortypes = [];
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
    if (typeof data.hivetypes != 'undefined' || typeof data.beeraces != 'undefined') $rootScope.$broadcast('taxonomyListsUpdated');
  };

  $rootScope.$on('taxonomyListsLoaded', self.taxonomyHandler);
  $rootScope.$on('taxonomyItemsLoaded', self.taxonomyHandler);

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

  this.loadRemoteSensors = function () {
    api.getApiRequest('sensors', 'sensors');
  };

  this.handleSensors = function (e, result) {
    if (result.length > 0) {
      self.sensors = result;
      self.sensors_owned = [];

      for (var i = 0; i < result.length; i++) {
        var s = result[i];
        if (s.owner) self.sensors_owned.push(s);
      }

      $rootScope.hasSensors = true;
      $rootScope.$broadcast('sensorsUpdated');
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

  this.sensorsError = function (e, error) {
    console.log('measurements sensorsError ' + error.message + ' status: ' + error.status);

    if (error.status == 404) {
      $rootScope.hasSensors = false;
      self.stopLoadingMeasurements();
    }
  };

  $rootScope.$on('sensorsLoaded', self.handleSensors);
  $rootScope.$on('saveSensorsLoaded', self.handleSensors);
  $rootScope.$on('sensorsError', self.sensorsError);
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


  self.loadRemoteSensors();
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
    'grade': 0
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

  this.typeIsNonNumeric = function (type) {
    switch (type) {
      case 'default':
      case 'date':
      case 'text':
      case 'list':
      case 'select':
      case 'select_country':
        return true;
    }

    return false;
  };

  this.parseTypeValueForChecklistInput = function (type, value) {
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
        return parseFloat(value);
    }

    return value;
  }; // Inspection Lists


  this.loadChecklist = function (id) {
    var suffix = '';
    if (typeof id != 'undefined' && id != null) suffix = 'id=' + id;
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
        //console.log('Changed '+type+' = '+value, name);
        self.saveObject[type] = value;
      } else {
        if (self.STD_VALUES[type] != value) {
          //console.log('Added '+type+' ('+id+') = '+value, name);
          self.saveObject.items[id] = value;
        } else if (typeof self.saveObject.items[id] != 'undefined') {
          //console.log('Removed '+type+' ('+id+') = '+self.saveObject.items[id], name);
          delete self.saveObject.items[id];
        }
      }
    } else {//console.log('NOT createInspectionObject', type, id, value, name);
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
 * Load controller
 */

app.controller('LoadCtrl', function ($scope, $rootScope, $location, settings, api) {
  // handle loading of all the settings
  $scope.init = function () {
    // start loading the settings, or login
    if (api.token != null) {
      settings.fetchSettings();
    } else {
      $location.path('/login');
    }
  }; // when the settings are loaded


  $scope.settingsFetched = $rootScope.$on('settingsLoaded', function (e, data) {
    // hide splash
    $rootScope.showSplash = false; // redirect to the dashboard

    if ($location.path() != '/user/edit') $location.path('/locations'); // remove this listener

    $scope.settingsFetched();
  }); // when the settings could not be fetched

  $scope.settingsError = $rootScope.$on('settingsError', function (e, error) {
    // check the error
    if (api.token != null) {
      // show the error message
      $rootScope.showMessage($rootScope.lang.could_not_load_settings, null, $rootScope.lang.login_title); // redirect to the dashboard

      $location.path('/locations');
    } else {
      $location.path('/login');
    }
  }); // call the init function

  $scope.init(); // remove references to the controller

  $scope.removeListeners = function () {
    $scope.settingsFetched();
    $scope.settingsError();
  };

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  });
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * User controller
 */

app.controller('UserCtrl', function ($scope, $rootScope, $window, $location, $routeParams, api) {
  // set the title
  $rootScope.title = $rootScope.lang.login_title;
  $scope.formStatus = '';
  $scope.message = null;
  $scope.error = null;
  $scope.fields = {};

  $scope.init = function () {
    // hide splash
    $rootScope.showSplash = false; // check if we're authenticated

    if (api.getApiToken() != null) {
      if ($location.path() != '/user/edit') {
        $location.path('/load');
      } else {
        $rootScope.title = $rootScope.lang.User;
        $scope.setEditFields();
      }
    } // Check locale


    if ($routeParams.language != undefined && $routeParams.language != $rootScope.locale) {
      $rootScope.switchLocale($routeParams.language);
      $location.search('language', null);
    }

    if ($routeParams.msg != undefined && $routeParams.msg != '') {
      $scope.message = {
        show: true,
        resultType: 'success',
        resultMessage: $rootScope.lang[$routeParams.msg],
        verifyLink: false
      };
    }

    if ($routeParams.email != undefined && $routeParams.email != '') {
      $scope.fields.login.email = $routeParams.email;
      $scope.fields.register.email = $routeParams.email;
    }
  };

  $scope.confirmDeleteUser = function () {
    $rootScope.showConfirm($rootScope.lang.Delete + ' ' + $rootScope.lang.user_data + '?', $scope.reallyConfirmDeleteUser);
  };

  $scope.reallyConfirmDeleteUser = function () {
    $rootScope.showConfirm($rootScope.lang.delete_complete_account, $scope.deleteUser);
  };

  $scope.deleteUser = function () {
    api.deleteApiRequest('deleteUser', 'user'); // delete myself
  };

  $scope.userDeleteLoadedHandler = $rootScope.$on('deleteUserLoaded', function (e, data) {
    $rootScope.doLogout(0);
  });

  $scope.setEditFields = function () {
    $scope.fields.edit = {
      name: $rootScope.user.name,
      email: $rootScope.user.email,
      password: '',
      password_confirmation: '',
      policy_accepted: $rootScope.user.policy_accepted == $rootScope.lang.policy_version
    };
  };

  $scope.editUser = function () {
    // reset the errors
    $scope.formStatus = '';
    $scope.resetErrors(); // set the errors

    var validate = $rootScope.validateFields($scope.fields.edit, $scope.edit, $scope.error);

    if (validate === true) {
      if ($scope.fields.edit.policy_accepted) $scope.fields.edit.policy_accepted = $rootScope.lang.policy_version;
      api.patchApiRequest('editUser', 'user', $scope.fields.edit);
    } else {
      $scope.message = validate;
    }
  };

  $scope.userEditLoadedHandler = $rootScope.$on('editUserLoaded', function (e, data) {
    $scope.formStatus = 'edited';
    api.handleAuthentication(data);
  });
  $scope.userUpdatedHandler = $rootScope.$on('userUpdated', $scope.setEditFields);

  $scope.resetErrors = function () {
    $scope.message = {
      show: false,
      resultType: 'error',
      resultMessage: ''
    };
    $scope.error = {
      email: false,
      password: false,
      password_retype: false
    };
  };

  $scope.resetErrors();
  $scope.fields.login = {
    email: '',
    password: ''
  };

  $scope.retreiveToken = function (e) {
    e.preventDefault();
    $scope.resetErrors(); // check if errors

    var validate = $rootScope.validateFields($scope.fields.login, $scope.login, $scope.error);

    if (validate === true) {
      // data
      var input = $scope.fields.login; // go register the user

      api.login(input.email, input.password);
    } else {
      $scope.message = validate;
    }
  };

  $scope.fields.register = {
    email: '',
    password: '',
    password_retype: '',
    policy_accepted: ''
  };

  $scope.registerUser = function (e) {
    // prevent default
    e.preventDefault(); // reset the errors

    $scope.resetErrors(); // set the errors

    var validate = $rootScope.validateFields($scope.fields.register, $scope.register, $scope.error);

    if (validate === true) {
      if ($scope.fields.register.policy_accepted) $scope.fields.register.policy_accepted = $rootScope.lang.policy_version; // go register the user

      var input = $scope.fields.register;
      $rootScope.user = input;
      $rootScope.user.name = input.email;
      api.registerUser(input.password, input.email, input.policy_accepted);
    } else {
      $scope.message = validate;
    }
  }; // Auth handlers


  $scope.sendVerificationEmail = function () {
    api.postApiRequest('verify', 'email/resend', $scope.fields.login);
  };

  $scope.authError = function (e, error) {
    // check email
    console.log(error);
    msg = error.message != undefined ? error.message : error;

    if (error.status == 503) {
      $scope.error.password = false;
      $scope.error.password_retype = false;
      $scope.error.email = false;
      msg = 'server_down';
    } // add a link


    var transMessage = $rootScope.lang[msg];
    var verifyOn = false;
    var resultStyle = 'error';
    if (msg.indexOf('email') !== -1) $scope.error.email = true;

    if (msg.indexOf('password') !== -1) {
      $scope.error.password = true;
      $scope.error.password_retype = true;
    } // check password


    if (msg == 'no_password_match') {
      $scope.error.password = false;
      $scope.error.password_retype = true;
    } else if (msg == 'email_not_verified') {
      verifyOn = true;
    } else if (msg == 'email_verification_sent') {
      $scope.error.email = false;
      $scope.formStatus = 'registered';
      verifyOn = true;
      resultStyle = 'success';
    } // set the message


    $scope.message = {
      show: true,
      resultType: resultStyle,
      resultMessage: transMessage,
      verifyLink: verifyOn
    };
  };

  $scope.userAuthenticateHandler = $rootScope.$on('authenticateLoaded', function (e, data) {
    $location.path('/load');
  });
  $scope.userDeleteErrorHandler = $rootScope.$on('deleteUserError', $scope.authError);
  $scope.userEditErrorHandler = $rootScope.$on('editUserError', $scope.authError);
  $scope.userAuthenticateErrorHandler = $rootScope.$on('authenticateError', $scope.authError);
  $scope.userRegisteredErrorHandler = $rootScope.$on('registerError', $scope.authError);
  $scope.userRegisteredHandler = $rootScope.$on('registerLoaded', function (e, data) {
    var result = data;
    if (result.api_token != null) api.setApiToken(result.api_token); // set the status on registered

    $scope.formStatus = 'registered';
  });

  $scope.back = function () {
    $location.path('/login');
  };

  $scope.backListener = $rootScope.$on('backbutton', $scope.back);
  $scope.init(); // remove the listeners

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  }); // remove listeners

  $scope.removeListeners = function () {
    $scope.userUpdatedHandler();
    $scope.userAuthenticateHandler();
    $scope.userAuthenticateErrorHandler();
    $scope.userRegisteredHandler();
    $scope.userRegisteredErrorHandler();
    $scope.userEditErrorHandler();
    $scope.userEditLoadedHandler();
    $scope.userDeleteLoadedHandler();
    $scope.userDeleteErrorHandler();
    $scope.backListener();
  };
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Settings controller
 */

app.controller('SettingsCtrl', function ($scope, $rootScope, $window, $timeout, $location, $filter, $interval, api, $routeParams, ngDialog, settings, hives, measurements) {
  // settings
  $scope.hives = [];
  $scope.sensors = [];
  $scope.weightSensors = [];
  $scope.weightSensorsCalibrate = {};
  $scope.lastSensorValues = [];
  $scope.lastSensorDate = null;
  $scope.selectedSensorId = null;
  $scope.selectedSensor = null;
  $scope.calibrate_msg = null;
  $scope.calibrating = false; // settings (readonly)

  $scope.firmware = settings.firmware_version;
  $scope.updated_at = settings.updated_at;
  $scope.userType = settings.type; // handlers

  $scope.isChecking = false;
  $scope.isLoading = false;
  $scope.isAutomatic = $scope.connection_type == 'automatic' ? true : false; //$scope.changed               = false;

  $scope.init = function () {
    $scope.hives = hives.hives;
    $scope.sensors = measurements.sensors;
    $scope.selectedSensorId = measurements.sensorId;
    $scope.selectedSensor = measurements.getSensorById($scope.selectedSensorId);
    $scope.calibrate_weight = settings.settings.calibrate_weight;
    $scope.getSensorValues();
  };

  $scope.inSensorNames = function (a, b) {
    return typeof SENSOR_NAMES[a] != 'undefined';
  };

  $scope.nonZeroWeight = function (a) {
    return a.value != 0 && a.name != 'id';
  };

  $scope.updateSensors = function () {
    $scope.sensors = measurements.sensors;
  };

  $scope.sensorsUpdatedHandler = $rootScope.$on('sensorsUpdated', $scope.updateSensors);

  $scope.updateWeightSensors = function () {
    $scope.weightSensors = convertOjectToNameArray(measurements.weightSensors);
  };

  $scope.weightSensorsUpdatedHandler = $rootScope.$on('weightSensorsUpdated', $scope.updateWeightSensors);

  $scope.getSensorValues = function (id) {
    if (typeof id != 'undefined') $scope.selectedSensorId = id;

    if ($scope.selectedSensorId) {
      $scope.selectedSensor = measurements.getSensorById($scope.selectedSensorId);
      $scope.lastSensorDate = null;
      measurements.loadLastWeightSensorValues($scope.selectedSensorId);
      $scope.loadLastSensorValues();
    }
  };

  $scope.handleLastSensorValues = function () {
    if (measurements.lastSensorValues.calibrating_weight) {
      $scope.calibrating = true;
      $scope.calibrate_msg = $rootScope.lang.calibration_started; //$scope.loading       = true;

      $scope.calibrate_weight = measurements.lastSensorValues.calibrating_weight;
    } else {
      $scope.calibrating = false;
      $scope.calibrate_msg = $rootScope.lang.calibration_ended;
    } //console.log(measurements.lastSensorValues);


    $scope.lastSensorValues = convertOjectToNameArray(measurements.lastSensorValues);
    $scope.lastSensorDate = moment(measurements.lastSensorDate).format('llll');
  };

  $scope.lastSensorValuesUpdatedHandler = $rootScope.$on('lastSensorValuesUpdated', $scope.handleLastSensorValues);
  $scope.loadLastSensorValuesTimer = null;

  $scope.loadLastSensorValues = function () {
    var activate = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
    if (angular.isDefined($scope.loadLastSensorValuesTimer)) $interval.cancel($scope.loadLastSensorValuesTimer); // Start loading interval

    if (activate && $scope.selectedSensorId) {
      $scope.loadLastSensorValuesTimer = $interval(function () {
        $scope.getSensorValues();
      }, 10000);
    }
  };

  $scope.offsetWeight = function () {
    // get weight from sensors, save offsets
    if ($scope.selectedSensorId) measurements.weightOffset({
      'id': $scope.selectedSensorId
    });
  };

  $scope.calibrateWeight = function (weight_kg) {
    if ($scope.selectedSensorId && weight_kg) measurements.weightCalibration({
      'id': $scope.selectedSensorId,
      'weight_kg': weight_kg
    });
  };

  $scope.calibrateWeightHandler = $rootScope.$on('weightCalibrationLoaded', $scope.calibrate);

  $scope.calibrate = function (e, data) {
    if (data == 'calibrating_weight') {
      $scope.calibrating = true;
      $scope.calibrate_msg = $rootScope.lang.calibration_started;
    } else {
      $scope.calibrating = false;
      $scope.calibrate_msg = $rootScope.lang.calibration_ended;
    }
  };

  $scope.saveSettings = function (e) {
    var data = {
      hives: $scope.hives,
      sensors: $scope.sensors // actuator_settings :

    }; // set loading

    $scope.isLoading = true; // save the settings to the server

    settings.saveSettings(data);
    console.log('settings saved');
  };

  $scope.settingsSavedHandler = $rootScope.$on('saveSettingsLoaded', function (e, data) {
    // reset the save button
    $scope.isLoading = false;
  });
  $scope.settingsErrorHandler = $rootScope.$on('saveSettingsError', function (e, data) {
    // message
    $rootScope.showMessage('Instellingen konden niet worden opgeslagen', null, 'Instelligen', $rootScope.lang.ok);
    $scope.isLoading = false; // set the status to offline
    //settings.setStatus('offline'); // Why taking the status of the app offline? Only because the settings can not be saved on the server?
  }); //refresh iscroll

  $scope.refreshIscroll = function () {// $timeout( function()
    // {
    // 	if(typeof $rootScope.myScroll['settings-form-wrapper'] != 'undefined')
    //     	$rootScope.myScroll['settings-form-wrapper'].refresh();
    // }, 200);
  };

  $scope.back = function () {
    if ($rootScope.optionsDialog) {
      $rootScope.optionsDialog.close();
    } else {
      $rootScope.historyBack();
    }
  }; //close options dialog


  $scope.backListener = $rootScope.$on('backbutton', $scope.back);
  $scope.init(); // remove the listeners

  $scope.$on('$destroy', function () {
    $scope.loadLastSensorValues(false);
    $scope.removeListeners();
  }); // remove listeners

  $scope.removeListeners = function () {
    $scope.settingsSavedHandler();
    $scope.settingsErrorHandler();
    $scope.sensorsUpdatedHandler();
    $scope.lastSensorValuesUpdatedHandler();
    $scope.calibrateWeightHandler();
    $scope.weightSensorsUpdatedHandler();
    $scope.backListener();
  };
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */

app.controller('LocationsCtrl', function ($scope, $rootScope, $window, $location, $routeParams, $filter, settings, api, moment, hives, NgMap, inspections, measurements) {
  $rootScope.title = $rootScope.lang.locations_title;
  $scope.showMore = false; // multiple locations

  $scope.redirect = null;
  $scope.locations = null;
  $scope.hiveLocation = null;
  $scope.selectedLocationIndex = 0;
  $scope.error_msg = null;
  $scope.hivetypes = null;
  $scope.hive_type = null;
  $scope.hive = {
    "name": $rootScope.lang.Location + ' ' + (hives.hives.length + 1),
    "color": "#F29100",
    "hive_type_id": "",
    "hive_amount": "1",
    "brood_layers": "2",
    "honey_layers": "1",
    "frames": "10",
    "offset": "1",
    "prefix": "Kast",
    "country_code": "nl",
    "city": "",
    "postal_code": "",
    "street": "",
    "street_no": "",
    "lat": 52,
    "lon": 5
  };
  $scope.types = "['address']";
  $scope.mybounds = {
    center: {
      lat: $scope.hive.lat,
      lng: $scope.hive.lon
    },
    radius: 200000
  };

  $scope.init = function () {
    if (api.getApiToken() == null) {
      $location.path('/login');
    } else if ($location.path().indexOf('/locations') > -1) {
      $scope.hivetypes = settings.hivetypes;

      if (hives.locations.length > 0) {
        $scope.locations = hives.locations;
      }

      if ($location.path() == 'locations/create' || $location.path().indexOf('/edit') > -1) {
        $rootScope.title = $rootScope.lang.Location;
        $scope.getGPS(); // NgMap.getMap().then(function(map) 
        // {
        // 	$scope.map = map;
        // });

        if ($routeParams.locationId != undefined) {
          $scope.locationsUpdate();
        }
      } else {
        hives.loadRemoteLocations();
      }
    }
  };

  $scope.toggleLoc = function (loc) {
    hives.toggle_open_loc(loc.id);
  };

  $scope.setHiveType = function (id) {
    console.log(id);
    $scope.hive.hive_type_id = id;
  };

  $scope.updateTaxonomy = function () {
    $scope.hivetypes = settings.hivetypes;
  };

  $scope.taxonomyHandler = $rootScope.$on('taxonomyListsUpdated', $scope.updateTaxonomy);

  $scope.natSort = function (a, b) {
    return naturalSort(a.value, b.value);
  };

  $scope.transSort = function (a) {
    var locale = $rootScope.locale;
    return a.trans[locale];
  };

  $scope.placeChanged = function () {
    $scope.place = this.getPlace(); //console.log($scope.place);

    if ($scope.map != undefined) {
      $scope.map.setCenter($scope.place.geometry.location);
      $scope.map.setZoom(16);
    }

    var lat = round_dec($scope.place.geometry.location.lat(), 3);
    var lon = round_dec($scope.place.geometry.location.lng(), 3);
    $scope.hive.lat = lat;
    $scope.hive.lon = lon;

    if ($scope.hiveLocation != undefined) {
      $scope.hiveLocation.lat = lat;
      $scope.hiveLocation.lon = lon;
    } // Fill Hive address


    if ($scope.place.address_components.length > 0) {
      for (var i = 0; i < $scope.place.address_components.length; i++) {
        comp = $scope.place.address_components[i];
        compName = comp.types.length > 0 ? comp.types[0] : null; // See Google maps API spec: https://developers.google.com/maps/documentation/geocoding/start#Types

        switch (compName) {
          case "route":
            $scope.hive.street = comp.short_name;
            if ($scope.hiveLocation != undefined) $scope.hiveLocation.street = comp.short_name;
            break;

          case "street_number":
            $scope.hive.street_no = parseInt(comp.short_name);
            if ($scope.hiveLocation != undefined) $scope.hiveLocation.street_no = parseInt(comp.short_name);
            break;

          case "country":
            $scope.hive.country_code = comp.short_name.toLowerCase();
            if ($scope.hiveLocation != undefined) $scope.hiveLocation.country_code = comp.short_name.toLowerCase();
            break;

          case "postal_code":
            $scope.hive.postal_code = comp.short_name;
            if ($scope.hiveLocation != undefined) $scope.hiveLocation.postal_code = comp.short_name;
            break;

          case "locality":
            $scope.hive.city = comp.short_name;
            if ($scope.hiveLocation != undefined) $scope.hiveLocation.city = comp.short_name;
            break;
        }
      }
    }
  };

  $scope.getGPS = function () {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function (position) {
        $scope.hive.lat = position.coords.latitude;
        $scope.hive.lon = position.coords.longitude;
      });
    }
  };

  $scope.refreshAndGoHome = function () {
    $location.path('/locations');
  };

  $scope.showError = function (type, error) {
    $scope.error_msg = error.status == 500 ? $rootScope.lang.Error : $rootScope.lang.empty_fields + (error.status == 422 ? ". Error: " + convertOjectToArray(error.message).join(', ') : '');
  };

  $scope.loadLocationIndex = function () {
    //console.log(id);
    if ($routeParams.locationId != undefined) {
      $scope.hiveLocation = hives.getHiveLocationById($routeParams.locationId);
    }
  };

  $scope.locationsUpdate = function (e, type) {
    $scope.showMore = hives.locations.length > 1 ? true : false;

    if (hives.locations.length > 0) {
      $scope.locations = hives.locations;
      $scope.loadLocationIndex();
    } else {
      $location.path('/locations/create');
    }
  };

  $scope.locationsError = function () {
    $scope.hiveLocation = null;
  };

  $scope.hivesHandler = $rootScope.$on('hivesUpdated', $scope.locationsUpdate);
  $scope.locationsHandler = $rootScope.$on('locationsLoaded', $scope.locationsUpdate);
  $scope.locationsErrorHandler = $rootScope.$on('locationsError', $scope.locationsError);

  $scope.createLocation = function () {
    api.postApiRequest('saveLocation', 'locations', $scope.hive);
    $scope.redirect = "/locations";
  };

  $scope.saveLocation = function (back) {
    api.patchApiRequest('saveLocation', 'locations/' + $scope.hiveLocation.id, $scope.hiveLocation);
    $scope.redirect = "/locations";
  };

  $scope.deleteLocation = function () {
    var text = $scope.hiveLocation.hives.length > 0 ? $rootScope.lang.first_remove_hives : '';
    text += "\r\n" + $rootScope.lang.Delete + ' ' + $rootScope.lang.location + ' ' + $scope.hiveLocation.name + '?';
    $rootScope.showConfirm(text, $scope.performDeleteLocation);
  };

  $scope.performDeleteLocation = function () {
    $scope.redirect = "/locations";
    api.deleteApiRequest('saveLocation', 'locations/' + $scope.hiveLocation.id, $scope.hiveLocation);
  };

  $scope.locationChanged = function () {
    if ($scope.redirect != null) {
      $location.path($scope.redirect);
      $scope.redirect = null;
    }
  };

  $scope.locationsSaveHandler = $rootScope.$on('saveLocationLoaded', $scope.locationChanged);
  $scope.locationsErrorHandler = $rootScope.$on('saveLocationError', $scope.showError);

  $scope.back = function () {
    if ($rootScope.optionsDialog) {
      $rootScope.optionsDialog.close();
    } else {
      $rootScope.historyBack();
    }
  }; //close options dialog


  $scope.backListener = $rootScope.$on('backbutton', $scope.back); // remove references to the controller

  $scope.removeListeners = function () {
    $scope.taxonomyHandler();
    $scope.locationsSaveHandler();
    $scope.locationsErrorHandler();
    $scope.hivesHandler();
    $scope.locationsHandler();
    $scope.locationsErrorHandler();
    $scope.backListener();
  };

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  }); // call the init function

  $scope.init();
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */

app.controller('HivesCtrl', function ($scope, $rootScope, $window, $location, $filter, $routeParams, settings, api, moment, hives, inspections, groups) {
  $rootScope.title = $rootScope.lang.hives_title;
  $scope.pageTitle = '';
  $scope.showMore = false; // multiple hives

  $scope.redirect = null;
  $scope.hives = [];
  $scope.hive = null;
  $scope.hive_loc = null;
  $scope.hive_type = null;
  $scope.bee_race = null;
  $scope.beeraces = null;
  $scope.hivetypes = null;
  $scope.locations = null;
  $scope.error_msg = null;
  $scope.selectedHiveIndex = 0;
  $scope.queen_colored = false;
  $scope.queen_colors = ['#4A90E2', '#F4F4F4', '#F8DB31', '#D0021B', '#7ED321', '#4A90E2', '#F4F4F4', '#F8DB31', '#D0021B', '#7ED321']; // year ending of birth year is index

  $scope.orderName = 'name';
  $scope.orderDirection = false;
  $scope.dateFormat = 'yyyy-MM-dd';

  $scope.setDateLanguage = function () {
    $("#dtBox").DateTimePicker({
      dateFormat: $scope.dateFormat,
      // ISO formatted date
      language: $rootScope.locale,
      mode: 'date',
      formatHumanDate: function formatHumanDate(dateObj, mode, format) {
        var output = '';
        output += dateObj.day + ' ';
        output += parseInt(dateObj.dd) + ' ';
        output += dateObj.month + ' ';
        output += dateObj.yyyy;
        return output;
      },
      afterShow: function afterShow(inputElement) {
        $("#dtBox .dtpicker-compValue").attr('type', 'tel'); // set mobile input keyboard to numeric
      }
    });
  };

  $scope.init = function () {
    if (api.getApiToken() == null) {
      $location.path('/login');
    } else if ($location.path().indexOf('/hives') > -1) {
      if ($routeParams.hiveId != undefined || $location.path().indexOf('/hives/create') > -1) {
        $scope.setDateLanguage();
        $scope.initHives();
        $rootScope.title = $rootScope.lang.Hive;

        if ($location.path().indexOf('/hives/create') > -1) {
          $scope.pageTitle = $rootScope.mobile ? $rootScope.lang.New + ' ' + $rootScope.lang.hive : $rootScope.lang.create_new + ' ' + $rootScope.lang.hive;
        }
      } else {
        if (hives.hives.length > 0) {
          $scope.initHives();
        } else {
          $location.path('/locations');
        }
      }
    }
  };

  $scope.initHives = function () {
    $scope.beeraces = settings.beeraces;
    $scope.hivetypes = settings.hivetypes;
    $scope.locations = hives.locations_owned;

    if (hives.hives.length > 0) {
      $scope.hives = hives.hives_owned;
    }

    $scope.showMore = $scope.hives.length > 1 ? true : false;

    if ($routeParams.location_id) {
      $scope.hive_loc = {
        id: parseInt($routeParams.location_id)
      };
    }

    if ($location.path().indexOf('/hives/create') > -1) {
      $scope.hive = {
        'location_id': $scope.hive_loc != null ? $scope.hive_loc.id : null,
        'name': $rootScope.lang.Hive + ' ' + ($scope.hives.length + 1),
        'color': '',
        'hive_type_id': '',
        'brood_layers': 1,
        'honey_layers': 1,
        'frames': 10,
        'queen': {}
      }; //console.log($scope.hive);

      $scope.add_hive_watchers();
    } else {
      $scope.loadHiveIndex();
    }
  };

  $scope.updateTaxonomy = function () {
    $scope.beeraces = settings.beeraces;
    $scope.hivetypes = settings.hivetypes; //console.log('taxonomyListsUpdated');
  };

  $scope.taxonomyHandler = $rootScope.$on('taxonomyListsUpdated', $scope.updateTaxonomy);

  $scope.hiveFilter = function (a, b) {
    console.log(a, b);
  };

  $scope.setOrder = function (name) {
    if ($scope.orderName == name) {
      $scope.orderDirection = !$scope.orderDirection;
    } else {
      if (name == 'attention' || 'impression') {
        $scope.orderDirection = true;
      } else {
        $scope.orderDirection = false;
      }
    }

    $scope.orderName = name;
  };

  $scope.natSort = function (a, b) {
    //console.log($scope.orderName, a.value, b.value);
    if ($scope.orderName == 'impression') {
      return b.value - a.value;
    } else if ($scope.orderName == 'attention') {
      if (a.value != 1) return -1;
      if (b.value != 1) return 1;
      return b.value - a.value;
    } else if ($scope.orderName == 'reminder_date') {
      if (a.value == null || a.value == '') return -1;
      if (b.value == null || b.value == '') return 1;
    }

    return naturalSort(a.value, b.value);
  };

  $scope.transSort = function (a) {
    var locale = $rootScope.locale;
    return a.trans[locale];
  };

  $scope.hivesUpdate = function (e, type) {
    $scope.initHives();
  };

  $scope.selectLocation = function (item) {
    $scope.hive.location_id = item.id;
  };

  $scope.selectHiveType = function (item) {
    $scope.hive.hive_type_id = item.id;
  };

  $scope.selectBeeRace = function (item) {
    $scope.hive.queen.race_id = item.id;
  };

  $scope.add_hive_watchers = function () {
    if ($scope.hive.queen != undefined && $scope.hive.queen != null && $scope.hive.queen.created_at == null) $scope.hive.queen.created_at = moment().format($scope.dateFormat.toUpperCase());
    $scope.queen_colored = $scope.hive.queen.color != '' && $scope.hive.queen.color != null;
    $scope.queenBirthColor();
    $scope.hive_loc = {
      id: $scope.hive.location_id
    };
    if ($scope.hive.hive_type_id && $scope.hive.hive_type_id != '') $scope.hive_type = {
      id: $scope.hive.hive_type_id
    };
    if ($scope.hive.queen.race_id && $scope.hive.queen.race_id != '') $scope.bee_race = {
      id: $scope.hive.queen.race_id
    }; // Watch layers and frames

    $scope.$watch('hive.brood_layers', function (o, n) {
      if (n != o) $scope.layersChange(o - n, 'brood');
    });
    $scope.$watch('hive.honey_layers', function (o, n) {
      if (n != o) $scope.layersChange(o - n, 'honey');
    });
    $scope.$watch('hive.frames', function (o, n) {
      if (n != o) $scope.framesChange(o - n);
    });
    $scope.$watch('hive.queen.created_at', function (o, n) {
      if (n != o) $scope.queenBirthColor(true);
    }); // $scope.$watch('hive_loc', function(o,n){ if (n != o && $scope.hive_loc != null) $scope.hive.location_id = $scope.hive_loc.id });
    // $scope.$watch('hive_type', function(o,n){ if (n != o && $scope.hive_type != null) $scope.hive.hive_type_id = $scope.hive_type.id; });
    //$scope.$watch('bee_race', function(o,n){ if (n != o && $scope.bee_race != null) $scope.hive.queen.race_id = $scope.bee_race.id });
  };

  $scope.loadHiveIndex = function () {
    $scope.hive = hives.getHiveById($routeParams.hiveId);
    if ($scope.hive == null) $scope.hive = groups.getHiveById($routeParams.hiveId);

    if ($scope.hive != undefined && ($location.path().indexOf('/hives/create') > -1 || $location.path().indexOf('/edit') > -1)) {
      //console.log('loadHiveIndex', $routeParams.hiveId, $scope.hive.name);
      $scope.pageTitle = $scope.hive.name;
      $scope.add_hive_watchers();
    }
  };

  $scope.queen_colored_change = function () {
    if ($scope.queen_colored) {
      $scope.hive.queen.color = '#FFFFFF';
    } else {
      $scope.hive.queen.color = '';
    }
  };

  $scope.queenBirthColor = function (forceChangeColor) {
    format = $scope.dateFormat.toUpperCase();
    date = $scope.hive.queen.created_at;
    dateNow = moment();
    dateBirth = moment(date, format);
    yearsOld = dateNow.diff(dateBirth, 'years', true); //console.log(format, yearsOld);

    $scope.hive.queen.age = isNaN(yearsOld) ? 0 : round_dec(yearsOld, 1);
    year = moment(date).year();
    yearEnd = year.toString().substr(3, 1);
    if ($scope.queen_colored && ($scope.hive.queen.color == '' || forceChangeColor)) $scope.hive.queen.color = $scope.queen_colors[yearEnd];
  };

  $scope.layersChange = function (amount, type) {
    if ($scope.hive.layers == undefined || $scope.hive.layers.length == 0) return;
    l = angular.copy($scope.hive.layers[0]);
    l.type = type;

    if (amount > 0) {
      $scope.hive.layers.push(l);
    } else if (amount < 0 && $scope.hive.layers.length > 1) {
      for (var i = $scope.hive.layers.length - 1; i >= 0; i--) {
        l = $scope.hive.layers[i];

        if (l.type == type) {
          $scope.hive.layers.splice(i, 1);
          break;
        }
      }
    }
  };

  $scope.framesChange = function (amount) {
    if ($scope.hive.layers == undefined || $scope.hive.layers.length == 0) return;
    f = angular.copy($scope.hive.layers[0].frames);

    for (var i = 0; i < $scope.hive.layers.length; i++) {
      frames = $scope.hive.layers[i].frames;

      if (amount > 0) {
        frames.push(f);
      } else if (frames.length > 1) {
        frames.pop();
      }
    }

    $scope.hive = hives.calculateHiveWidth($scope.hive);
  };

  $scope.saveHive = function (back) {
    $scope.redirect = "/locations";

    if ($location.path().indexOf('/hives/create') > -1) {
      api.postApiRequest('saveHive', 'hives', $scope.hive);
    } else {
      api.patchApiRequest('saveHive', 'hives/' + $scope.hive.id, $scope.hive);
    }
  };

  $scope.deleteHive = function () {
    $scope.redirect = "/locations";
    api.deleteApiRequest('deleteHive', 'hives/' + $scope.hive.id, $scope.hive);
  };

  $scope.confirmDeleteHive = function () {
    $rootScope.showConfirm($rootScope.lang.remove_hive + '?', $scope.deleteHive);
  };

  $scope.hivesError = function (type, error) {
    if (typeof error.errors != 'undefined') $scope.error_msg = $rootScope.lang.empty_fields + (error.status == 422 ? ". Error: " + convertOjectToArray(error.errors).join(', ') : '');else $scope.error_msg = $rootScope.lang.empty_fields + (error.status == 422 ? ". Error: " + convertOjectToArray(error.message).join(', ') : '');
  };

  $scope.hiveChanged = function () {
    if ($scope.redirect != null) {
      $scope.redirect = null;
      $scope.back();
    }
  };

  $scope.hivesDeleteError = $rootScope.$on('deleteHiveError', $scope.hivesError);
  $scope.hivesSaveError = $rootScope.$on('saveHiveError', $scope.hivesError);
  $scope.hivesDeleteHandler = $rootScope.$on('deleteHiveLoaded', $scope.hiveChanged);
  $scope.hivesSaveHandler = $rootScope.$on('saveHiveLoaded', $scope.hiveChanged);
  $scope.hivesHandler = $rootScope.$on('hivesUpdated', $scope.hivesUpdate);
  $scope.hivesErrorHandler = $rootScope.$on('hivesError', $scope.hivesError);

  $scope.back = function () {
    if ($rootScope.optionsDialog) {
      $rootScope.optionsDialog.close();
    } else {
      for (var i = $rootScope.history.length - 1; i >= 0; i--) {
        var path = $rootScope.history[i];
        var go = false;
        var hive_id = typeof $scope.hive != 'undefined' && $scope.hive != null ? $scope.hive.id : '';
        if (path.indexOf('/locations') > -1 || path.indexOf('/hives') > -1 && path.indexOf('/hives/' + hive_id) == -1 && path.indexOf('/hives/create') == -1 || path.indexOf('/groups') > -1) go = true;

        if (go) {
          //console.log('hiveChanged', $scope.redirect, $rootScope.history, path);
          return $location.path(path);
        }
      }

      $rootScope.historyBack();
    }
  }; //close options dialog


  $scope.backListener = $rootScope.$on('backbutton', $scope.back); // remove references to the controller

  $scope.removeListeners = function () {
    $scope.taxonomyHandler();
    $scope.hivesDeleteError();
    $scope.hivesSaveError();
    $scope.hivesDeleteHandler();
    $scope.hivesSaveHandler();
    $scope.hivesHandler();
    $scope.hivesErrorHandler();
    $scope.backListener();
  };

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  }); // call the init function

  $scope.init();
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Password recovery controller
 */

app.controller('PasswordCtrl', function ($scope, $rootScope, $window, $location, $routeParams, api) {
  // set the title
  $rootScope.title = $rootScope.lang.password_recovery_title;
  $scope.formStatus = '';
  $scope.message = null;
  $scope.error = null;
  $scope.fields = {};

  $scope.resetErrors = function () {
    $scope.formStatus = '';
    $scope.message = {
      show: false,
      resultType: 'error',
      resultMessage: ''
    };
    $scope.error = {
      email: false,
      password: false,
      password_confirm: false,
      token: false
    };
  };

  $scope.resetErrors(); // reminder

  $scope.fields.reminder = {
    email: ''
  }; // reset

  $scope.fields.reset = {
    email: '',
    password: '',
    password_confirm: '',
    token: ''
  };

  $scope.init = function () {
    // copy the emailadres from the reminder
    if (_typeof(api.cache.email) != undefined) $scope.fields.reset.email = api.cache.email; // hide splash

    $rootScope.showSplash = false; // Set reset token

    if ($routeParams.token != undefined) {
      $scope.fields.reset.token = $routeParams.token;
    }

    if ($routeParams.email != undefined && $routeParams.email != '') {
      $scope.fields.reminder.email = $routeParams.email;
      $scope.fields.reset.email = $routeParams.email;
    } // check if we're authenticated


    if (api.getApiToken() != null) {
      $location.path('/load');
    }
  };

  $scope.sendReminder = function (e) {
    e.preventDefault();
    $scope.resetErrors(); // check if errors

    var validate = $rootScope.validateFields($scope.fields.reminder, $scope.reminder, $scope.error);

    if (validate === true) {
      // data
      var input = $scope.fields.reminder; // do the call

      api.passwordReminder(input.email);
    } else {
      $scope.message = validate;
    }
  };

  $scope.doReset = function (e) {
    e.preventDefault();
    $scope.resetErrors(); // check if errors

    var validate = $rootScope.validateFields($scope.fields.reset, $scope.reset, $scope.error);

    if (validate === true) {
      // data
      var input = $scope.fields.reset; // do the call

      api.passwordReset(input.email, input.password, input.password_confirm, input.token);
    } else {
      $scope.message = validate;
    }
  };

  $scope.responseSuccess = function (e, data) {
    if (typeof data.message != 'undefined') {
      var _msg = data.message;

      switch (_msg) {
        case 'reminder_sent':
          $scope.formStatus = 'reminder_sent';
          break;
      }
    } else {
      var result = data.data;
      if (result.api_token != null) api.setApiToken(result.api_token); // redirect to the main page

      $scope.formStatus = 'password_reset';
    }
  };

  $scope.responseError = function (e, err) {
    var message = null;
    var error = $scope.error;

    switch (err.message) {
      case 'invalid_user':
        error.email = true;
        message = $rootScope.lang.invalid_user;
        break;

      case 'invalid_password':
        error.password = true;
        message = $rootScope.lang.invalid_password;
        break;

      case 'invalid_token':
        error.token = true;
        message = $rootScope.lang.invalid_token;
        break;

      default:
        message = $rootScope.lang.server_error + ": " + err.status;
        break;
    } // check for errors


    if (message != null) {
      $scope.message = {
        show: true,
        resultType: 'error',
        resultMessage: message
      };
    }
  };

  $scope.reminderHandler = $rootScope.$on('passwordReminderLoaded', $scope.responseSuccess);
  $scope.reminderErrorHandler = $rootScope.$on('passwordReminderError', $scope.responseError);
  $scope.resetHandler = $rootScope.$on('passwordResetLoaded', $scope.responseSuccess);
  $scope.resetErrorHandler = $rootScope.$on('passwordResetError', $scope.responseError);

  $scope.back = function () {
    $location.path('/login');
  };

  $scope.init(); // remove the listeners

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  }); // remove listeners

  $scope.removeListeners = function () {
    $scope.reminderHandler();
    $scope.reminderErrorHandler();
    $scope.resetHandler();
    $scope.resetErrorHandler();
  };
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */

app.controller('InspectionCreateCtrl', function ($scope, $rootScope, $window, $location, $filter, $routeParams, $timeout, settings, api, moment, hives, groups, inspections) {
  $rootScope.title = $rootScope.lang.Inspections;
  $scope.showMore = false; // multiple inspections

  $scope.checklist = null;
  $scope.checklists = null;
  $scope.checklist_id = null;
  $scope.inspection = {};
  $scope.hive = null;
  $scope.hives = null;
  $scope.location = null;
  $scope.locations = null;
  $scope.beeraces = null;
  $scope.hivetypes = null;
  $scope.langScript = $rootScope.lang.pick_a_date_lang_file;

  $scope.init = function () {
    if (api.getApiToken() == null) {
      $location.path('/login');
    } else {
      // console.log('checklist', inspections.checklist);
      // console.log('checklists', inspections.checklists);
      $scope.setDateLanguage();
      $rootScope.beeraces = settings.beeraces;
      $rootScope.hivetypes = settings.hivetypes;
      $rootScope.hives = hives.hives;
      $rootScope.locations = hives.locations;
      $scope.hive = hives.getHiveById($routeParams.hiveId);
      if ($scope.hive == null) $scope.hive = groups.getHiveById($routeParams.hiveId);
      $scope.inspection = inspections.newSaveObject();
      $scope.checklistsUpdated();
      $scope.checklist = inspections.checklist;
      $scope.updateLists(false);
      $scope.showMore = hives.hives.length > 1 ? true : false;

      if ($routeParams.inspectionId) {
        $scope.inspection_id = $routeParams.inspectionId;
        inspections.loadRemoteInspection($routeParams.inspectionId);
      } //console.log('init-inspection', $scope.inspection);

    }
  };

  $scope.setDateLanguage = function () {
    $("#dtBox").DateTimePicker({
      dateTimeFormat: 'yyyy-MM-dd HH:mm',
      // ISO formatted date
      language: $rootScope.locale,
      mode: 'datetime',
      formatHumanDate: function formatHumanDate(dateObj, mode, format) {
        var output = '';
        output += dateObj.day + ' ';
        output += parseInt(dateObj.dd) + ' ';
        output += dateObj.month + ' ';
        output += dateObj.yyyy + ', ';
        output += dateObj.HH + ':';
        output += dateObj.mm + ' ';
        return output;
      },
      afterShow: function afterShow(inputElement) {
        $("#dtBox .dtpicker-compValue").attr('type', 'tel'); // set monbile input keyboard to numeric
      }
    });
  };

  $rootScope.changeChecklistItem = function (type, id, value, items) {
    //console.log(type, id, value, items);
    inspections.createInspectionObject(type, id, value, items);
  };

  $scope.renderSliders = function () {
    $timeout(function () {
      console.log('rzSliderForceRender');
      $scope.$broadcast('rzSliderForceRender');
    }, 100);
  };

  $scope.saveInspection = function () {
    var data = inspections.validateChecklist(); // set general items

    data.date = $scope.inspection.date;
    data.impression = $scope.inspection.impression;
    data.attention = $scope.inspection.attention;
    data.notes = $scope.inspection.notes;
    data.reminder_date = $scope.inspection.reminder_date;
    data.reminder = $scope.inspection.reminder;
    data.hive_id = $routeParams.hiveId;
    console.log("saveInspection", data);

    if (data.valid === false) {
      var msg = '\'' + data.unfilled.join('\', \'') + '\' ' + $rootScope.lang['not_filled'];
      $scope.showError(null, {
        message: msg
      });
    } else if (data != null) {
      api.postApiRequest('saveInspection', 'inspections/store', data);
    }
  };

  $scope.showError = function (type, error) {
    var msg = typeof error.status !== 'undefined' ? "Status: " + error.status : typeof error.message !== 'undefined' ? error.message : '';
    $scope.error_msg = $rootScope.lang.empty_fields + " " + msg;
  };

  $scope.saveAndeditChecklist = function () {
    //console.log('saveAndeditChecklist');
    $scope.saveInspectionHandler = $rootScope.$on('saveInspectionLoaded', $scope.navigateToEditChecklist);
    $scope.saveInspection();
  };

  $scope.navigateToEditChecklist = function (type, data) {
    //console.log('navigateToEditChecklist', data);
    var inspection_id = data ? data : $scope.inspection_id;
    $location.path('/checklist/' + $scope.checklist.id + '/edit').search({
      hive_id: $routeParams.hiveId,
      inspection_edit: inspection_id
    });
  };

  $scope.editChecklist = function () {
    var so = inspections.saveObject; //console.log('editChecklist', so);

    if (so && (Object.keys(so.items).length > 0 || so.impression != -1 || so.attention != -1 || so.notes != '' || so.reminder != '' || so.reminder_date != '')) {
      $rootScope.showConfirm($rootScope.lang.save_input_first, $scope.saveAndeditChecklist, null, $scope.navigateToEditChecklist);
    } else {
      $scope.navigateToEditChecklist();
    }
  };

  $scope.refreshAndGoHome = function () {
    $location.path('/hives/' + $routeParams.hiveId + '/inspections');
  };

  $scope.saveInspectionHandler = $rootScope.$on('saveInspectionLoaded', $scope.refreshAndGoHome);
  $scope.saveInspectionErrorHandler = $rootScope.$on('saveInspectionError', $scope.showError);

  $scope.updateLists = function () {
    var force = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
    var id = $scope.checklist ? $scope.checklist.id : null;

    if (inspections.checklist == null || force) {
      $scope.setDateLanguage();
      $scope.selectChecklist(id, force); //console.log('selected checklist id NULL', id, force);
    } else {
      //console.log('selected checklist id NOT NULL', id, force);
      $scope.checklistUpdated(null, null);
    }
  };

  $scope.checklistUpdated = function (e, type) {
    $scope.checklist = inspections.checklist;
    var id = $scope.checklist ? $scope.checklist.id : null;

    if (id != null) {
      $scope.checklist_id = id;
      $scope.checklists = null;
      $scope.checklists = inspections.checklists; //console.log('checklistUpdated id', id, $scope.checklists);
    }

    if (typeof e != 'undefined' && e != null && typeof e.name != 'undefined' && e.name == 'localeChange') $scope.updateLists(true);
  };

  $scope.checklistHandler = $rootScope.$on('checklistUpdated', $scope.checklistUpdated);
  $scope.localeChangeHandler = $rootScope.$on('localeChange', $scope.checklistUpdated);

  $scope.checklistsUpdated = function (e, type) {
    $scope.checklists = inspections.checklists;
  };

  $scope.checklistsHandler = $rootScope.$on('checklistsUpdated', $scope.checklistsUpdated);

  $scope.selectChecklist = function (id) {
    var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

    if ($scope.checklist && id == $scope.checklist.id && force == false) {
      console.log('DO NOT selectChecklist', id, force);
      return;
    }

    $scope.checklist_id = id;
    inspections.loadChecklist(id);
  };

  $scope.inspectionUpdate = function (e, data) {
    $scope.inspection = inspections.newSaveObject(data);
  };

  $scope.inspectionHandler = $rootScope.$on('inspectionUpdated', $scope.inspectionUpdate);

  $scope.loadHiveIndex = function (direction) {
    var i = hives.getHiveIndex($routeParams.hiveId); //console.log('inspection_create loadedHiveIndex:', i);

    var max = hives.hives.length - 1;

    if (i < max && direction > 0) {
      $scope.hive = hives.hives[i + 1];
    } else if (i > 0 && direction < 0) {
      $scope.hive = hives.hives[i - 1];
    } else {
      if (direction > 0) {
        $scope.hive = hives.hives[0];
      } else {
        $scope.hive = hives.hives[max];
      }
    }

    $location.path('/hives/' + $scope.hive.id + '/inspect'); //inspections.loadRemoteInspections($scope.hive.id);
  };

  $scope.prevHive = function (e) {
    $scope.loadHiveIndex(-1);
  };

  $scope.nextHive = function (e) {
    $scope.loadHiveIndex(1);
  };

  $scope.back = function () {
    if ($rootScope.optionsDialog) {
      $rootScope.optionsDialog.close();
    } else {
      // make sure that back goes to the last main screen in the history
      for (var i = $rootScope.history.length - 1; i >= 0; i--) {
        var path = $rootScope.history[i];
        var go = false;
        var hive_id = typeof $scope.hive != 'undefined' && $scope.hive != null ? $scope.hive.id : '';
        if (path.indexOf('/inspections') > -1 && path.indexOf('/inspections/') == -1 || path.indexOf('/locations') > -1 || path.indexOf('/hives') > -1 && path.indexOf('/hives/' + hive_id) == -1 || path.indexOf('/groups') > -1) go = true;
        if (go) return $location.path(path);
      }

      $rootScope.historyBack();
    }
  }; //close options dialog


  $scope.backListener = $rootScope.$on('backbutton', $scope.back); // remove references to the controller

  $scope.removeListeners = function () {
    $scope.saveInspectionHandler();
    $scope.saveInspectionErrorHandler();
    $scope.checklistHandler();
    $scope.checklistsHandler();
    $scope.localeChangeHandler();
    $scope.inspectionHandler();
    $scope.backListener();
  };

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  }); // call the init function

  $scope.init();
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */

app.controller('InspectionsCtrl', function ($scope, $rootScope, $window, $location, $filter, $routeParams, settings, api, moment, hives, inspections, groups) {
  $rootScope.title = $rootScope.lang.Inspections;
  $scope.showMore = false; // multiple inspections

  $scope.inspections = null;
  $scope.items_by_date = null;
  $scope.inspection = null;
  $scope.location = null;
  $scope.hive = null;
  $scope.hiveId = null;
  $scope.selectedInspectionIndex = 0;

  $scope.setScales = function () {
    $scope.gradeColor = function (value) {
      if (value == 0) return '#CCC';
      if (value < 4) return '#8F1619';
      if (value < 6) return '#5F3F90';
      if (value < 8) return '#243D80';
      if (value < 11) return '#069518';
      return '#F29100';
    };

    $scope.scoreQualityOptions = {
      1: $rootScope.lang.Poor,
      2: $rootScope.lang.Fair,
      3: $rootScope.lang.Good,
      4: $rootScope.lang.Excellent
    };

    $scope.qualityColor = function (value) {
      if (value == 0) return '#CCC';
      if (value == 1) return '#8F1619';
      if (value == 2) return '#5F3F90';
      if (value == 3) return '#243D80';
      if (value == 4) return '#069518';
      return '#F29100';
    };

    $scope.scoreAmountOptions = {
      1: $rootScope.lang.Low,
      2: $rootScope.lang.Medium,
      3: $rootScope.lang.High,
      4: $rootScope.lang.Extreme
    };

    $scope.amountColor = function (value) {
      if (value == 0) return '#CCC';
      if (value == 1) return '#069518';
      if (value == 2) return '#243D80';
      if (value == 3) return '#5F3F90';
      if (value == 4) return '#8F1619';
      return '#F29100';
    };
  };

  $scope.init = function () {
    if (api.getApiToken() == null) {
      $location.path('/login');
    } else {
      $scope.hiveId = $routeParams.hiveId;
      $scope.hive = hives.getHiveById($scope.hiveId);
      if ($scope.hive == null) $scope.hive = groups.getHiveById($scope.hiveId);
      $scope.showMore = hives.hives_inspected.length > 1 ? true : false;
      $scope.setScales();
      $scope.loadInspections();
      console.log($scope.hive);
    }
  };

  $scope.localeChange = function (e) {
    $scope.setScales();
    $scope.loadInspections(e);
  };

  $scope.loadInspections = function (e) {
    inspections.loadRemoteInspections($scope.hiveId);
  };

  $scope.rounddec = function (v, d) {
    return round_dec(v, d).toString();
  };

  $scope.parseBool = function (v) {
    //console.log(name, v);
    return parseInt(v);
  };

  $scope.inspectionsUpdate = function (e, type) {
    $scope.inspections = inspections.inspections.inspections;
    $scope.items_by_date = inspections.inspections.items_by_date;

    if ($scope.inspections && $scope.inspections.length > 0) {
      console.log('Inspections have ' + $scope.inspections.length + ' dates');
    } else {//$location.path('/hives/'+$routeParams.hiveId+'/inspect'); // create first inspection
    }
  };

  $scope.inspectionsError = function () {
    $scope.conditions = null;
    $scope.actions = null;
  };

  function deleteInspection(id) {
    if (id) api.deleteApiRequest('deleteInspection', 'inspections/' + id);
  }

  $scope.confirmDeleteInspection = function (id) {
    if (id) $rootScope.showConfirm($rootScope.lang.remove_inspection + '?', deleteInspection, id);
  };

  $scope.inspectionsDeleteHandler = $rootScope.$on('deleteInspectionLoaded', $scope.loadInspections);
  $scope.inspectionsHandler = $rootScope.$on('inspectionsUpdated', $scope.inspectionsUpdate);
  $scope.inspectionsErrorHandler = $rootScope.$on('inspectionsError', $scope.inspectionsError);
  $scope.localeChangeHandler = $rootScope.$on('localeChange', $scope.localeChange);

  $scope.getHiveName = function (id) {
    console.log('getHiveName', id);
    var name = hives.getHiveNameById(id);
    return name != null ? name : $rootScope.lang.Hive + ' id: ' + id;
  };

  $scope.getApiaryName = function (id) {
    var loc = hives.getHiveLocationById(id);
    return loc != null ? loc.name : '';
  };

  $scope.loadHiveIndex = function (direction) {
    var i = hives.getHiveInspectedIndex($routeParams.hiveId);
    console.log('inspections loadedHiveIndex:', i);
    var max = hives.hives_inspected.length - 1;

    if (i < max && direction > 0) {
      $scope.hive = hives.hives_inspected[i + 1];
    } else if (i > 0 && direction < 0) {
      $scope.hive = hives.hives_inspected[i - 1];
    } else {
      if (direction > 0) {
        $scope.hive = hives.hives_inspected[0];
      } else {
        $scope.hive = hives.hives_inspected[max];
      }
    }

    $location.path('/hives/' + $scope.hive.id + '/inspections'); // create first inspection
  };

  $scope.prevHive = function (e) {
    $scope.loadHiveIndex(-1);
  };

  $scope.nextHive = function (e) {
    $scope.loadHiveIndex(1);
  };

  $scope.back = function () {
    if ($rootScope.optionsDialog) {
      $rootScope.optionsDialog.close();
    } else {
      for (var i = $rootScope.history.length - 1; i >= 0; i--) // make sure that back goes to the previous main screen
      {
        var path = $rootScope.history[i];
        var go = false;
        var hive_id = typeof $scope.hive != 'undefined' && $scope.hive != null ? $scope.hive.id : '';
        if (path.indexOf('/locations') > -1 || path.indexOf('/hives') > -1 && path.indexOf('/hives/') == -1 || path.indexOf('/groups') > -1) go = true;
        if (go) return $location.path(path);
      }

      $location.path('/locations');
    }
  }; //close options dialog


  $scope.backListener = $rootScope.$on('backbutton', $scope.back); // remove references to the controller

  $scope.removeListeners = function () {
    $scope.inspectionsHandler();
    $scope.inspectionsErrorHandler();
    $scope.localeChangeHandler();
    $scope.backListener();
  };

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  }); // call the init function

  $scope.init();
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */

app.controller('ChecklistCtrl', function ($scope, $rootScope, $window, $location, $filter, $routeParams, settings, api, moment, hives, inspections) {
  $rootScope.title = $rootScope.lang.Checklist;
  $scope.showMore = false; // multiple inspections

  $scope.inspections = null;
  $scope.checklist = null;
  $scope.checklists = null;
  $scope.checklist_id = null;
  $scope.hive = null;
  $scope.location = null;
  $scope.treeData = [];
  $scope.search = "";

  $scope.ac = function () {
    return false;
  };

  function treeError(error) {
    console.log('JsTree: error from js tree - ' + angular.toJson(error));
  }

  ;

  function readyCB() {//console.log('JsTree ready called');
  }

  ;

  function deselectNodeCB(node, selected, event) {
    if (typeof $scope.checklist.required_ids != 'undefined' && $scope.checklist.required_ids.length > 0 && $scope.checklist.required_ids.indexOf(parseInt(selected.node.id)) > -1) {
      console.log('Do not deselectNodeCB', selected.node.id);
      $scope.treeInstance.jstree(true).select_node(selected.node);
      $rootScope.showMessage($rootScope.lang.cannot_deselect);
      return false;
    }

    $scope.treeInstance.jstree(true).deselect_node(selected.node.children_d);
  }

  ;

  function selectNodeCB(node, selected, event) {
    $scope.treeInstance.jstree(true).select_node(selected.node.children_d);
  }

  ;

  function checkCallback(operation, node, node_parent, node_position, more) {
    // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
    // in case of 'rename_node' node_position is filled with the new node name
    if (operation === "move_node") {
      if (node.parent === "#") // root item is dragged
        return node_parent.id === "#"; // and dropped on a root node
      else return node_parent.id === node.parent;
    }

    return false; //allow all other operations
  }

  function checkDraggable(nodes, e) {
    var drag = nodes[0].parents.length > 2 ? false : true;
    return drag;
  }

  $scope.applySearch = function () {
    var to = false;

    if (to) {
      clearTimeout(to);
    }

    to = setTimeout(function () {
      if ($scope.treeInstance) {
        $scope.treeInstance.jstree(true).search($scope.search);
      }
    }, 250);
  };

  $scope.treeConfig = {
    "core": {
      "check_callback": checkCallback,
      "error": treeError,
      "themes": {
        "variant": "large",
        "stripes": true
      }
    },
    "plugins": ["search", "checkbox", "dnd"],
    "checkbox": {
      "cascade": "undetermined",
      "three_state": false,
      "cascade_to_hidden": true,
      "keep_selected_style": true
    },
    "dnd": {
      "check_while_dragging": true,
      "drag_selection": false,
      "touch": true,
      "copy": false,
      "use_html5": false
    },
    "version": 1
  };
  $scope.treeEventsObj = {
    'ready': readyCB,
    'select_node': selectNodeCB,
    'deselect_node': deselectNodeCB // sorting categories

  };

  $scope.init = function () {
    if (api.getApiToken() == null) {
      $location.path('/login');
    } else {
      $scope.checklistsUpdated(); // only show name if multiple checklists

      if ($routeParams.checklistId == $scope.checklist_id) $scope.updateLists();else $scope.selectChecklist($routeParams.checklistId);
      console.log('checklist.js');
    }
  };

  $scope.saveChecklist = function () {
    console.log("saveChecklist");
    var check = {
      'name': $scope.checklist.name
    };
    var tree = $scope.treeInstance.jstree(false).get_json(null, {
      "no_icon": true,
      "no_id": true,
      "no_data": true,
      "no_li_attr": true,
      "no_a_attr": true,
      "flat": true
    });
    var cats = [];
    Object.entries(tree).forEach(function (_ref) {
      var _ref2 = _slicedToArray(_ref, 2),
          i = _ref2[0],
          item = _ref2[1];

      if (item.state.selected && typeof item.state.cat != 'undefined') cats.push(item.state.cat);
    });

    if (cats.length > 0) {
      check.categories = cats.join(',');
      console.log(check);
      if ($scope.checklist_id == null) api.postApiRequest('saveChecklist', 'checklists', check);else api.patchApiRequest('saveChecklist', 'checklists/' + $scope.checklist.id, check);
    } else {
      $scope.showError(null, {
        'status': 'No items selected'
      });
    }
  };

  $scope.showError = function (type, error) {
    $scope.error_msg = $rootScope.lang.empty_fields + ". Status: " + error.status;
  };

  $scope.refreshAndGoHome = function () {
    inspections.loadChecklist($scope.checklist.id);
    $scope.back();
  };

  $scope.saveChecklistHandler = $rootScope.$on('saveChecklistLoaded', $scope.refreshAndGoHome);
  $scope.saveChecklistErrorHandler = $rootScope.$on('saveChecklistError', $scope.showError);

  $scope.updateLists = function () {
    var force = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

    if (inspections.checklistTree == null || force) {
      var id = $scope.checklist ? $scope.checklist.id : null;
      $scope.selectChecklist(id, force);
    } else {
      $scope.checklistUpdated(null, null);
    }
  };

  $scope.checklistUpdated = function (e, type) {
    $scope.checklist = inspections.checklistTree;
    $scope.treeData = $scope.checklist.taxonomy;
    if ($scope.checklist) $scope.treeConfig.version++;
    if (typeof e != 'undefined' && e != null && typeof e.name != 'undefined' && e.name == 'localeChange') $scope.updateLists(true);
  };

  $scope.checklistHandler = $rootScope.$on('checklistTreeUpdated', $scope.checklistUpdated);
  $scope.localeChangeHandler = $rootScope.$on('localeChange', $scope.checklistUpdated);

  $scope.checklistsUpdated = function (e, type) {
    $scope.checklists = inspections.checklists;
  };

  $scope.checklistsHandler = $rootScope.$on('checklistsUpdated', $scope.checklistsUpdated);

  $scope.selectChecklist = function (id) {
    var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    if ($scope.checklist && id == $scope.checklist.id && force == false) return;
    console.log('selectChecklist', id);

    if ($routeParams.checklistId != id) {
      $location.path('/checklist/' + id + '/edit');
    } else {
      $scope.checklist_id = id;
      inspections.loadChecklistTree(id);
    }
  };

  $scope.back = function () {
    if ($rootScope.optionsDialog) {
      $rootScope.optionsDialog.close();
    } else if ($routeParams.inspection_edit && $routeParams.hive_id) {
      $location.replace();
      $location.path('/hives/' + $routeParams.hive_id + '/inspections/' + $routeParams.inspection_edit).search({});
    } else {
      $rootScope.historyBack();
    }
  }; //close options dialog


  $scope.backListener = $rootScope.$on('backbutton', $scope.back); // remove references to the controller

  $scope.removeListeners = function () {
    $scope.saveChecklistHandler();
    $scope.saveChecklistErrorHandler();
    $scope.checklistHandler();
    $scope.checklistsHandler();
    $scope.localeChangeHandler();
    $scope.backListener();
  };

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  }); // call the init function

  $scope.init();
});
/*
 * Kweecker iPad app
 * Author: Neat projects <pim@expertees.nl>
 *
 * Measurements controller for chart measurements 
 */

app.controller('MeasurementsCtrl', function ($scope, $rootScope, $timeout, $interval, $routeParams, $location, api, moment, measurements) {
  $rootScope.title = $rootScope.lang.sensors;
  $scope.periods = ['hour', 'day', 'week', 'month', 'year'];
  $scope.timeZone = 'Europe/Amsterdam';
  $scope.periodIndex = 0;
  $scope.activePeriod = 'day';
  $scope.activeUnit = 'hour';
  $scope.parseFormat = 'YYYY-MM-DD[T]HH:mm:ssZ';
  $scope.chartParseFmt = 'YYYY-MM-DD[T]HH:mm:ssZ';
  $scope.timeFormat = 'ddd D MMM YYYY';
  $scope.tooltTimeFrmt = 'ddd D MMM YYYY';
  $scope.displayFormats = {
    "year": 'YYYY MMM D',
    "month": 'YYYY MMM D',
    "quarter": 'YYYYY MMM D',
    "week": '[w]W',
    "day": 'D MMM',
    "hour": 'ddd H[u]',
    "minute": 'HH:mm',
    "second": 'HH:mm:ss',
    "millisecond": 'HH:mm:ss'
  };
  $scope.showChart = false; // $scope.showActuators= true;

  $scope.chartTitle = null;
  $scope.fontSize = 15;
  $scope.fontSizeMob = 10;
  $scope.startTime = null;
  $scope.endTime = null;
  $scope.sensors = [];
  $scope.selectedSensor = null;
  $scope.selectedSensorId = null;
  $scope.sensorMin = SENSOR_MIN;
  $scope.sensorLow = SENSOR_LOW;
  $scope.sensorHigh = SENSOR_HIGH;
  $scope.sensorMax = SENSOR_MAX;
  $scope.sensorUnits = SENSOR_UNITS;
  $scope.allinone = false;
  $scope.measurementData = null;
  $scope.chart = {};
  $scope.chartLegend = {
    display: true,
    position: 'top',
    labels: {
      usePointStyle: true,
      fontSize: $rootScope.mobile ? $scope.fontSizeMob : $scope.fontSize,
      boxWidth: $rootScope.mobile ? $scope.fontSizeMob : $scope.fontSize + 2,
      padding: $rootScope.mobile ? 6 : 10,
      fullWidth: $rootScope.mobile ? false : true // generateLabels: function(chart) 
      // {
      //     console.log('generateLabels');
      //     //console.log(chart.data);
      //     var text = [];
      //     for (var i=0; i<chart.data.datasets.length; i++) 
      //     {
      //         var ds = chart.data.datasets[i];
      //         text.push({text:ds.label, pointStyle:'pointStyleMad'});
      //     }
      //     console.log(text);
      //     return text;
      // },

    }
  };
  $scope.chartScales = {
    xAxes: [{
      display: true,
      position: "bottom",
      ticks: {
        autoSkip: true,
        maxRotation: 0,
        minRotation: 0,
        fontSize: $rootScope.mobile ? $scope.fontSizeMob : $scope.fontSize
      },
      type: "time",
      time: {
        round: false,
        parser: $scope.chartParseFmt,
        tooltipFormat: $scope.timeFormat,
        displayFormats: $scope.displayFormats
      }
    }],
    yAxes: [{
      ticks: {
        fontSize: $rootScope.mobile ? $scope.fontSizeMob : $scope.fontSize
      },
      display: false,
      position: "left"
    }]
  };
  $scope.chart.optionsSensors = {
    legend: angular.copy($scope.chartLegend),
    scales: angular.copy($scope.chartScales),
    elements: {
      point: {
        radius: $rootScope.mobile ? 0.5 : 1,
        borderWidth: $rootScope.mobile ? 0 : 2,
        pointHoverBorderWidth: 2,
        pointBorderColor: 'rgba(255,255,255,0)'
      },
      line: {
        borderWidth: $rootScope.mobile ? 2 : 3
      }
    },
    tooltips: {
      mode: 'nearest',
      intersect: true,
      bodySpacing: 5,
      xPadding: 10,
      yPadding: 10,
      displayColors: false,
      callbacks: {
        // title: function(tooltipItem, data) 
        // {
        //     var date = tooltipItem[0].xLabel; // .substr(0, 19)
        //     console.log(date, $scope.tooltTimeFrmt);
        //     return moment(date, $scope.chartParseFmt).format($scope.tooltTimeFrmt);
        // },
        label: function label(tooltipItem, data) {
          var name = data.datasets[tooltipItem.datasetIndex].name;
          var unit = data.datasets[tooltipItem.datasetIndex].unit;
          return name + ': ' + round_dec(tooltipItem.yLabel, 1) + ' ' + unit;
        }
      }
    },
    animation: {
      onComplete: function onComplete() {
        var ctx = this.chart.ctx;
        ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle'; //console.log(this.data.datasets);

        this.data.datasets.forEach(function (dataset) {
          for (var i = 0; i < dataset.data.length; i++) {
            var point = dataset.data[i];

            if (typeof point != 'undefined' && point.y != null && (i == dataset.data.length - 1 || dataset.data[i + 1].y == null)) // last point with value
              {
                var text = Math.round(point.y * 10) / 10 + (dataset.unit != '' ? ' ' + dataset.unit : '');
                var textWidth = ctx.measureText(text).width;

                for (var key in dataset._meta) {
                  var model = dataset._meta[key].data[i]._model;
                  ctx.fillStyle = 'rgba(255,255,255,0.7)';
                  ctx.fillRect(model.x + 3, model.y - 8, textWidth + 4, 15);
                  ctx.fillStyle = "black";
                  ctx.fillText(text, model.x + 5, model.y);
                }
              }
          }
        });
      }
    }
  };
  $scope.chart.optionsActuators = {
    legend: angular.copy($scope.chartLegend),
    scales: angular.copy($scope.chartScales),
    elements: {
      point: {
        radius: $rootScope.mobile ? 0.5 : 2,
        borderWidth: $rootScope.mobile ? 2 : 4,
        pointHoverBorderWidth: $rootScope.mobile ? 0 : 2,
        pointBorderColor: 'rgba(255,255,255,0)'
      },
      line: {
        borderWidth: $rootScope.mobile ? 3 : 8
      }
    },
    tooltips: {
      mode: 'nearest',
      intersect: true,
      bodySpacing: 5,
      xPadding: 10,
      yPadding: 10,
      displayColors: false,
      callbacks: {
        // title: function(tooltipItem, data) 
        // {
        //     var date = tooltipItem[0].xLabel;
        //     console.log(date, $scope.tooltTimeFrmt);
        //     return moment(date, $scope.chartParseFmt).format($scope.tooltTimeFrmt);
        // },
        label: function label(tooltipItem, data) {
          var name = data.datasets[tooltipItem.datasetIndex].name;
          var unit = data.datasets[tooltipItem.datasetIndex].unit;
          return name + ': ' + $rootScope.lang['on'];
        }
      } // tooltips: 
      // {
      //     enabled: false,
      // }

    }
  };
  $scope.chart.optionsSound = angular.copy($scope.chart.optionsSensors);
  $scope.chart.optionsDebug = angular.copy($scope.chart.optionsSensors);
  $scope.chart.optionsActuators.legend.position = 'bottom';

  $scope.getSensorName = function (item) {
    return $rootScope.lang[item.name];
  }; // handle loading of all the settings


  $scope.init = function () {
    if ($rootScope.pageSlug == 'measurements') {
      if ($routeParams.sensorId != undefined) measurements.sensorId = $routeParams.sensorId;
      $scope.setDateLanguage();
      $scope.updateSensors();
      $scope.loadData();
    }
  };

  $scope.dateFormat = 'yyyy-MM-dd';
  $scope.selectedDate = '';

  $scope.setDateLanguage = function () {
    $("#dtBox").DateTimePicker({
      dateFormat: $scope.dateFormat,
      // ISO formatted date
      language: $rootScope.locale,
      mode: 'date',
      formatHumanDate: function formatHumanDate(dateObj, mode, format) {
        var output = '';
        output += dateObj.day + ' ';
        output += parseInt(dateObj.dd) + ' ';
        output += dateObj.month + ' ';
        output += dateObj.yyyy;
        return output;
      },
      afterShow: function afterShow(inputElement) {
        $("#dtBox .dtpicker-compValue").attr('type', 'tel'); // set mobile input keyboard to numeric
      }
    });
  };

  $scope.selectDate = function (selectedDate) {
    var p = $scope.activePeriod;
    var d = p + 's';
    var selectedMoment = moment(selectedDate);
    var currentMoment = moment();
    var periodeDiff = currentMoment.diff(selectedMoment, d);

    if (!isNaN(periodeDiff)) {
      $scope.periodIndex = periodeDiff;
      $scope.loadData();
    } else {
      console.log('Error selectDate: ' + selectedDate);
    }
  };

  $scope.handleLastSensorValues = function () {
    $scope.lastSensorValues = convertOjectToNameArray(measurements.lastSensorValues);
    $scope.lastSensorDate = moment(measurements.lastSensorDate).format('llll');
  };

  $scope.lastSensorValuesUpdatedHandler = $rootScope.$on('lastSensorValuesUpdated', $scope.handleLastSensorValues);
  $scope.loadLastSensorValuesTimer = null;

  $scope.loadLastSensorValues = function () {
    var activate = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
    var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    if (angular.isDefined($scope.loadLastSensorValuesTimer)) $interval.cancel($scope.loadLastSensorValuesTimer); // Start loading interval

    if (activate && $scope.selectedSensorId) {
      // load direct
      if ($scope.lastSensorValues == null || force) {
        $scope.lastSensorValues = null;
        $scope.lastSensorDate = null;
        measurements.loadLastSensorValues();
      } // set timer


      if ($scope.periodIndex == 0) {
        $scope.loadLastSensorValuesTimer = $interval(function () {
          measurements.loadLastSensorValues();
        }, 20000);
      }
    }
  };

  $scope.updateSensors = function () {
    $scope.sensors = measurements.sensors;
    $scope.selectedSensorId = measurements.sensorId;
    $scope.selectedSensor = measurements.getSensorById($scope.selectedSensorId);

    if ($scope.selectedSensorId == null && $scope.sensors.length > 0) {
      var id = $scope.sensors[$scope.sensors.length - 1].id;
      $scope.loadData(id);
    }
  };

  $scope.sensorHandler = $rootScope.$on('sensorsUpdated', $scope.updateSensors);

  $scope.loadData = function (id) {
    //console.log('sensors:', $scope.sensors);
    //$scope.selectedSensor = null;
    var sensorChanged = false;

    if (($scope.selectedSensor == null || $scope.selectedSensorId != id) && id != null && typeof id != 'undefined') {
      $scope.selectedSensorId = id;
      measurements.sensorId = id;
      $scope.selectedSensor = measurements.getSensorById(id);
      sensorChanged = true;
    }

    $scope.loadLastSensorValues($scope.selectedSensorId != null, sensorChanged);
    var period = $scope.activePeriod;
    var timeGroup = period == 'hour' ? null : period; // get all measurements
    //console.log('loadData', period, $scope.periodIndex);
    // $scope.showActuators = (period != 'year');

    $scope.setDataTitle();
    console.log('loadData id', id); //var sensorId = $scope.selectedSensorId != null ? $scope.selectedSensor['id'] : null;
    //api.getApiRequest('dataRequest', 'sensors/measurements', 'interval='+period+'&index='+$scope.periodIndex+'&timeGroup='+timeGroup+'&timezone='+$scope.timeZone);

    measurements.loadRemoteSensorMeasurements(period, $scope.periodIndex, timeGroup, $scope.timeZone, id);
  };

  $scope.setDataTitle = function () {
    var p = $scope.activePeriod;
    var pi = Math.max(1, $scope.periods.indexOf(p));
    $scope.activeUnit = $scope.periods[pi - 1]; //console.log(p, pi, $scope.activeUnit);

    var d = p + 's';
    var i = $scope.periodIndex;
    var startTimeFormat = $scope.timeFormat;
    var endTimeFormat = $scope.timeFormat;

    if (p == 'hour') {
      endTimeFormat = 'HH:mm';
      startTimeFormat += ' ' + endTimeFormat;
    } else if (p == 'day') {
      endTimeFormat = null;
    } else if (p == 'week') {
      p = 'isoweek';
    }

    var ep = p;
    var pStaTime = moment().subtract(i, d).startOf(p);
    var pEndTime = moment().subtract(i, d).endOf(ep); //console.log('selectedDate = '+$scope.selectedDate);

    var s = pStaTime.format(startTimeFormat);
    var e = pEndTime.format(endTimeFormat);
    $scope.chartTitle = s + '' + (endTimeFormat != null ? ' - ' + e : '');
    $scope.startTime = pStaTime; //.format($scope.parseFormat);

    $scope.endTime = pEndTime; //.format($scope.parseFormat);

    $scope.selectedDate = pStaTime.format($scope.dateFormat.toUpperCase()); // for moment formatting has to be uppercase
    //console.log(i, startTimeFormat, endTimeFormat, s, e, $scope.startTime.format($scope.timeFormat), $scope.endTime.format($scope.timeFormat));
  };

  $scope.handleDataResult = function (e, data) {
    if (data != null && typeof data.id != 'undefined' && ($scope.selectedSensorId == null || $scope.selectedSensorId != data.id)) $scope.selectedSensorId = data.id; //console.log(data);

    if (data != null && typeof data.interval !== 'undefined' && data.interval == $scope.activePeriod && typeof data.index !== 'undefined' && data.index == $scope.periodIndex && typeof data.measurements !== 'undefined' && data.measurements.length > 0) {
      $scope.setDataTitle(); // update start and end time

      var fontSize = $rootScope.mobile ? $scope.fontSizeMob : $scope.fontSize;
      var measurementData = data.measurements;
      var resolutionCharacter = typeof data.resolution !== 'undefined' ? data.resolution.substr(-1, 1) : null;
      var resolutionFormat = {
        'w': $scope.displayFormats['week'],
        'd': $scope.displayFormats['day'],
        'h': $scope.displayFormats['hour'],
        'm': $scope.displayFormats['minute'],
        's': $scope.displayFormats['second']
      };
      var tooltipTimeFormat = resolutionCharacter != null ? resolutionFormat[resolutionCharacter] : $scope.displayFormats[$scope.activeUnit];
      $scope.tooltTimeFrmt = tooltipTimeFormat; //console.log('Parsing '+measurementData.length+' '+data.interval+' '+data.index+' measurementData', 'resolutionCharacter: '+resolutionCharacter, 'tooltipTimeFormat: '+tooltipTimeFormat);

      $scope.measurementData = null;
      $scope.measurementData = convertInfluxMeasurementsArrayToChartObject(measurementData, $rootScope.lang, fontSize, $scope.chartParseFormat);

      if ($scope.measurementData != null) {
        //console.log($scope.measurementData);
        // Set axes
        $scope.chart.optionsSensors.scales.yAxes = typeof $scope.measurementData.sensors.yAxes != 'undefined' ? $scope.measurementData.sensors.yAxes : [];
        $scope.chart.optionsSound.scales.yAxes = typeof $scope.measurementData.sound.yAxes != 'undefined' ? $scope.measurementData.sound.yAxes : [];
        $scope.chart.optionsDebug.scales.yAxes = typeof $scope.measurementData.debug.yAxes != 'undefined' ? $scope.measurementData.debug.yAxes : [];
        $scope.chart.optionsActuators.scales.yAxes = typeof $scope.measurementData.actuators.yAxes != 'undefined' ? $scope.measurementData.actuators.yAxes : [];
        $scope.chart.optionsSensors.scales.xAxes[0].time.tooltipFormat = tooltipTimeFormat;
        $scope.chart.optionsSound.scales.xAxes[0].time.tooltipFormat = tooltipTimeFormat;
        $scope.chart.optionsDebug.scales.xAxes[0].time.tooltipFormat = tooltipTimeFormat;
        $scope.chart.optionsActuators.scales.xAxes[0].time.tooltipFormat = tooltipTimeFormat; //$scope.chart.optionsSensors.scales.xAxes[0].time.unit   = $scope.activeUnit;

        $scope.chart.optionsSensors.scales.xAxes[0].time.min = $scope.startTime;
        $scope.chart.optionsSound.scales.xAxes[0].time.min = $scope.startTime;
        $scope.chart.optionsDebug.scales.xAxes[0].time.min = $scope.startTime;
        $scope.chart.optionsSensors.scales.xAxes[0].time.max = $scope.endTime;
        $scope.chart.optionsSound.scales.xAxes[0].time.max = $scope.endTime;
        $scope.chart.optionsDebug.scales.xAxes[0].time.max = $scope.endTime; //$scope.chart.optionsActuators.scales.xAxes[0].time.unit = $scope.activeUnit;

        $scope.chart.optionsActuators.scales.xAxes[0].time.min = $scope.startTime;
        $scope.chart.optionsActuators.scales.xAxes[0].time.max = $scope.endTime; // console.log($scope.measurementData);
      }

      $scope.showChart = $scope.measurementData == null ? false : true; //$rootScope.refreshInterface();
    } else {
      console.log(data, 'MeasurementsCtrl: Empty data result for ' + $scope.activePeriod + ' ' + $scope.periodIndex);
      $scope.showChart = false; // $rootScope.refreshInterface();
    }
  };

  $scope.resultHandler = $rootScope.$on('dataRequestLoaded', $scope.handleDataResult);
  $scope.resultErrorHandler = $rootScope.$on('dataRequestError', $scope.handleDataResult);

  $scope.setPeriod = function (period) {
    $scope.activePeriod = period;
    $scope.periodIndex = 0;
    $scope.loadData();
  };

  $scope.setPeriodIndex = function (offset) {
    $scope.periodIndex += offset;
    $scope.loadData();
  };

  $scope.nativeBackbutton = function (e) {
    if (runsNative()) {
      $rootScope.goToPage('/dashboard');
    }
  };

  $scope.backListener = $rootScope.$on('backbutton', $scope.nativeBackbutton); // call the init function

  $scope.init(); // remove references to the controller

  $scope.removeListeners = function () {
    $scope.lastSensorValuesUpdatedHandler();
    $scope.sensorHandler();
    $scope.resultHandler();
    $scope.resultErrorHandler();
    $scope.backListener();
  };

  $scope.$on('$destroy', function () {
    measurements.stopLoadingMeasurements();
    $scope.loadLastSensorValues(false);
    $scope.removeListeners();
  });
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * User controller
 */

app.controller('ExportCtrl', function ($scope, $rootScope, $window, $location, $routeParams, api) {
  // set the title
  $rootScope.title = $rootScope.lang.Data_export;
  $scope.message = null;
  $scope.error = null;

  $scope.init = function () {
    // Check locale
    if ($routeParams.language != undefined && $routeParams.language != $rootScope.locale) {
      $rootScope.switchLocale($routeParams.language);
      $location.search('language', null);
    }
  };

  $scope.exportData = function () {
    api.getApiRequest('export', 'export');
  };

  $scope.back = function () {
    $location.path('/login');
  };

  $scope.backListener = $rootScope.$on('backbutton', $scope.back);
  $scope.init(); // remove the listeners

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  }); // remove listeners

  $scope.removeListeners = function () {
    $scope.backListener();
  };
});
/*
 * Kweecker iPad app
 * Author: Neat projects <pim@expertees.nl>
 *
 * Measurements controller for chart measurements 
 */

app.controller('SensorsCtrl', function ($scope, $rootScope, $timeout, $interval, $location, measurements, hives, api, settings) {
  $rootScope.title = $rootScope.lang.sensors;
  $scope.sensors = [];
  $scope.hives = [];
  $scope.sensortypes = null;
  $scope.selectedSensor = null;
  $scope.selectedSensorId = null;
  $scope.measurementData = null;
  $scope.success_msg = null;
  $scope.error_msg = null;
  $scope.sensorTimer = null; // handle loading of all the settings

  $scope.init = function () {
    if ($rootScope.pageSlug == 'sensors') {
      $scope.updateSensors();
    }
  };

  $scope.selectSensorHive = function (sensorIndex, hiveId) {
    var s = measurements.getSensorOwnedByIndex(sensorIndex);

    if (s != null) {
      s.selected_hive_id = {
        id: hiveId
      };
      s.hive_id = hiveId;
      s.hive = hives.getHiveOwnedById(hiveId); //console.log('selectSensorHive', sensorIndex, hiveId, s.hive.name);
    }
  };

  $scope.selectSensorType = function (sensorIndex, type) {
    var s = measurements.getSensorOwnedByIndex(sensorIndex);

    if (s != null) {
      s.selected_type = {
        name: type
      };
      s.type = type;
      console.log('selectSensorType', sensorIndex, type);
    }
  };

  $scope.addSensor = function () {
    var key = randomString(16);
    $scope.sensors.push({
      'name': 'Sensor ' + ($scope.sensors.length + 1),
      'key': key
    });
  };

  $scope.removeSensorByIndex = function (i) {
    return typeof $scope.sensors[i] != 'undefined' ? $scope.sensors.splice(i, 1) : null;
  };

  $scope.showMeasurements = function (sensorIndex) {
    var s = measurements.getSensorByIndex(sensorIndex);
    return $location.path('/measurements/' + s.id);
  };

  $scope.deleteSensor = function (sensorIndex) {
    var s = measurements.getSensorOwnedByIndex(sensorIndex);
    if (typeof s.id == 'undefined') return $scope.removeSensorByIndex(sensorIndex);
    if (typeof s["delete"] == 'undefined') s["delete"] = true;else s["delete"] = s["delete"] ? false : true;
  };

  $scope.saveSensors = function () {
    $scope.success_msg = null;
    $scope.error_msg = null;
    api.postApiRequest('saveSensors', 'sensors/store', $scope.sensors);
  };

  $scope.showSuccess = function (type, data) {
    $scope.success_msg = $rootScope.lang.succesfully_saved + "!";
  };

  $scope.showError = function (type, error) {
    var msg = [];

    if (_typeof(error.message) == 'object' && _typeof(error.message.errors) == 'object') {
      for (type in error.message.errors) {
        var err = error.message.errors[type].join(' ');

        if (err.indexOf('is required') > -1) {
          var typeName = type;

          switch (type) {
            case 'hive_id':
              typeName = $rootScope.lang.Hive;
              break;

            case 'key':
              typeName = $rootScope.lang.sensor_key;
              break;

            case 'type':
              typeName = $rootScope.lang.Type;
              break;
          }

          err = $rootScope.lang.the_field + ' \'' + typeName + '\' ' + $rootScope.lang.is_required + '.';
        }

        if (typeof err != 'udefined') msg.push(err);
      }
    }

    if (msg.length > 0) $scope.error_msg = msg.join(' ');else $scope.error_msg = error.message;
  };

  $scope.saveSensorsSuccessHandler = $rootScope.$on('saveSensorsLoaded', $scope.showSuccess);
  $scope.saveSensorsErrorHandler = $rootScope.$on('saveSensorsError', $scope.showError);

  $scope.updateSensors = function () {
    $scope.sensortypes = settings.sensortypes;
    $scope.sensors = measurements.sensors_owned;

    if ($scope.sensors.length == 0 && $scope.sensorTimer == null) {
      $scope.sensorTimer = $timeout(function () {
        measurements.loadRemoteSensors();
      }, 500);
      return;
    }

    $scope.hives = hives.hives_owned;

    for (var i = $scope.sensors.length - 1; i >= 0; i--) {
      var s = $scope.sensors[i];
      var h = hives.getHiveById(s.hive_id);

      if (h != null) {
        s.selected_hive_id = {
          id: h.id
        };
        s.hive = h;
      }

      s.selected_type = s.type != null ? {
        name: s.type
      } : '';
    }

    $scope.selectedSensorId = measurements.sensorId;
    $scope.selectedSensor = measurements.getSensorById($scope.selectedSensorId);
  };

  $scope.sensorHandler = $rootScope.$on('sensorsUpdated', $scope.updateSensors);
  $scope.hivesHandler = $rootScope.$on('hivesUpdated', $scope.updateSensors);

  $scope.nativeBackbutton = function (e) {
    if (runsNative()) {
      $rootScope.goToPage('/dashboard');
    }
  };

  $scope.backListener = $rootScope.$on('backbutton', $scope.nativeBackbutton); // call the init function

  $scope.init(); // remove references to the controller

  $scope.removeListeners = function () {
    $scope.saveSensorsSuccessHandler();
    $scope.saveSensorsErrorHandler();
    $scope.sensorHandler();
    $scope.hivesHandler();
    $scope.backListener();
  };

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  });
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */

app.controller('GroupsCtrl', function ($scope, $rootScope, $window, $location, $filter, $routeParams, $timeout, groups, api, moment, hives, inspections) {
  $rootScope.title = $rootScope.lang.Groups;
  $scope.pageTitle = '';
  $scope.showMore = false; // multiple groups

  $scope.redirect = null;
  $scope.hives = [];
  $scope.groups = [];
  $scope.invitations = [];
  $scope.hive = null;
  $scope.locations = null;
  $scope.error_msg = null;
  $scope.success_msg = null;
  $scope.selectedGroupIndex = 0;
  $scope.orderName = 'name';
  $scope.orderDirection = false;
  $scope.addedUser = false;
  $scope.deletedUser = false;

  $scope.init = function () {
    if (api.getApiToken() == null) {
      $location.path('/login');
    } else if ($location.path().indexOf('/groups') > -1) {
      if ($routeParams.token != undefined && $routeParams.groupId != undefined && $location.path().indexOf('/groups/') > -1 && $location.path().indexOf('/token/') > -1) {
        $rootScope.title = $rootScope.lang.Invitation_accepted;
        $scope.checkToken($routeParams.token, $routeParams.groupId);
      } else if ($routeParams.groupId != undefined || $location.path().indexOf('/groups/create') > -1) {
        if ($location.path().indexOf('/groups/create') > -1) {
          $scope.pageTitle = $rootScope.mobile ? $rootScope.lang.New + ' ' + $rootScope.lang.group : $rootScope.lang.create_new + ' ' + $rootScope.lang.group;
        }

        $scope.initGroups();
      } else {
        groups.loadRemoteGroups();
      } // show message


      if (typeof $routeParams.success != 'undefined') {
        $scope.displaySuccessMessage($routeParams.success);
      }
    }
  };

  $scope.displaySuccessMessage = function (msg) {
    if (typeof $rootScope.lang[msg] != 'undefined') msg = $rootScope.lang[msg];
    $scope.success_msg = msg; //$timeout(function(){ $location.search('success', ''); }, 5000 );
  };

  $scope.initGroups = function () {
    $scope.hivesUpdate();

    if (groups.groups.length > 0) {
      $scope.groups = groups.groups;
    } else {
      $scope.groups = [];
    }

    $scope.showMore = $scope.groups.length > 1 ? true : false;

    if (groups.invitations.length > 0) {
      $scope.invitations = groups.invitations;
    } else {
      $scope.invitations = [];
    }

    if ($location.path().indexOf('/groups/create') > -1) {
      $scope.group = {
        'creator': true,
        'name': $rootScope.lang.Group + ' ' + ($scope.groups.length + 1),
        'color': '',
        'description': '',
        'hives_selected': [],
        'hives_editable': [],
        'users': [{
          'name': $rootScope.user.name,
          'email': $rootScope.user.email,
          'admin': true,
          'creator': true,
          'invited': null
        }]
      }; //console.log($scope.group);
    } else {
      $scope.loadGroupIndex();
    }
  };

  $scope.toggleGroup = function (group) {
    groups.toggle_open_group(group.id);
  };

  $scope.checkToken = function (token, groupId) {
    $scope.redirect = "/groups";
    $scope.success_msg = $rootScope.lang.Invitation_accepted;
    api.postApiRequest('checkToken', 'groups/checktoken', {
      'group_id': groupId,
      'token': token
    });
  };

  $scope.addGroupUser = function () {
    $scope.addedUser = true;
    $scope.group.users.push({
      'name': '',
      'email': '',
      'admin': false,
      'creator': false
    });
  };

  $scope.removeGroupUserByIndex = function (i) {
    return typeof $scope.group.users[i] != 'undefined' ? $scope.group.users.splice(i, 1) : null;
  };

  $scope.deleteGroupUser = function (userIndex) {
    var u = $scope.group.users[userIndex];

    if (typeof u.id == 'undefined') {
      $scope.addedUser = false;
      return $scope.removeGroupUserByIndex(userIndex);
    }

    if (typeof u["delete"] == 'undefined') u["delete"] = true;else u["delete"] = u["delete"] ? false : true;
    $scope.deletedUser = u["delete"];
  };

  $scope.selectGroupHive = function (hive) {
    if (typeof $scope.group == 'undefined') return;
    var hive_id = hive.id;
    if (typeof $scope.group.hives_selected == 'undefined' || $scope.group.hives_selected == null) $scope.group.hives_selected = [];
    if (typeof $scope.group.hives_editable == 'undefined' || $scope.group.hives_editable == null) $scope.group.hives_editable = [];
    var selected_ind = $scope.group.hives_selected.indexOf(hive_id);
    var editable_ind = $scope.group.hives_editable.indexOf(hive_id);

    if (selected_ind == -1) {
      $scope.group.hives_selected.push(hive_id);
    } else if (editable_ind == -1) {
      $scope.group.hives_editable.push(hive_id);
    } else if (selected_ind > -1 && editable_ind > -1) {
      $scope.group.hives_selected.splice(selected_ind, 1);
      $scope.group.hives_editable.splice(editable_ind, 1);
    } //console.log(hive_id, $scope.group.hives_selected, $scope.group.hives_editable)

  };

  $scope.groupsUpdate = function (e, type) {
    $scope.initGroups();
  };

  $scope.hivesUpdate = function (e, type) {
    $scope.locations = hives.locations;
  };

  $scope.hiveFilter = function (a, b) {//console.log(a,b);
  };

  $scope.setOrder = function (name) {
    if ($scope.orderName == name) {
      $scope.orderDirection = !$scope.orderDirection;
    }

    $scope.orderName = name;
  };

  $scope.natSort = function (a, b) {
    //console.log($scope.orderName, a.value, b.value);
    return naturalSort(a.value, b.value);
  };

  $scope.transSort = function (a) {
    var locale = $rootScope.locale;
    return a.trans[locale];
  };

  $scope.loadGroupIndex = function () {
    $scope.group = groups.getGroupById($routeParams.groupId);

    if ($scope.group != undefined && ($location.path().indexOf('/groups/create') > -1 || $location.path().indexOf('/edit') > -1)) {
      $scope.pageTitle = $scope.group.name;
    }
  };

  $scope.saveGroup = function () {
    var postGroup = {
      'name': $scope.group.name,
      'description': $scope.group.description,
      'hex_color': $scope.group.hex_color,
      'hives_selected': $scope.group.hives_selected,
      'hives_editable': $scope.group.hives_editable,
      'users': $scope.group.users
    };

    if ($location.path().indexOf('/groups/create') > -1) {
      api.postApiRequest('saveGroup', 'groups', postGroup);
    } else {
      api.patchApiRequest('saveGroup', 'groups/' + $scope.group.id, postGroup);
    }

    $scope.redirect = "/groups";
  };

  $scope.detachGroup = function () {
    var detach = false;
    $scope.redirect = "/groups";
    var i = 0;

    for (var id in $scope.group.users) {
      var user = $scope.group.users[id];

      if (user.id == $rootScope.user.id) {
        //console.log('detach user',user.id);
        $scope.removeGroupUserByIndex(i);
        detach = true;
        break;
      }

      i++;
    }

    if (detach) {
      var group = groups.getGroupById($routeParams.groupId);
      api.deleteApiRequest('detachGroup', 'groups/detach/' + $scope.group.id);
    }
  };

  $scope.confirmDetachGroup = function () {
    $scope.redirect = "/groups";
    $rootScope.showConfirm($rootScope.lang.Detach_from_group + '?', $scope.detachGroup);
  };

  $scope.deleteGroup = function () {
    $scope.redirect = "/groups";
    api.deleteApiRequest('deleteGroup', 'groups/' + $scope.group.id, $scope.group);
  };

  $scope.confirmDeleteGroup = function () {
    $rootScope.showConfirm($rootScope.lang.Remove_group + '?', $scope.deleteGroup);
  };

  $scope.groupsError = function (type, error) {
    $scope.error_msg = error.status == 422 ? "Error: " + convertOjectToArray(error.message).join(', ') : $rootScope.lang.empty_fields + '.';
  };

  $scope.groupChanged = function (type, data, status) {
    if (type.name == 'checkTokenLoaded' || 'detachGroupLoaded' || 'deleteGroupLoaded') // invlitation accepted
      groups.loadRemoteGroups();

    if ($scope.redirect != null) {
      $location.path($scope.redirect);

      if (data.message != null) {
        var msg = data.message;
        if (typeof $rootScope.lang[msg] != 'undefined') msg = $rootScope.lang[msg];
        $location.search('success', msg);
      }

      $scope.success_msg = null;
      $scope.redirect = null;
    } else if (data.message != null) {
      var msg = data.message;
      if (typeof $rootScope.lang[msg] != 'undefined') msg = $rootScope.lang[msg];
      $scope.success_msg = msg;
    }
  };

  $scope.groupsDetachHandler = $rootScope.$on('detachGroupLoaded', $scope.groupChanged);
  $scope.groupsDeleteError = $rootScope.$on('deleteGroupError', $scope.groupsError);
  $scope.groupsSaveError = $rootScope.$on('saveGroupError', $scope.groupsError);
  $scope.groupsDeleteHandler = $rootScope.$on('deleteGroupLoaded', $scope.groupChanged);
  $scope.groupsSaveHandler = $rootScope.$on('saveGroupLoaded', $scope.groupChanged);
  $scope.groupsTokenHandler = $rootScope.$on('checkTokenLoaded', $scope.groupChanged);
  $scope.groupsHandler = $rootScope.$on('groupsUpdated', $scope.groupsUpdate);
  $scope.hivesHandler = $rootScope.$on('hivesUpdated', $scope.hivesUpdate);
  $scope.groupsErrorHandler = $rootScope.$on('groupsError', $scope.groupsError);

  $scope.back = function () {
    if ($rootScope.optionsDialog) {
      $rootScope.optionsDialog.close();
    } else {
      $rootScope.historyBack();
    }
  }; //close options dialog


  $scope.backListener = $rootScope.$on('backbutton', $scope.back); // remove references to the controller

  $scope.removeListeners = function () {
    $scope.groupsDetachHandler();
    $scope.groupsDeleteError();
    $scope.groupsSaveError();
    $scope.groupsDeleteHandler();
    $scope.groupsSaveHandler();
    $scope.groupsTokenHandler();
    $scope.groupsHandler();
    $scope.hivesHandler();
    $scope.groupsErrorHandler();
    $scope.backListener();
  };

  $scope.$on('$destroy', function () {
    $scope.removeListeners();
  }); // call the init function

  $scope.init();
});
