app.directive('checklistFieldset', ['$rootScope', function($rootScope) {
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
                var bees_per_cm2= 1.25;
                var colony_size = 0;
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

                var hive = scope.hive;
                if (pixelsTotal == 0 || typeof hive == 'undefined' || hive == null || isNaN(pixelsBees) || isNaN(pixelsTotal))
                {
                    colony_size = 0;
                }
                else
                {
                    // colony_size = ratio occupied * fully occupied frames * 2 * brood layers * bees per cm2
                    var ratio   = pixelsTotal > pixelsBees ? (pixelsBees / pixelsTotal) : 1;
                    colony_size = Math.round( ratio * (parseFloat(hive.fr_width_cm) * parseFloat(hive.fr_height_cm) * hive.frames * 2 * hive.brood_layers * bees_per_cm2) );
                }
                //console.log(hive, colony_size, pixelsTotal, pixelsBees, parseFloat(hive.fr_width_cm), parseFloat(hive.fr_height_cm), hive.frames);

                // put value into input element 'colony_size'
                for (var i = scope.cat.children.length - 1; i >= 0; i--) 
                {
                    var child = scope.cat.children[i];
                    if (child.name == 'colony_size')
                        child.value = colony_size;
                }
                scope.colony_size = colony_size;
                console.log('tpa_colony_size', colony_size);
              };
              $rootScope.$on('inspectionItemUpdated', scope.calculateTpaColonySize);
          }
          else if (scope.cat.name == 'liebefelder_method')
          {
              //console.log('top_photo_analysis function defined');
              scope.calculateLieberfeldColonySize = function() 
              { 
                var bees_per_cm2= 1.25;
                var colony_size = 0;
                var bees_squares_25cm2 = 0;

                for (var i = scope.cat.children.length - 1; i >= 0; i--) 
                {
                    var child = scope.cat.children[i];

                    if (typeof child != 'undefined' && child != null && typeof child.name != 'undefined' && child.name.indexOf('frame') > -1) // frame_1_side_a
                    {
                        for (var j = child.children.length - 1; j >= 0; j--) 
                        {
                            var child2 = child.children[j];
                            if (typeof child2 != 'undefined' && child2 != null && typeof child2.name != 'undefined' && child2.name == 'bees_squares_25cm2' && parseFloat(child2.value) > 0)
                            {
                                bees_squares_25cm2 += parseFloat(child2.value);
                            }
                        }        
                    }
                }
                
                var hive = scope.hive;
                if (typeof hive == 'undefined' || hive == null || isNaN(bees_squares_25cm2) )
                {
                    colony_size = 0;
                }
                else
                {
                    colony_size = Math.round( bees_squares_25cm2 * 25 * bees_per_cm2);
                }

                // put value into input element 'colony_size'
                for (var i = scope.cat.children.length - 1; i >= 0; i--) 
                {
                    var child = scope.cat.children[i];
                    if (child.name == 'colony_size')
                        child.value = colony_size;
                }
                scope.colony_size = colony_size;
                console.log('lieberfeld_colony_size', colony_size);
                //console.log(hive, scope.colony_size, pixelsTotal, pixelsBees, parseFloat(hive.fr_width_cm), parseFloat(hive.fr_height_cm), hive.frames);
              };
              $rootScope.$on('inspectionItemUpdated', scope.calculateLieberfeldColonySize);
          }
        },
        templateUrl: '/app/views/forms/checklist_fieldset.html'
    };
}]);
