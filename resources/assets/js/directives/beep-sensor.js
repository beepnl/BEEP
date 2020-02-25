app.directive('beepSensor', ['$rootScope', function($rootScope) {
    return {
      restrict: 'EA',
      template:
         
          // Table row
            '<td>'+
              '<span ng-bind="index+1" style="margin-right: 10px;"></span>'+
              '<a ng-if="!edit" ng-click="show(index)" class="btn btn-default"><i class="fa fa-line-chart"></i></a>'+
            '</td>'+
            '<td>'+
              '<input ng-if="edit" class="form-control" ng-model="sensor.name" ng-disabled="sensor.delete">'+
              '<div style="display: block;" ng-if="!edit"><strong>{{sensor.name}}</strong></div>'+
              '<div style="display: block;" ng-if="!edit && sensor.selected_type.name != \'beep\'">DEV EUI: {{ sensor.key }}</div>'+
              '<div style="display: block;" ng-if="!edit && sensor.selected_type.name == \'beep\'">HW ID: {{ sensor.hardware_id }}</div>'+
              '<div style="display: block;" ng-if="!edit && sensor.selected_type.name == \'beep\'">FW v{{sensor.firmware_version}}</div>'+
            '</td>'+
            '<td ng-if="edit">'+
              '<input class="form-control" ng-model="sensor.key" ng-disabled="sensor.delete">'+
            '</td>'+
            '<td>'+
              '<select ng-if="edit" ng-change="changetype(index, sensor.selected_type.name)" ng-model="sensor.selected_type" ng-options="item as item.trans[locale] for item in sensortypes | orderBy:transSort track by item.name" class="form-control" ng-disabled="sensor.delete">'+
              '<option value="">{{lang.Select}} {{lang.type}}...</option>'+
              '</select>'+
              '<p ng-if="!edit && sensor.selected_type.name != \'beep\'">{{sensor.selected_type.name}}</p>'+
              '<img src="/img/icons/beep-base.png" style="height:60px;" ng-if="!edit && sensor.selected_type.name == \'beep\'" title="BEEP base - DEV EUI: {{ sensor.key }}, HW v{{sensor.hardware_version}}, Booted {{ sensor.boot_count != null ? sensor.boot_count : \'?\' }}x, BLE PIN: {{ sensor.ble_pin }}">'+
            '</td>'+
            '<td ng-if="!edit">'+
              '<div style="display: block; margin-right:10px;"><i class="fa fa-battery"></i> {{ sensor.battery_voltage != null ? sensor.battery_voltage + " V" : " ?"}}</div>'+
              '<div style="display: block; margin-right:10px;"><i class="fa fa-wifi"></i> {{ sensor.last_message_received != null ? sensor.last_message_received : " ?"}}</div>'+
              '<div style="display: block;"><i class="fa fa-refresh"></i> {{ sensor.measurement_transmission_ratio != null ? sensor.measurement_transmission_ratio < 2 ? sensor.measurement_interval_min+" min" : sensor.measurement_interval_min + " * " + sensor.measurement_transmission_ratio+" min" : " ?"}}</div>'+
            '</td>'+
            '<td>'+
              '<p class="hive-name-mobile" ng-bind="sensor.hive.name"></p>'+
              '<p class="location notes" ng-bind="sensor.hive.location"></p>'+
            '</td>'+
            '<td ng-if="edit">'+
              '<select ng-change="change(index, sensor.selected_hive_id)" ng-model="sensor.selected_hive_id" ng-options="item.id as item.name group by item.location for item in hives | orderBy:\'name\' track by item.id" class="form-control" ng-disabled="sensor.delete">'+
              '<option value="">{{lang.Select}} {{lang.hive}}...</option>'+
              '</select>'+
            '</td>'+
            '<td ng-if="edit">'+
              '<a ng-click="settings(index)" data-toggle="modal" data-target="#sensor-modal" class="btn btn-primary" title="{{lang.Sensor}} {{lang.settings}}"><i class="fa fa-cog"></i></a>'+
              '<a ng-click="delete(index)" class="btn pull-right" ng-class="{\'btn-warning\':sensor.delete, \'btn-danger\':!sensor.delete}" title="{{sensor.delete ? lang.Undelete : lang.Delete}}"><i class="fa fa-trash"></i></a>'+
            '</td>',

      scope: {
        hives: '=?', // show location name
        sensortypes: '=?',
        sensor: '=?',
        change: '=?',
        changetype: '=?',
        settings: '=?',
        delete: '=?',
        show: '=?',
        index: '=?',
        edit: '=?'
      },
      link: function(scope, element, attributes) {
        scope.locale = $rootScope.locale;
        scope.lang   = $rootScope.lang;
        scope.mobile = $rootScope.mobile;
      }
    };
  }
]);