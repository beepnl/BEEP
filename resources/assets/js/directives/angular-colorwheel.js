/*
 * BEEP
 * Author: Neat projects <ties@expertees.nl>
 *
 * Colorwheel directive
 */

  app.directive('colorwheel', function($q) 
  {
    return {
      restrict: 'A',
      scope: 
      {
        current : '=',
      },
      link: function($scope, $elem, $attrs) 
      {
        var colorwheel;

        setTimeout(function()
        {
          colorwheel = createColorwheel($scope, $elem);
        }, 500);


        function createColorwheel($scope, $elem) 
        {
          var el      = $elem[0];

          var drawBackground = function()
          {
            var context = el.getContext('2d');
            var width = el.width;
            var height = el.height;
            var cx = width / 2;
            var cy = height / 2;
            var radius = width  / 2.3;
            var imageData;
            var pixels;
            var hue; 
            var sat; 
            var value;
            var i = 0; 
            var x; 
            var y; 
            var rx; 
            var ry; 
            var d;
            var f; 
            var g; 
            var p; 
            var u; 
            var v; 
            var w; 
            var rgb;

            el.width  = width/2;
            el.height = height/2;
            imageData = context.createImageData(width/2, height/2);
            pixels    = imageData.data;

            for (y = 0; y < height; y = y + 2) {
                for (x = 0; x < width; x = x + 2, i = i + 4) {
                    rx = x - cx;
                    ry = y - cy;
                    d = rx * rx + ry * ry;
                    if (d < radius * radius) {
                        hue = 6 * (Math.atan2(ry, rx) + Math.PI) / (2 * Math.PI);
                        sat = Math.sqrt(d) / radius;
                        g = Math.floor(hue);
                        f = hue - g;
                        u = 255 * (1 - sat);
                        v = 255 * (1 - sat * f);
                        w = 255 * (1 - sat * (1 - f));
                        pixels[i] = [255, v, u, u, w, 255, 255][g];
                        pixels[i + 1] = [w, 255, 255, v, u, u, w][g];
                        pixels[i + 2] = [u, u, w, 255, 255, v, u][g];
                        pixels[i + 3] = 255;
                    }
                }
            }

            context.putImageData(imageData, 0, 0);
          };


          var drawHandler = function()
          {
            var x = (dragger.width/4);
            var y = (dragger.height/4);

            
            var path             = new paper.Path.Circle(new paper.Point(0, 0), 10);
              path.strokeWidth   = 3;
              path.strokeColor   = '#FFFFFF';
              path.shadowColor   = new paper.Color(0, 0, 0, 0.5),
              path.shadowBlur    = 15;
              path.shadowOffsetX = 7;
              path.shadowOffsetY = 7;

              path.position = new paper.Point(x, y);

            return path;
          };

          background = drawBackground();


          // install paper
          var dragger = document.createElement('canvas');
              dragger.width  = el.width;
              dragger.height = el.height;

          el.parentNode.insertBefore(dragger, el.nextSibling); 

          paper.setup(dragger);

          with(paper)
          {
            view.setViewSize((el.width), (el.height));

            /*  setup the drag options 
            handler = this.drawHandler();
            */
            
            handler    = drawHandler();
            isDown     = false;

            doDrag = function(e)
            {
              if(isDown)
              {
                var dragOffsetX = 0;
                var dragOffsetY = 0;

                if(e.offsetX == undefined || e.offsetY == undefined)
                {
                  var rect = dragger.getBoundingClientRect();

                  dragOffsetX = (e.targetTouches[0].clientX)-(rect.left);
                  dragOffsetY = (e.targetTouches[0].clientY)-(rect.top);
                }
                else
                {
                  dragOffsetX = e.offsetX;
                  dragOffsetY = e.offsetY;
                }

                var x = (dragOffsetX);
                var y = (dragOffsetY);

                var centerX = (dragger.width/2);
                var centerY = (dragger.height/2);
                var radius  = (dragger.width/1.8);

                var minX    = (centerX-(dragger.width/2.3))+12;
                var maxX    = (centerX+(dragger.width/2.3))-12;

                var minY    = (centerY-(dragger.height/2.3))+12;
                var maxY    = (centerY+(dragger.height/2.3))-12;

                var rad      = (radius^2);
                var inCircle = (Math.abs(centerX-x)^2) + (Math.abs(centerY-y)^2);


                //if(inCircle < rad && (x > minX && x < maxX) && (y > minY && y < maxY))
                //{
                  handler.position = new paper.Point(x, y);

                  var d      = el.getContext('2d').getImageData(x, y, 1, 1);
                  var pixel  = d.data;
                  var dColor = pixel[2] + 256 * pixel[1] + 65536 * pixel[0];
                  
                  var colorEvent = new CustomEvent('colorwheel.select', 
                  {
                    detail : { color : dColor.toString(16)},
                  });
                  document.dispatchEvent(colorEvent);

                  view.update();
                //}


              }
            };


            startDrag = function(e)
            {
              isDown = true;
              doDrag(e);
            };

            endDrag = function(e)
            {
              isDown = false;
              doDrag(e);
            };



            dragger.addEventListener('mousedown', startDrag, false);
            dragger.addEventListener('mouseup', endDrag, false);
            dragger.addEventListener('mousemove', doDrag, false);
            dragger.addEventListener('touchstart', startDrag, false);
            dragger.addEventListener('touchend', endDrag, false);
            dragger.addEventListener('touchmove', doDrag, false);

            view.draw();
          }




        }


        $scope.$on('$destroy', function() 
        {
          //ctx.clearRect(0,0,w,h);
        });

      }

    };



});
