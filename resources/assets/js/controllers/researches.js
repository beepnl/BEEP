/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Settings controller
 */
app.controller('ResearchesCtrl', function($scope, $rootScope, $window, $timeout, $location, $filter, $interval, api, $routeParams, ngDialog, hives, measurements) 
{

    // settings
    $scope.hives                 = [];
    $scope.apiaries              = [];
    $scope.sensors               = [];
    $scope.researches            = [];
 
     // handlers
    $scope.isLoading             = false;
 
    $scope.init = function()
    {
        $scope.hives             = hives.hives;
        $scope.apiaries          = hives.locations_owned;
        $scope.sensors           = measurements.sensors;
        $scope.loadResearches();
    };

    $scope.loadResearches = function()
    {
        $scope.researches = api.getApiRequest('research', 'research', $scope.sensors);
    }

    $scope.updateResearches = function(e, data)
    {
        $scope.researches = data;
    }
    $scope.researchLoadedHandler = $rootScope.$on('researchLoaded', $scope.updateResearches);


    $scope.consentToggle = function(research_id, consent)
    {
        if (consent)
        {
            api.postApiRequest('researchConsent', 'research/'+research_id+'/add_consent');
        }
        else
        {
            api.postApiRequest('researchConsent', 'research/'+research_id+'/remove_consent');
        }
    }
    $scope.researchConsentLoadedHandler = $rootScope.$on('researchConsentLoaded', $scope.loadResearches);

    $scope.back = function()
    {
        if ($rootScope.optionsDialog)
        {
            $rootScope.optionsDialog.close();
        }
        else
        {
            $rootScope.historyBack();
        }
    };

    //close options dialog
    $scope.backListener = $rootScope.$on('backbutton', $scope.back);


    $scope.init();


    // remove the listeners
    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });


    // remove listeners
    $scope.removeListeners = function()
    {
        $scope.researchLoadedHandler();
        $scope.researchConsentLoadedHandler();
        $scope.backListener();
    };

});

