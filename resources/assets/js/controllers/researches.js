/*
 * BEEP app
 * Author: Iconize <pim@beep.nl>
 *
 * Researches controller
 */
app.controller('ResearchesCtrl', function($scope, $rootScope, $window, $timeout, $location, $filter, $interval, api, $routeParams, ngDialog, hives, measurements) 
{

    // settings
    $scope.hives                 = [];
    $scope.apiaries              = [];
    $scope.sensors               = [];
    $scope.researches            = [];
    $scope.selectedResearchId    = null;
    $scope.selectedConsentId     = null;
 
     // handlers
    $scope.isLoading             = false;
 
    $scope.init = function()
    {
        $scope.hives             = hives.hives;
        $scope.apiaries          = hives.locations_owned;
        $scope.sensors           = measurements.sensors;
        $scope.setDateLanguage();
        $scope.loadResearches();
    };

    // Datepicker
    $scope.setDateLanguage = function(minDate=null, maxDate=null)
    {
        $("#dtBox").DateTimePicker(
        {
            minDateTime     : minDate,
            maxDateTime     : maxDate,
            dateTimeFormat  : 'yyyy-MM-dd HH:mm', // ISO formatted date
            language        : $rootScope.locale,
            mode            : 'datetime',
            buttonsToDisplay: ["HeaderCloseButton", "SetButton"], // "ClearButton"
            formatHumanDate : function(dateObj, mode, format)
                                {
                                    var output = '';
                                    output    += dateObj.day + ' ';
                                    output    += parseInt(dateObj.dd) + ' ';
                                    output    += dateObj.month + ' ';
                                    output    += dateObj.yyyy + ', ';
                                    output    += dateObj.HH + ':';
                                    output    += dateObj.mm + ' ';
                                    return output;
                                },
            afterShow       : function(inputElement)
                                {
                                    $("#dtBox .dtpicker-compValue").attr('type', 'tel'); // set monbile input keyboard to numeric
                                }
        });
    }

    $scope.editConsentDate = function(consent)
    {
        if (consent.consent === 1) // from this date there has been given a consent, so only dates earlier are possible
        {
            $scope.setDateLanguage(null, consent.updated_at);
        }
        else if (consent.consent === 0) // from this date there is no consent anymore, so only dates later are possible
        {
            $scope.setDateLanguage(consent.updated_at, null);
        }
    }

    $scope.loadResearches = function()
    {
        api.getApiRequest('research', 'research');
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
    $scope.updateConsent = function(research_id, consent_id, date)
    {
        console.log('Update consent', consent_id, date);
        api.patchApiRequest('researchConsent', 'research/'+research_id+'/edit/'+consent_id, {'updated_at':date});
    }

    $scope.deleteNoConsent = function(research_id, consent)
    {
        $scope.selectedResearchId = research_id;
        $scope.selectedConsentId  = consent.id;
        var text = $rootScope.lang.Delete+' '+$rootScope.lang.Consent+': '+consent.updated_at+' -> '+(consent.consent === 1 ? $rootScope.lang.consent_yes : $rootScope.lang.consent_no)+'?'
        
        $rootScope.showConfirm(text, $scope.performDeleteNoConsent);
    }

    $scope.performDeleteNoConsent = function()
    {
        if ($scope.selectedResearchId && $scope.selectedConsentId)
        {
            console.log('Delete consent', $scope.selectedConsentId);
            api.deleteApiRequest('researchConsent', 'research/'+$scope.selectedResearchId+'/delete/'+$scope.selectedConsentId);

            $scope.selectedResearchId = null;
            $scope.selectedConsentId  = null;
        }
        else
        {
            console.log('No consent to delete');
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

