app.directive('checklistFieldset', ['$rootScope', '$filter', function($rootScope, $filter) {
    return {
        restrict: 'EA',
        scope: {
            cat: '=',
            cols: '=',
        },
        link: function (scope, iElement, iAttrs) 
        {
          scope.lang   = $rootScope.lang;
          scope.locale = $rootScope.locale;
          scope.hive   = $rootScope.hive;
          scope.colony_size = 0;
          scope.rangeStep   = function(min, max, step) { return rangeStep(min, max, step); };

          if (scope.cat.name == 'top_photo_analysis')
          {
              //console.log('top_photo_analysis function defined');
              scope.calculateTpaColonySize = function() 
              { 
                var hive = scope.hive;

                if (typeof hive == 'undefined' || (typeof hive != 'undefined' && hive.brood_layers_tpa == null && hive.frames_tpa == null))
                {
                  // don't recalculate TPA colony size, because if hive changes, the edit inspection is wrogly saved
                  return;
                }                  

                var bees_per_cm2= 1.25;
                var colony_size = null;
                var pixelsTotal = 0;
                var pixelsBees  = 0;

                for (var i = scope.cat.children.length - 1; i >= 0; i--) 
                {
                    var child = scope.cat.children[i];

                    if (child.name == 'pixels_with_bees')
                        pixelsBees = parseInt(child.value);
                    else if (child.name == 'pixels_total_top')
                        pixelsTotal = parseInt(child.value);
                }

                if (pixelsTotal == 0 || typeof hive == 'undefined' || hive == null || isNaN(pixelsBees) || isNaN(pixelsTotal || hive.fr_width_cm == null || hive.fr_height_cm == null))
                {
                    colony_size = null;
                }
                else
                {
                    // colony_size = ratio occupied * fully occupied frames * 2 * brood layers * bees per cm2
                    var ratio   = 0;
                    if (!isNaN(pixelsTotal) && !isNaN(pixelsBees) && pixelsBees > 0)
                      ratio = pixelsTotal > pixelsBees ? (pixelsBees / pixelsTotal) : 1;

                    colony_size = Math.round( ratio * (parseFloat(hive.fr_width_cm) * parseFloat(hive.fr_height_cm) * hive.frames_tpa * 2 * hive.brood_layers_tpa * bees_per_cm2) );
                    //console.log(ratio, colony_size, pixelsTotal, pixelsBees, parseFloat(hive.fr_width_cm), parseFloat(hive.fr_height_cm), hive.frames);
                }

                // put value into input element 'colony_size'
                for (var i = scope.cat.children.length - 1; i >= 0; i--) 
                {
                    var child = scope.cat.children[i];
                    if (child.name == 'colony_size')
                        child.value = colony_size;
                }
                scope.colony_size = colony_size;
                //console.log('tpa_colony_size', colony_size);
              };
              $rootScope.$on('inspectionItemUpdated', scope.calculateTpaColonySize);
          }
          else if (scope.cat.name == 'liebefelder_method')
          {
              scope.frame_filter = function (item) { 
                if (typeof scope.hive != 'undefined' && typeof item.name != 'undefined' && (item.name.indexOf('colony_size') > -1 || (item.name.indexOf('frame') > -1 && parseInt(item.name.split('_')[1]) <= scope.hive.brood_layers * scope.hive.frames)) )
                  return true;
                else
                  return false
              };

              scope.super_filter = function (item) { 
                if (typeof scope.hive != 'undefined' && typeof item.name != 'undefined' && item.name.indexOf('super') > -1 && parseInt(item.name.split('_')[1]) <= scope.hive.honey_layers)
                  return true;
                else
                  return false
              };

              scope.super_and_frame_filter = function (item) { 
                    if (typeof scope.hive != 'undefined' && typeof item.name != 'undefined' && (item.name.indexOf('super') > -1 || item.name.indexOf('frame') > -1))
                    {
                      if (item.name.indexOf('frame') > -1 && parseInt(item.name.split('_')[1]) <= scope.hive.brood_layers * scope.hive.frames)
                        return true;
                      else if (item.name.indexOf('super') > -1 && parseInt(item.name.split('_')[1]) <= scope.hive.honey_layers)
                        return true;
                    }
                    return false;
              };

              //console.log('top_photo_analysis function defined');
              scope.calculateLieberfeldColonySize = function() 
              { 
                var hive               = scope.hive;
                var bees_per_cm2       = 1.25;
                var colony_size        = null;
                var bees_squares_25cm2 = 0;

                var bee_layers = $filter('filter')(scope.cat.children, scope.super_and_frame_filter);
                
                //console.log('bee_layers', bee_layers);
                for (var i = bee_layers.length - 1; i >= 0; i--) 
                {
                  var child = bee_layers[i];

                  for (var j = child.children.length - 1; j >= 0; j--) 
                  {
                      var child2 = child.children[j];
                      if (typeof child2 != 'undefined' && child2 != null && typeof child2.name != 'undefined' && child2.name == 'bees_squares_25cm2' && parseFloat(child2.value) > 0)
                      {
                          bees_squares_25cm2 += parseFloat(child2.value);
                      }
                  }        
                }
                
                if (typeof hive == 'undefined' || hive == null || isNaN(bees_squares_25cm2) || bees_squares_25cm2 == 0)
                {
                    colony_size = null;
                }
                else
                {
                    colony_size = Math.round( bees_squares_25cm2 * 25 * bees_per_cm2);
                }

                // put value into input element 'colony_size'
                for (var k = scope.cat.children.length; k >= 0; k--) 
                {
                    var child = scope.cat.children[k];
                    if (typeof child != 'undefined' && child != null && child.name == 'colony_size')
                        child.value = colony_size;
                }
                scope.colony_size = colony_size;
                //console.log('lieberfeld_colony_size', colony_size);
                //console.log(hive, scope.colony_size, pixelsTotal, pixelsBees, parseFloat(hive.fr_width_cm), parseFloat(hive.fr_height_cm), hive.frames);
              };
              $rootScope.$on('inspectionItemUpdated', scope.calculateLieberfeldColonySize);
          }
        },
        templateUrl: '/app/views/forms/checklist_fieldset.html'
    };
}]);
