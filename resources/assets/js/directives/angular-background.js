/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Background directive
 */
app.directive('background', function($q) 
{
    return {
      restrict: 'E',
      link: function(scope, element, attrs, tabsCtrl) {

        scope.preload = function(url)
        {
          element.addClass('loading');

          var deffered = $q.defer(),

          image     = new Image();
          image.src = url;

          if (image.complete) {
        
            deffered.resolve();

          } else {
        
            image.addEventListener('load', function() {
              deffered.resolve();
            });
        
            image.addEventListener('error', function() {
              deffered.reject();
            });
          }
          return deffered.promise;
        };
        

        scope.fadeImage = function()
        {
            element.css({"background-image": "url('" + attrs.url + "')"});
            element.addClass('animated fadeIn');
            element.removeClass('loading');

            setTimeout(function()
            {
              element.removeClass('animated fadeIn');
            }, 1000);
        };


        scope.preload(attrs.url).then(function()
        {
           scope.fadeImage();
        });

        scope.$watch(function(){ return attrs.url; }, function()
        {
            element.css({ "background-image": "none"});

            scope.preload(attrs.url).then(function()
            {
                scope.fadeImage();
            })
        });
      
      }
    };
  });