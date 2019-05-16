/**
 * Inspired by AngularJS' implementation of "click dblclick mousedown..."
 *
 * This ties in the Hammer 2 events to attributes like:
 *
 * hm-tap="add_something()" hm-swipe="remove_something()"
 *
 * and also has support for Hammer options with:
 *
 * hm-tap-opts="{hold: false}"
 *
 * or any other of the "hm-event" listed underneath.
 */
'use strict';

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function (root, factory) {
  // AMD
  if (typeof define === 'function' && define.amd) {
    define(['angular', 'Hammer'], function (angular, Hammer) {
      return factory({}, angular, Hammer);
    });
  } // Node.js
  else if ((typeof exports === "undefined" ? "undefined" : _typeof(exports)) === 'object') {
      module.exports = factory({}, require('angular'), require('Hammer'));
    } // Angular
    else if (angular) {
        factory(root, root.angular, root.Hammer);
      }
})(this, function (global, angular, Hammer) {
  angular.module('angular-gestures', []);
  var HGESTURES = {
    hmDoubleTap: 'doubletap',
    hmDragstart: 'panstart',
    // will bedeprecated soon, us Pan*
    hmDrag: 'pan',
    // will bedeprecated soon, us Pan*
    hmDragUp: 'panup',
    // will bedeprecated soon, us Pan*
    hmDragDown: 'pandown',
    // will bedeprecated soon, us Pan*
    hmDragLeft: 'panleft',
    // will bedeprecated soon, us Pan*
    hmDragRight: 'panright',
    // will bedeprecated soon, us Pan*
    hmDragend: 'panend',
    // will bedeprecated soon, us Pan*
    hmPanstart: 'panstart',
    hmPan: 'pan',
    hmPanUp: 'panup',
    hmPanDown: 'pandown',
    hmPanLeft: 'panleft',
    hmPanRight: 'panright',
    hmPanend: 'panend',
    hmHold: 'press',
    hmPinch: 'pinch',
    hmPinchstart: 'pinchstart',
    hmPinchend: 'pinchend',
    hmPinchIn: 'pinchin',
    hmPinchOut: 'pinchout',
    hmPress: 'press',
    hmPressUp: 'pressup',
    hmRelease: 'pressup',
    hmRotate: 'rotate',
    hmSwipe: 'swipe',
    hmSwipeUp: 'swipeup',
    hmSwipeDown: 'swipedown',
    hmSwipeLeft: 'swipeleft',
    hmSwipeRight: 'swiperight',
    hmTap: 'tap',
    hmTouch: 'touch',
    hmTransformstart: 'transformstart',
    hmTransform: 'transform',
    hmTransformend: 'transformend'
  };
  var HRECOGNIZERS = {
    hmDoubleTap: [Hammer.Tap, 'Hammer.Tap'],
    hmDragstart: [Hammer.Pan, 'Hammer.Pan'],
    hmDrag: [Hammer.Pan, 'Hammer.Pan'],
    hmDragUp: [Hammer.Pan, 'Hammer.Pan'],
    hmDragDown: [Hammer.Pan, 'Hammer.Pan'],
    hmDragLeft: [Hammer.Pan, 'Hammer.Pan'],
    hmDragRight: [Hammer.Pan, 'Hammer.Pan'],
    hmDragend: [Hammer.Pan, 'Hammer.Pan'],
    hmPanstart: [Hammer.Pan, 'Hammer.Pan'],
    hmPan: [Hammer.Pan, 'Hammer.Pan'],
    hmPanUp: [Hammer.Pan, 'Hammer.Pan'],
    hmPanDown: [Hammer.Pan, 'Hammer.Pan'],
    hmPanLeft: [Hammer.Pan, 'Hammer.Pan'],
    hmPanRight: [Hammer.Pan, 'Hammer.Pan'],
    hmPanend: [Hammer.Pan, 'Hammer.Pan'],
    hmHold: [Hammer.Press, 'Hammer.Press'],
    hmPinch: [Hammer.Pinch, 'Hammer.Pinch'],
    hmPinchstart: [Hammer.Pinch, 'Hammer.Pinch'],
    hmPinchend: [Hammer.Pinch, 'Hammer.Pinch'],
    hmPinchIn: [Hammer.Pinch, 'Hammer.Pinch'],
    hmPinchOut: [Hammer.Pinch, 'Hammer.Pinch'],
    hmPress: [Hammer.Press, 'Hammer.Press'],
    hmPressUp: [Hammer.Press, 'Hammer.Press'],
    hmRelease: [Hammer.Press, 'Hammer.Press'],
    hmRotate: [Hammer.Rotate, 'Hammer.Rotate'],
    hmSwipe: [Hammer.Swipe, 'Hammer.Swipe'],
    hmSwipeUp: [Hammer.Swipe, 'Hammer.Swipe'],
    hmSwipeDown: [Hammer.Swipe, 'Hammer.Swipe'],
    hmSwipeLeft: [Hammer.Swipe, 'Hammer.Swipe'],
    hmSwipeRight: [Hammer.Swipe, 'Hammer.Swipe'],
    hmTap: [Hammer.Tap, 'Hammer.Tap']
  };
  var VERBOSE = false;
  angular.forEach(HGESTURES, function (eventName, directiveName) {
    angular.module('angular-gestures').directive(directiveName, ['$parse', '$log', '$timeout', 'hammerDefaultOpts', function ($parse, $log, $timeout, hammerDefaultOpts) {
      return function (scope, element, attr) {
        var handler;
        attr.$observe(directiveName, function (value) {
          var callback = $parse(value);
          var opts = $parse(attr[directiveName + 'Opts'])(scope, {});
          var defaultOpts = angular.copy(hammerDefaultOpts);
          angular.extend(defaultOpts, opts);

          if (angular.isUndefined(element.hammertime)) {
            // validate that needed recognizer is enabled
            var recognizers = angular.isDefined(defaultOpts.recognizers) ? defaultOpts.recognizers : [];
            var recognizer = HRECOGNIZERS[directiveName];

            if (angular.isDefined(recognizer)) {
              var enabled = false;
              angular.forEach(recognizers, function (r) {
                if (recognizer[0] === r[0]) {
                  if (angular.isUndefined(r[1].enable) || r[1].enable === true) {
                    enabled = true;
                  }
                }
              });

              if (!enabled) {
                throw new Error('Directive ' + directiveName + ' requires gesture recognizer [' + recognizer[1] + '] to be enabled');
              }
            }

            element.hammer = new Hammer.Manager(element[0], defaultOpts);
            scope.$on('$destroy', function () {
              element.hammer.off(eventName);
              element.hammer.destroy();
            });
          }

          handler = function handler(event) {
            if (VERBOSE) {
              $log.debug('angular-gestures: ', eventName, event);
            }

            var callbackHandler = function callbackHandler() {
              var cb = callback(scope, {
                $event: event
              });

              if (typeof cb === 'function') {
                cb.call(scope, event);
              }
            };

            if (scope.$root.$$phase === '$apply' || scope.$root.$$phase === '$digest') {
              callbackHandler();
            } else {
              scope.$apply(callbackHandler);
            }
          }; // register actual event


          element.hammer.on(eventName, handler);
        });
      };
    }]);
  });
  angular.module('angular-gestures').provider('hammerDefaultOpts', function HammerDefaultOptsProvider() {
    var opts = {};

    this.set = function (value) {
      opts = value;
    };

    this.$get = function () {
      return opts;
    };
  });
});

'use strict';

function nalert(message) {
  var normalizeStyle = "margin:0px;" + "padding:0px;" + "overflow:hidden;" + "box-sizing:border-box;" + "color:#444;";

  var createPopup = function createPopup() {
    var nalertStyle = normalizeStyle;
    nalertStyle += "position:absolute;" + "top:50%;" + "left:50%;" + "transform:translateX(-50%) translateY(-50%);" + "-webkit-transform:translateX(-50%) translateY(-50%);" + "width:250px;" + "height:120px;" + "z-index:10100;" + "background:#FFF;" + "padding-top:10px;" + "border-radius:20px;" + "box-shadow:0px 3px 10px rgba(0,0,0,0.3)";
    var messageStyle = normalizeStyle;
    messageStyle += "display:flex;" + "display:-webkit-flex;" + "height:70px;" + "align-items:center;" + "-webkit-align-items:center;" + "padding:0px 10px;" + "justify-content:center;" + "text-align:center";
    var btnStyle = normalizeStyle;
    btnStyle += "position:absolute;" + "display:block;" + "right:0px;" + "bottom:0px;" + "left:0px;" + "padding:10px;" + "text-align:center;" + "border-top:1px solid #eaeaea;" + "font-weight:bold;";
    var template = '<div class="nalert" style="' + nalertStyle + '">' + '<p class="nalert--message" style="' + messageStyle + '">' + '<span>' + message + '</span>' + '</p>' + '<a class="nalert--btn js-nalert-btn-ok" style="' + btnStyle + '" title="Ok">Ok</a>' + '</div>';
    var nalert = document.createElement('div');
    nalert.innerHTML = template;
    return nalert;
  };

  var createOverlay = function createOverlay() {
    var overlayStyle = normalizeStyle;
    overlayStyle += "position:absolute;" + "top:0px;" + "right:0px;" + "bottom:0px;" + "left:0px;" + "background:rgba(0,0,0,0.4);" + "width:100%;" + "height:100%;" + "z-index:10000;";
    var overlay = document.createElement('div');
    overlay.style = overlayStyle;
    return overlay;
  };

  var overlay = createOverlay();
  var popup = createPopup();
  var body = document.querySelector('body'); // append the popup

  var wrapper = document.createElement('div');
  wrapper.setAttribute('id', 'nalert');
  wrapper.appendChild(overlay);
  wrapper.appendChild(popup);
  body.insertBefore(wrapper, body.firstChild);

  var closePopup = function closePopup(e) {
    // prevent default
    e.preventDefault(); // remove the element

    document.querySelector('body').removeChild(wrapper);
    return false;
  }; // set the close btn


  var closeBtn = document.querySelector('.js-nalert-btn-ok');
  closeBtn.addEventListener('mouseup', closePopup);
  closeBtn.addEventListener('touchend', closePopup);
}

;
;

(function () {
  'use strict';
  /**
   * @preserve FastClick: polyfill to remove click delays on browsers with touch UIs.
   *
   * @codingstandard ftlabs-jsv2
   * @copyright The Financial Times Limited [All Rights Reserved]
   * @license MIT License (see LICENSE.txt)
   */

  /*jslint browser:true, node:true*/

  /*global define, Event, Node*/

  /**
   * Instantiate fast-clicking listeners on the specified layer.
   *
   * @constructor
   * @param {Element} layer The layer to listen on
   * @param {Object} [options={}] The options to override the defaults
   */

  function FastClick(layer, options) {
    var oldOnClick;
    options = options || {};
    /**
     * Whether a click is currently being tracked.
     *
     * @type boolean
     */

    this.trackingClick = false;
    /**
     * Timestamp for when click tracking started.
     *
     * @type number
     */

    this.trackingClickStart = 0;
    /**
     * The element being tracked for a click.
     *
     * @type EventTarget
     */

    this.targetElement = null;
    /**
     * X-coordinate of touch start event.
     *
     * @type number
     */

    this.touchStartX = 0;
    /**
     * Y-coordinate of touch start event.
     *
     * @type number
     */

    this.touchStartY = 0;
    /**
     * ID of the last touch, retrieved from Touch.identifier.
     *
     * @type number
     */

    this.lastTouchIdentifier = 0;
    /**
     * Touchmove boundary, beyond which a click will be cancelled.
     *
     * @type number
     */

    this.touchBoundary = options.touchBoundary || 10;
    /**
     * The FastClick layer.
     *
     * @type Element
     */

    this.layer = layer;
    /**
     * The minimum time between tap(touchstart and touchend) events
     *
     * @type number
     */

    this.tapDelay = options.tapDelay || 200;
    /**
     * The maximum time for a tap
     *
     * @type number
     */

    this.tapTimeout = options.tapTimeout || 700;

    if (FastClick.notNeeded(layer)) {
      return;
    } // Some old versions of Android don't have Function.prototype.bind


    function bind(method, context) {
      return function () {
        return method.apply(context, arguments);
      };
    }

    var methods = ['onMouse', 'onClick', 'onTouchStart', 'onTouchMove', 'onTouchEnd', 'onTouchCancel'];
    var context = this;

    for (var i = 0, l = methods.length; i < l; i++) {
      context[methods[i]] = bind(context[methods[i]], context);
    } // Set up event handlers as required


    if (deviceIsAndroid) {
      layer.addEventListener('mouseover', this.onMouse, true);
      layer.addEventListener('mousedown', this.onMouse, true);
      layer.addEventListener('mouseup', this.onMouse, true);
    }

    layer.addEventListener('click', this.onClick, true);
    layer.addEventListener('touchstart', this.onTouchStart, false);
    layer.addEventListener('touchmove', this.onTouchMove, false);
    layer.addEventListener('touchend', this.onTouchEnd, false);
    layer.addEventListener('touchcancel', this.onTouchCancel, false); // Hack is required for browsers that don't support Event#stopImmediatePropagation (e.g. Android 2)
    // which is how FastClick normally stops click events bubbling to callbacks registered on the FastClick
    // layer when they are cancelled.

    if (!Event.prototype.stopImmediatePropagation) {
      layer.removeEventListener = function (type, callback, capture) {
        var rmv = Node.prototype.removeEventListener;

        if (type === 'click') {
          rmv.call(layer, type, callback.hijacked || callback, capture);
        } else {
          rmv.call(layer, type, callback, capture);
        }
      };

      layer.addEventListener = function (type, callback, capture) {
        var adv = Node.prototype.addEventListener;

        if (type === 'click') {
          adv.call(layer, type, callback.hijacked || (callback.hijacked = function (event) {
            if (!event.propagationStopped) {
              callback(event);
            }
          }), capture);
        } else {
          adv.call(layer, type, callback, capture);
        }
      };
    } // If a handler is already declared in the element's onclick attribute, it will be fired before
    // FastClick's onClick handler. Fix this by pulling out the user-defined handler function and
    // adding it as listener.


    if (typeof layer.onclick === 'function') {
      // Android browser on at least 3.2 requires a new reference to the function in layer.onclick
      // - the old one won't work if passed to addEventListener directly.
      oldOnClick = layer.onclick;
      layer.addEventListener('click', function (event) {
        oldOnClick(event);
      }, false);
      layer.onclick = null;
    }
  }
  /**
  * Windows Phone 8.1 fakes user agent string to look like Android and iPhone.
  *
  * @type boolean
  */


  var deviceIsWindowsPhone = navigator.userAgent.indexOf("Windows Phone") >= 0;
  /**
   * Android requires exceptions.
   *
   * @type boolean
   */

  var deviceIsAndroid = navigator.userAgent.indexOf('Android') > 0 && !deviceIsWindowsPhone;
  /**
   * iOS requires exceptions.
   *
   * @type boolean
   */

  var deviceIsIOS = /iP(ad|hone|od)/.test(navigator.userAgent) && !deviceIsWindowsPhone;
  /**
   * iOS 4 requires an exception for select elements.
   *
   * @type boolean
   */

  var deviceIsIOS4 = deviceIsIOS && /OS 4_\d(_\d)?/.test(navigator.userAgent);
  /**
   * iOS 6.0-7.* requires the target element to be manually derived
   *
   * @type boolean
   */

  var deviceIsIOSWithBadTarget = deviceIsIOS && /OS [6-7]_\d/.test(navigator.userAgent);
  /**
   * BlackBerry requires exceptions.
   *
   * @type boolean
   */

  var deviceIsBlackBerry10 = navigator.userAgent.indexOf('BB10') > 0;
  /**
   * Determine whether a given element requires a native click.
   *
   * @param {EventTarget|Element} target Target DOM element
   * @returns {boolean} Returns true if the element needs a native click
   */

  FastClick.prototype.needsClick = function (target) {
    switch (target.nodeName.toLowerCase()) {
      // Don't send a synthetic click to disabled inputs (issue #62)
      case 'button':
      case 'select':
      case 'textarea':
        if (target.disabled) {
          return true;
        }

        break;

      case 'input':
        // File inputs need real clicks on iOS 6 due to a browser bug (issue #68)
        if (deviceIsIOS && target.type === 'file' || target.disabled) {
          return true;
        }

        break;

      case 'label':
      case 'iframe': // iOS8 homescreen apps can prevent events bubbling into frames

      case 'video':
        return true;
    }

    return /\bneedsclick\b/.test(target.className);
  };
  /**
   * Determine whether a given element requires a call to focus to simulate click into element.
   *
   * @param {EventTarget|Element} target Target DOM element
   * @returns {boolean} Returns true if the element requires a call to focus to simulate native click.
   */


  FastClick.prototype.needsFocus = function (target) {
    switch (target.nodeName.toLowerCase()) {
      case 'textarea':
        return true;

      case 'select':
        return !deviceIsAndroid;

      case 'input':
        switch (target.type) {
          case 'button':
          case 'checkbox':
          case 'file':
          case 'image':
          case 'radio':
          case 'submit':
            return false;
        } // No point in attempting to focus disabled inputs


        return !target.disabled && !target.readOnly;

      default:
        return /\bneedsfocus\b/.test(target.className);
    }
  };
  /**
   * Send a click event to the specified element.
   *
   * @param {EventTarget|Element} targetElement
   * @param {Event} event
   */


  FastClick.prototype.sendClick = function (targetElement, event) {
    var clickEvent, touch; // On some Android devices activeElement needs to be blurred otherwise the synthetic click will have no effect (#24)

    if (document.activeElement && document.activeElement !== targetElement) {
      document.activeElement.blur();
    }

    touch = event.changedTouches[0]; // Synthesise a click event, with an extra attribute so it can be tracked

    clickEvent = document.createEvent('MouseEvents');
    clickEvent.initMouseEvent(this.determineEventType(targetElement), true, true, window, 1, touch.screenX, touch.screenY, touch.clientX, touch.clientY, false, false, false, false, 0, null);
    clickEvent.forwardedTouchEvent = true;
    targetElement.dispatchEvent(clickEvent);
  };

  FastClick.prototype.determineEventType = function (targetElement) {
    //Issue #159: Android Chrome Select Box does not open with a synthetic click event
    if (deviceIsAndroid && targetElement.tagName.toLowerCase() === 'select') {
      return 'mousedown';
    }

    return 'click';
  };
  /**
   * @param {EventTarget|Element} targetElement
   */


  FastClick.prototype.focus = function (targetElement) {
    var length; // Issue #160: on iOS 7, some input elements (e.g. date datetime month) throw a vague TypeError on setSelectionRange. These elements don't have an integer value for the selectionStart and selectionEnd properties, but unfortunately that can't be used for detection because accessing the properties also throws a TypeError. Just check the type instead. Filed as Apple bug #15122724.

    if (deviceIsIOS && targetElement.setSelectionRange && targetElement.type.indexOf('date') !== 0 && targetElement.type !== 'time' && targetElement.type !== 'month') {
      length = targetElement.value.length;
      targetElement.setSelectionRange(length, length);
    } else {
      targetElement.focus();
    }
  };
  /**
   * Check whether the given target element is a child of a scrollable layer and if so, set a flag on it.
   *
   * @param {EventTarget|Element} targetElement
   */


  FastClick.prototype.updateScrollParent = function (targetElement) {
    var scrollParent, parentElement;
    scrollParent = targetElement.fastClickScrollParent; // Attempt to discover whether the target element is contained within a scrollable layer. Re-check if the
    // target element was moved to another parent.

    if (!scrollParent || !scrollParent.contains(targetElement)) {
      parentElement = targetElement;

      do {
        if (parentElement.scrollHeight > parentElement.offsetHeight) {
          scrollParent = parentElement;
          targetElement.fastClickScrollParent = parentElement;
          break;
        }

        parentElement = parentElement.parentElement;
      } while (parentElement);
    } // Always update the scroll top tracker if possible.


    if (scrollParent) {
      scrollParent.fastClickLastScrollTop = scrollParent.scrollTop;
    }
  };
  /**
   * @param {EventTarget} targetElement
   * @returns {Element|EventTarget}
   */


  FastClick.prototype.getTargetElementFromEventTarget = function (eventTarget) {
    // On some older browsers (notably Safari on iOS 4.1 - see issue #56) the event target may be a text node.
    if (eventTarget.nodeType === Node.TEXT_NODE) {
      return eventTarget.parentNode;
    }

    return eventTarget;
  };
  /**
   * On touch start, record the position and scroll offset.
   *
   * @param {Event} event
   * @returns {boolean}
   */


  FastClick.prototype.onTouchStart = function (event) {
    var targetElement, touch, selection; // Ignore multiple touches, otherwise pinch-to-zoom is prevented if both fingers are on the FastClick element (issue #111).

    if (event.targetTouches.length > 1) {
      return true;
    }

    targetElement = this.getTargetElementFromEventTarget(event.target);
    touch = event.targetTouches[0];

    if (deviceIsIOS) {
      // Only trusted events will deselect text on iOS (issue #49)
      selection = window.getSelection();

      if (selection.rangeCount && !selection.isCollapsed) {
        return true;
      }

      if (!deviceIsIOS4) {
        // Weird things happen on iOS when an alert or confirm dialog is opened from a click event callback (issue #23):
        // when the user next taps anywhere else on the page, new touchstart and touchend events are dispatched
        // with the same identifier as the touch event that previously triggered the click that triggered the alert.
        // Sadly, there is an issue on iOS 4 that causes some normal touch events to have the same identifier as an
        // immediately preceeding touch event (issue #52), so this fix is unavailable on that platform.
        // Issue 120: touch.identifier is 0 when Chrome dev tools 'Emulate touch events' is set with an iOS device UA string,
        // which causes all touch events to be ignored. As this block only applies to iOS, and iOS identifiers are always long,
        // random integers, it's safe to to continue if the identifier is 0 here.
        if (touch.identifier && touch.identifier === this.lastTouchIdentifier) {
          event.preventDefault();
          return false;
        }

        this.lastTouchIdentifier = touch.identifier; // If the target element is a child of a scrollable layer (using -webkit-overflow-scrolling: touch) and:
        // 1) the user does a fling scroll on the scrollable layer
        // 2) the user stops the fling scroll with another tap
        // then the event.target of the last 'touchend' event will be the element that was under the user's finger
        // when the fling scroll was started, causing FastClick to send a click event to that layer - unless a check
        // is made to ensure that a parent layer was not scrolled before sending a synthetic click (issue #42).

        this.updateScrollParent(targetElement);
      }
    }

    this.trackingClick = true;
    this.trackingClickStart = event.timeStamp;
    this.targetElement = targetElement;
    this.touchStartX = touch.pageX;
    this.touchStartY = touch.pageY; // Prevent phantom clicks on fast double-tap (issue #36)

    if (event.timeStamp - this.lastClickTime < this.tapDelay) {
      event.preventDefault();
    }

    return true;
  };
  /**
   * Based on a touchmove event object, check whether the touch has moved past a boundary since it started.
   *
   * @param {Event} event
   * @returns {boolean}
   */


  FastClick.prototype.touchHasMoved = function (event) {
    var touch = event.changedTouches[0],
        boundary = this.touchBoundary;

    if (Math.abs(touch.pageX - this.touchStartX) > boundary || Math.abs(touch.pageY - this.touchStartY) > boundary) {
      return true;
    }

    return false;
  };
  /**
   * Update the last position.
   *
   * @param {Event} event
   * @returns {boolean}
   */


  FastClick.prototype.onTouchMove = function (event) {
    if (!this.trackingClick) {
      return true;
    } // If the touch has moved, cancel the click tracking


    if (this.targetElement !== this.getTargetElementFromEventTarget(event.target) || this.touchHasMoved(event)) {
      this.trackingClick = false;
      this.targetElement = null;
    }

    return true;
  };
  /**
   * Attempt to find the labelled control for the given label element.
   *
   * @param {EventTarget|HTMLLabelElement} labelElement
   * @returns {Element|null}
   */


  FastClick.prototype.findControl = function (labelElement) {
    // Fast path for newer browsers supporting the HTML5 control attribute
    if (labelElement.control !== undefined) {
      return labelElement.control;
    } // All browsers under test that support touch events also support the HTML5 htmlFor attribute


    if (labelElement.htmlFor) {
      return document.getElementById(labelElement.htmlFor);
    } // If no for attribute exists, attempt to retrieve the first labellable descendant element
    // the list of which is defined here: http://www.w3.org/TR/html5/forms.html#category-label


    return labelElement.querySelector('button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea');
  };
  /**
   * On touch end, determine whether to send a click event at once.
   *
   * @param {Event} event
   * @returns {boolean}
   */


  FastClick.prototype.onTouchEnd = function (event) {
    var forElement,
        trackingClickStart,
        targetTagName,
        scrollParent,
        touch,
        targetElement = this.targetElement;

    if (!this.trackingClick) {
      return true;
    } // Prevent phantom clicks on fast double-tap (issue #36)


    if (event.timeStamp - this.lastClickTime < this.tapDelay) {
      this.cancelNextClick = true;
      return true;
    }

    if (event.timeStamp - this.trackingClickStart > this.tapTimeout) {
      return true;
    } // Reset to prevent wrong click cancel on input (issue #156).


    this.cancelNextClick = false;
    this.lastClickTime = event.timeStamp;
    trackingClickStart = this.trackingClickStart;
    this.trackingClick = false;
    this.trackingClickStart = 0; // On some iOS devices, the targetElement supplied with the event is invalid if the layer
    // is performing a transition or scroll, and has to be re-detected manually. Note that
    // for this to function correctly, it must be called *after* the event target is checked!
    // See issue #57; also filed as rdar://13048589 .

    if (deviceIsIOSWithBadTarget) {
      touch = event.changedTouches[0]; // In certain cases arguments of elementFromPoint can be negative, so prevent setting targetElement to null

      targetElement = document.elementFromPoint(touch.pageX - window.pageXOffset, touch.pageY - window.pageYOffset) || targetElement;
      targetElement.fastClickScrollParent = this.targetElement.fastClickScrollParent;
    }

    targetTagName = targetElement.tagName.toLowerCase();

    if (targetTagName === 'label') {
      forElement = this.findControl(targetElement);

      if (forElement) {
        this.focus(targetElement);

        if (deviceIsAndroid) {
          return false;
        }

        targetElement = forElement;
      }
    } else if (this.needsFocus(targetElement)) {
      // Case 1: If the touch started a while ago (best guess is 100ms based on tests for issue #36) then focus will be triggered anyway. Return early and unset the target element reference so that the subsequent click will be allowed through.
      // Case 2: Without this exception for input elements tapped when the document is contained in an iframe, then any inputted text won't be visible even though the value attribute is updated as the user types (issue #37).
      if (event.timeStamp - trackingClickStart > 100 || deviceIsIOS && window.top !== window && targetTagName === 'input') {
        this.targetElement = null;
        return false;
      }

      this.focus(targetElement);
      this.sendClick(targetElement, event); // Select elements need the event to go through on iOS 4, otherwise the selector menu won't open.
      // Also this breaks opening selects when VoiceOver is active on iOS6, iOS7 (and possibly others)

      if (!deviceIsIOS || targetTagName !== 'select') {
        this.targetElement = null;
        event.preventDefault();
      }

      return false;
    }

    if (deviceIsIOS && !deviceIsIOS4) {
      // Don't send a synthetic click event if the target element is contained within a parent layer that was scrolled
      // and this tap is being used to stop the scrolling (usually initiated by a fling - issue #42).
      scrollParent = targetElement.fastClickScrollParent;

      if (scrollParent && scrollParent.fastClickLastScrollTop !== scrollParent.scrollTop) {
        return true;
      }
    } // Prevent the actual click from going though - unless the target node is marked as requiring
    // real clicks or if it is in the whitelist in which case only non-programmatic clicks are permitted.


    if (!this.needsClick(targetElement)) {
      event.preventDefault();
      this.sendClick(targetElement, event);
    }

    return false;
  };
  /**
   * On touch cancel, stop tracking the click.
   *
   * @returns {void}
   */


  FastClick.prototype.onTouchCancel = function () {
    this.trackingClick = false;
    this.targetElement = null;
  };
  /**
   * Determine mouse events which should be permitted.
   *
   * @param {Event} event
   * @returns {boolean}
   */


  FastClick.prototype.onMouse = function (event) {
    // If a target element was never set (because a touch event was never fired) allow the event
    if (!this.targetElement) {
      return true;
    }

    if (event.forwardedTouchEvent) {
      return true;
    } // Programmatically generated events targeting a specific element should be permitted


    if (!event.cancelable) {
      return true;
    } // Derive and check the target element to see whether the mouse event needs to be permitted;
    // unless explicitly enabled, prevent non-touch click events from triggering actions,
    // to prevent ghost/doubleclicks.


    if (!this.needsClick(this.targetElement) || this.cancelNextClick) {
      // Prevent any user-added listeners declared on FastClick element from being fired.
      if (event.stopImmediatePropagation) {
        event.stopImmediatePropagation();
      } else {
        // Part of the hack for browsers that don't support Event#stopImmediatePropagation (e.g. Android 2)
        event.propagationStopped = true;
      } // Cancel the event


      event.stopPropagation();
      event.preventDefault();
      return false;
    } // If the mouse event is permitted, return true for the action to go through.


    return true;
  };
  /**
   * On actual clicks, determine whether this is a touch-generated click, a click action occurring
   * naturally after a delay after a touch (which needs to be cancelled to avoid duplication), or
   * an actual click which should be permitted.
   *
   * @param {Event} event
   * @returns {boolean}
   */


  FastClick.prototype.onClick = function (event) {
    var permitted; // It's possible for another FastClick-like library delivered with third-party code to fire a click event before FastClick does (issue #44). In that case, set the click-tracking flag back to false and return early. This will cause onTouchEnd to return early.

    if (this.trackingClick) {
      this.targetElement = null;
      this.trackingClick = false;
      return true;
    } // Very odd behaviour on iOS (issue #18): if a submit element is present inside a form and the user hits enter in the iOS simulator or clicks the Go button on the pop-up OS keyboard the a kind of 'fake' click event will be triggered with the submit-type input element as the target.


    if (event.target.type === 'submit' && event.detail === 0) {
      return true;
    }

    permitted = this.onMouse(event); // Only unset targetElement if the click is not permitted. This will ensure that the check for !targetElement in onMouse fails and the browser's click doesn't go through.

    if (!permitted) {
      this.targetElement = null;
    } // If clicks are permitted, return true for the action to go through.


    return permitted;
  };
  /**
   * Remove all FastClick's event listeners.
   *
   * @returns {void}
   */


  FastClick.prototype.destroy = function () {
    var layer = this.layer;

    if (deviceIsAndroid) {
      layer.removeEventListener('mouseover', this.onMouse, true);
      layer.removeEventListener('mousedown', this.onMouse, true);
      layer.removeEventListener('mouseup', this.onMouse, true);
    }

    layer.removeEventListener('click', this.onClick, true);
    layer.removeEventListener('touchstart', this.onTouchStart, false);
    layer.removeEventListener('touchmove', this.onTouchMove, false);
    layer.removeEventListener('touchend', this.onTouchEnd, false);
    layer.removeEventListener('touchcancel', this.onTouchCancel, false);
  };
  /**
   * Check whether FastClick is needed.
   *
   * @param {Element} layer The layer to listen on
   */


  FastClick.notNeeded = function (layer) {
    var metaViewport;
    var chromeVersion;
    var blackberryVersion;
    var firefoxVersion; // Devices that don't support touch don't need FastClick

    if (typeof window.ontouchstart === 'undefined') {
      return true;
    } // Chrome version - zero for other browsers


    chromeVersion = +(/Chrome\/([0-9]+)/.exec(navigator.userAgent) || [, 0])[1];

    if (chromeVersion) {
      if (deviceIsAndroid) {
        metaViewport = document.querySelector('meta[name=viewport]');

        if (metaViewport) {
          // Chrome on Android with user-scalable="no" doesn't need FastClick (issue #89)
          if (metaViewport.content.indexOf('user-scalable=no') !== -1) {
            return true;
          } // Chrome 32 and above with width=device-width or less don't need FastClick


          if (chromeVersion > 31 && document.documentElement.scrollWidth <= window.outerWidth) {
            return true;
          }
        } // Chrome desktop doesn't need FastClick (issue #15)

      } else {
        return true;
      }
    }

    if (deviceIsBlackBerry10) {
      blackberryVersion = navigator.userAgent.match(/Version\/([0-9]*)\.([0-9]*)/); // BlackBerry 10.3+ does not require Fastclick library.
      // https://github.com/ftlabs/fastclick/issues/251

      if (blackberryVersion[1] >= 10 && blackberryVersion[2] >= 3) {
        metaViewport = document.querySelector('meta[name=viewport]');

        if (metaViewport) {
          // user-scalable=no eliminates click delay.
          if (metaViewport.content.indexOf('user-scalable=no') !== -1) {
            return true;
          } // width=device-width (or less than device-width) eliminates click delay.


          if (document.documentElement.scrollWidth <= window.outerWidth) {
            return true;
          }
        }
      }
    } // IE10 with -ms-touch-action: none or manipulation, which disables double-tap-to-zoom (issue #97)


    if (layer.style.msTouchAction === 'none' || layer.style.touchAction === 'manipulation') {
      return true;
    } // Firefox version - zero for other browsers


    firefoxVersion = +(/Firefox\/([0-9]+)/.exec(navigator.userAgent) || [, 0])[1];

    if (firefoxVersion >= 27) {
      // Firefox 27+ does not have tap delay if the content is not zoomable - https://bugzilla.mozilla.org/show_bug.cgi?id=922896
      metaViewport = document.querySelector('meta[name=viewport]');

      if (metaViewport && (metaViewport.content.indexOf('user-scalable=no') !== -1 || document.documentElement.scrollWidth <= window.outerWidth)) {
        return true;
      }
    } // IE11: prefixed -ms-touch-action is no longer supported and it's recomended to use non-prefixed version
    // http://msdn.microsoft.com/en-us/library/windows/apps/Hh767313.aspx


    if (layer.style.touchAction === 'none' || layer.style.touchAction === 'manipulation') {
      return true;
    }

    return false;
  };
  /**
   * Factory method for creating a FastClick object
   *
   * @param {Element} layer The layer to listen on
   * @param {Object} [options={}] The options to override the defaults
   */


  FastClick.attach = function (layer, options) {
    return new FastClick(layer, options);
  };

  if (typeof define === 'function' && _typeof(define.amd) === 'object' && define.amd) {
    // AMD. Register as an anonymous module.
    define(function () {
      return FastClick;
    });
  } else if (typeof module !== 'undefined' && module.exports) {
    module.exports = FastClick.attach;
    module.exports.FastClick = FastClick;
  } else {
    window.FastClick = FastClick;
  }
})();
/*
 * Bee Monitor
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */


function filterUnique(value, index, self) {
  return self.indexOf(value) === index;
}

Array.prototype.getUnique = function () {
  return this.filter(filterUnique);
};

var runsNative = function runsNative() {
  if (typeof cordova == 'undefined') {
    return false;
  }

  return true;
};

var propertyValueInObject = function propertyValueInObject(obj, property, value, callback) {
  if (typeof obj == 'array') {
    for (var i = obj.length - 1; i >= 0; i--) {
      var item = obj[i];

      if (item.hasOwnProperty(property) && item[property] == value) {
        callback(value);
      }
    }
  } else if (_typeof(obj) == 'object') {
    for (var o in obj) {
      var item = obj[o]; //console.log(item.hasOwnProperty(property), property, item[property], value, item.children.length, item);

      if (item.children.length > 0) {
        propertyValueInObject(item.children, property, value, callback);
      } else if (item.hasOwnProperty(property) && item[property] == value) {
        callback(value);
      }
    }
  }
};

var range = function range(n) {
  return new Array(n);
};

var convertOjectToArray = function convertOjectToArray(obj) {
  var array = [];

  for (var i in obj) {
    array.push(obj[i]);
  }

  return array;
};

var convertOjectToNameArray = function convertOjectToNameArray(obj) {
  var array = [];

  for (var i in obj) {
    array.push({
      'name': i,
      'value': obj[i]
    });
  }

  return array;
};

var fixOjectNames = function fixOjectNames(obj) {
  var postFix = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
  var prefix = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';

  for (var i in obj) {
    obj[prefix + i + postFix] = obj[i];
    delete obj[i];
  }

  return obj;
};

var convertOjectToFormDataArray = function convertOjectToFormDataArray(obj, nameAdd) {
  if (typeof nameAdd == 'undefined') nameAdd = '';
  var array = [];

  for (var i in obj) {
    array.push(i + nameAdd + "=" + obj[i]);
  }

  return array;
}; // Chart functions


var solidColorObj = function solidColorObj(rgbaStr, borderRgbaStr) // This is the only way to pass a losid color value, RGB values get converted to alpha 0.2 in angular-chart.js
{
  var cObj = {
    backgroundColor: rgbaStr,
    pointBackgroundColor: rgbaStr,
    pointHoverBackgroundColor: rgbaStr
  };

  if (borderRgbaStr) {
    cObj.borderColor = borderRgbaStr;
    cObj.pointBorderColor = borderRgbaStr;
    cObj.pointHoverBorderColor = borderRgbaStr;
  }

  return cObj;
};

var convertInfluxMeasurementsArrayToChartObject = function convertInfluxMeasurementsArrayToChartObject(obj_arr, lang, labelSize, timeParseFormat) {
  if (obj_arr.length == 0) {
    console.log('convertSensorMeasurementsArrayToChartObject has no data');
    return null;
  }

  console.log('Converting ' + obj_arr.length + ' Influx measurements to chart object'); //console.log(labelSize);

  var yAxisL = {
    display: true,
    position: 'left',
    id: 'y1',
    scaleLabel: {
      display: false,
      labelArray: [],
      labelString: '',
      fontSize: labelSize
    },
    ticks: {
      fontSize: labelSize
    }
  };
  var yAxisR = {
    display: false,
    position: 'right',
    id: 'y2',
    scaleLabel: {
      display: false,
      labelArray: [],
      labelString: '',
      fontSize: labelSize
    },
    ticks: {
      fontSize: labelSize
    }
  };
  var noAxis = {
    display: false,
    offsetGridLines: true
  };
  var sensors = {
    datasets: [],
    series: [],
    data: [],
    colors: [],
    yAxes: [angular.copy(yAxisL), angular.copy(yAxisR)]
  };
  var debug = {
    datasets: [],
    series: [],
    data: [],
    colors: [],
    yAxes: [angular.copy(yAxisL), angular.copy(yAxisR)]
  };
  var sound = {
    datasets: [],
    series: [],
    data: [],
    colors: [],
    yAxes: [angular.copy(yAxisL), angular.copy(yAxisR)]
  };
  var actuators = {
    datasets: [],
    series: [],
    data: [],
    colors: [],
    yAxes: [noAxis],
    labels: []
  };
  var obj_out = {
    sensors: sensors,
    actuators: actuators,
    sound: sound,
    debug: debug
  };
  var unitLenMx = 10; // max length of unit in y-scale

  var dataset = {
    label: '',
    name: '',
    unit: '',
    visible: false,
    yAxisID: 'y1',
    cubicInterpolationMode: 'linear',
    lineTension: 0,
    fill: false,
    steppedLine: false
  }; // Fill datasets with sensor/actuator names

  for (var name in obj_arr[0]) {
    if (name != 'time') {
      var quantity = typeof SENSOR_NAMES[name] !== 'undefined' ? SENSOR_NAMES[name] : null;

      if (quantity != null) {
        var isSensor = SENSORS.indexOf(name) > -1 ? true : false;
        var isSound = SOUND.indexOf(name) > -1 ? true : false;
        var isDebug = DEBUG.indexOf(name) > -1 ? true : false;
        var isActuator = isSensor || isSound || isDebug ? false : true;
        var chart = isSensor ? obj_out.sensors : isSound ? obj_out.sound : isDebug ? obj_out.debug : obj_out.actuators; // sensor or other output

        var new_dataset = angular.copy(dataset);
        var quantityUnit = SENSOR_UNITS[name] !== 'undefined' ? SENSOR_UNITS[name] : null;
        var readableName = typeof lang[quantity] !== 'undefined' ? lang[quantity] : quantity;
        var nameAndUnit = quantityUnit != null && quantityUnit != '' ? readableName + ' (' + quantityUnit + ')' : readableName;
        var abbrName = readableName.substring(0, unitLenMx);
        var rgb = typeof SENSOR_COLOR[name] !== 'undefined' ? SENSOR_COLOR[name] : {
          r: 150,
          g: 150,
          b: 150
        };
        var color = solidColorObj('rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',0.1)', 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',1)'); //console.log(name, color);

        if (!isActuator) {
          // set y-axis label
          var axisUnit = quantityUnit == null || quantityUnit == '' ? abbrName : quantityUnit;
          axisUnit = ' ' + axisUnit;
          if (axisUnit != ' ' && chart.yAxes[0].scaleLabel.labelArray.indexOf(axisUnit) == -1) chart.yAxes[0].scaleLabel.labelArray.push(axisUnit);
          new_dataset.pointBorderWidth = 2; // dots

          new_dataset.borderColor = color.borderColor;
          new_dataset.pointBorderColor = color.pointBorderColor;
          new_dataset.pointHoverBorderColor = color.pointHoverBorderColor;
        } else {
          new_dataset.yAxisID = null; // no y axis

          new_dataset.pointBorderColor = 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',1)'; // solid dots

          new_dataset.pointBackgroundColor = 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',1)'; // solid dots
        }

        new_dataset.label = nameAndUnit;
        new_dataset.name = readableName;
        new_dataset.unit = quantityUnit;
        chart.colors.push(color);
        chart.datasets.push(new_dataset);
        chart.series.push(name);
        chart.data.push([]); //console.table(chart);
      }
    }
  } // Fill datasets with sensor/actuator data


  for (var i = 0; i < obj_arr.length; i++) {
    var obj = obj_arr[i];
    var time = obj['time'].length > 19 ? obj['time'].substr(0, 19) + 'Z' : obj['time']; // YYYY-MM-DD[T]HH:mm:ss[Z]

    if (obj['time'].length <= 19) {
      var timeParsed = moment(time, timeParseFormat).format('X');
      time = parseInt(timeParsed); // console.log('time (<=19): '+obj['time'],'cut-off: '+time, 'parsed: '+timeParsed);
    }

    var firstLast = i == 0 || i == obj_arr.length - 1 ? true : false;
    var highestActuatorY = 1;
    var afterNow = false;

    if (typeof time !== 'undefined') {
      afterNow = moment(time).format('X') > moment().format('X');
    } //obj_out.actuators.labels.push(time);


    var dataSetIndex = -1;

    for (var name in obj) {
      if (name != 'time') {
        var val = obj[name];
        var unit = SENSOR_UNITS[name] !== 'undefined' ? SENSOR_UNITS[name] : null;
        var isSensor = SENSORS.indexOf(name) > -1 ? true : false;
        var isSound = SOUND.indexOf(name) > -1 ? true : false;
        var isDebug = DEBUG.indexOf(name) > -1 ? true : false;
        var isActuator = isSensor || isSound || isDebug ? false : true;
        var chart = isSensor ? obj_out.sensors : isSound ? obj_out.sound : isDebug ? obj_out.debug : obj_out.actuators; // sensor or other output

        var dataSetIndex = chart.series.indexOf(name);

        if (dataSetIndex > -1) {
          if (typeof chart.data[dataSetIndex] == 'undefined') console.log('chart.data has no index: ' + dataSetIndex); // fill sensor data

          if (!isActuator) {
            if (val != null || firstLast) {
              //console.log(name, val, dataSetIndex, firstLast);
              if (!isSensor && Math.abs(val) > 100 && chart.series.length > 1) // transfer unit to y-scale 2
                {
                  chart.yAxes[1].display = true;
                  chart.datasets[dataSetIndex].yAxisID = 'y2';
                  var label = '';

                  if (unit != null && unit != '' && chart.yAxes[1].scaleLabel.labelArray.indexOf(unit) == -1) {
                    label = unit;
                  } else // try to transfer abbr name
                    {
                      var abbrName = ' ';
                      var quantity = typeof SENSOR_NAMES[name] !== 'undefined' ? SENSOR_NAMES[name] : null;

                      if (quantity != null) {
                        var readableName = typeof lang[quantity] !== 'undefined' ? lang[quantity] : quantity;
                        abbrName += readableName.substring(0, unitLenMx);
                      }

                      if (abbrName != null && abbrName != ' ' && chart.yAxes[1].scaleLabel.labelArray.indexOf(abbrName) == -1) label = abbrName;
                    } // set axis label


                  if (label != '') {
                    chart.yAxes[0].scaleLabel.display = true;
                    chart.yAxes[1].scaleLabel.display = true;
                    var index = chart.yAxes[0].scaleLabel.labelArray.indexOf(label);

                    if (index > -1) {
                      chart.yAxes[0].scaleLabel.labelArray.splice(index, 1);
                      chart.yAxes[1].scaleLabel.labelArray.push(label);
                    }
                  }
                }

              chart.data[dataSetIndex].push({
                x: time,
                y: val
              });
            }
          } else // fill actuator horizontal lines
            {// var dataIndex     = chart.data[dataSetIndex].length;
              // var actuatorY     = (ACTUATOR_INDEX[name] !== 'undefined') ? ACTUATOR_INDEX[name] : dataIndex + 1;
              // var actuatorUnit  = (SENSOR_UNITS[name] !== 'undefined') ? SENSOR_UNITS[name] : '';
              // highestActuatorY  = Math.max(actuatorY, highestActuatorY);
              // var previousVal   = dataIndex == 0 ? null : chart.data[dataSetIndex][dataIndex-1];
              // var valueOn       = actuatorUnit == '' && val > 0.5 ? actuatorY : actuatorUnit == '%' && val > 50 ? actuatorY : null;
              // var continuousVal = afterNow ? null : val == null ? previousVal : valueOn;
              // //var continuousVal = val > 0 ? actuatorY : null;
              // chart.data[dataSetIndex].push(continuousVal);
            }
        }
      }
    }
  } // Fill sensor axis labels


  if (obj_out.sensors.yAxes.length > 0) {
    for (var i = 0; i < obj_out.sensors.yAxes.length; i++) {
      var axisLabels = obj_out.sensors.yAxes[i].scaleLabel.labelArray.join();
      obj_out.sensors.yAxes[i].scaleLabel.labelString = axisLabels;
    }
  } // Fill actuator axis 


  var labelAmount = obj_out.actuators.series.length;
  obj_out.actuators.yAxes[0].ticks = {
    min: 0,
    max: highestActuatorY + 1
  }; //console.log(obj_out);

  return obj_out;
};

var convertSensorMeasurementsArrayToChartObject = function convertSensorMeasurementsArrayToChartObject(obj_arr) {
  var obj_out = {
    data: [],
    labels: [],
    series: []
  };
  var series_index = -1;

  for (var i = 0; i < obj_arr.length; i++) {
    var m = obj_arr[i];

    if (obj_out.series.indexOf(m.name) == -1) {
      series_index++;
      obj_out.series.push(m.name); //obj_out.series[series_index] = m.name;
      //obj_out.data[series_index] = [];
      //obj_out.labels[series_index] = [];
    }

    obj_out.data.push(m.value);
    obj_out.labels.push(m.time);
  }

  return obj_out;
}; // Settings


var convertSettingJsonToObject = function convertSettingJsonToObject(json) {
  var out = {};

  for (var i in json) {
    var o = json[i];
    if (o.name != "") out[o.name] = o.value;
  }

  return out;
};

var percDiffOf = function percDiffOf(tot, num) {
  return tot > 0 ? Math.abs(tot - num) / tot * 100 : num > 0 ? 100 : 0;
};

var round_dec = function round_dec(num, dec) {
  return Math.round(num * Math.pow(10, dec)) / Math.pow(10, dec);
};

var number_format = function number_format(number, decimals, decPoint, thousandsSep) {
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number;
  var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
  var sep = typeof thousandsSep === 'undefined' ? ',' : thousandsSep;
  var dec = typeof decPoint === 'undefined' ? '.' : decPoint;
  var s = '';

  var toFixedFix = function toFixedFix(n, prec) {
    var k = Math.pow(10, prec);
    return '' + (Math.round(n * k) / k).toFixed(prec);
  }; // @todo: for IE parseFloat(0.55).toFixed(0) = 0;


  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');

  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }

  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }

  return s.join(dec);
};

var versionCompare = function versionCompare(v1, v2, options) {
  var lexicographical = options && options.lexicographical,
      zeroExtend = options && options.zeroExtend,
      v1parts = v1.split('.'),
      v2parts = v2.split('.');

  var isValidPart = function isValidPart(x) {
    return (lexicographical ? /^\d+[A-Za-z]*$/ : /^\d+$/).test(x);
  };

  if (!v1parts.every(isValidPart) || !v2parts.every(isValidPart)) {
    return NaN;
  }

  if (zeroExtend) {
    while (v1parts.length < v2parts.length) {
      v1parts.push("0");
    }

    while (v2parts.length < v1parts.length) {
      v2parts.push("0");
    }
  }

  if (!lexicographical) {
    v1parts = v1parts.map(Number);
    v2parts = v2parts.map(Number);
  }

  for (var i = 0; i < v1parts.length; ++i) {
    if (v2parts.length == i) {
      return 1;
    }

    if (v1parts[i] == v2parts[i]) {
      continue;
    } else if (v1parts[i] > v2parts[i]) {
      return 1;
    } else {
      return -1;
    }
  }

  if (v1parts.length != v2parts.length) {
    return -1;
  }

  return 0;
};

function randomString() {
  var length = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 16;
  var text = "";
  var possible = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz0123456789"; // excluded o and O to avoid confusion with 0

  for (var i = 0; i < length; i++) {
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  }

  return text;
}
/*
 * Natural Sort algorithm for Javascript - Version 0.7 - Released under MIT license
 * Author: Jim Palmer (based on chunking idea from Dave Koelle)
 */


function naturalSort(a, b) {
  var re = /(^-?[0-9]+(\.?[0-9]*)[df]?e?[0-9]?$|^0x[0-9a-f]+$|[0-9]+)/gi,
      sre = /(^[ ]*|[ ]*$)/g,
      dre = /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/,
      hre = /^0x[0-9a-f]+$/i,
      ore = /^0/,
      i = function i(s) {
    return naturalSort.insensitive && ('' + s).toLowerCase() || '' + s;
  },
      // convert all to strings strip whitespace
  x = i(a).replace(sre, '') || '',
      y = i(b).replace(sre, '') || '',
      // chunk/tokenize
  xN = x.replace(re, '\0$1\0').replace(/\0$/, '').replace(/^\0/, '').split('\0'),
      yN = y.replace(re, '\0$1\0').replace(/\0$/, '').replace(/^\0/, '').split('\0'),
      // numeric, hex or date detection
  xD = parseInt(x.match(hre)) || xN.length != 1 && x.match(dre) && Date.parse(x),
      yD = parseInt(y.match(hre)) || xD && y.match(dre) && Date.parse(y) || null,
      oFxNcL,
      oFyNcL; // first try and sort Hex codes or Dates


  if (yD) if (xD < yD) return -1;else if (xD > yD) return 1; // natural sorting through split numeric strings and default strings

  for (var cLoc = 0, numS = Math.max(xN.length, yN.length); cLoc < numS; cLoc++) {
    // find floats not starting with '0', string or 0 if not defined (Clint Priest)
    oFxNcL = !(xN[cLoc] || '').match(ore) && parseFloat(xN[cLoc]) || xN[cLoc] || 0;
    oFyNcL = !(yN[cLoc] || '').match(ore) && parseFloat(yN[cLoc]) || yN[cLoc] || 0; // handle numeric vs string comparison - number < string - (Kyle Adams)

    if (isNaN(oFxNcL) !== isNaN(oFyNcL)) {
      return isNaN(oFxNcL) ? 1 : -1;
    } // rely on string comparison if different types - i.e. '02' < 2 != '02' < '2'
    else if (_typeof(oFxNcL) !== _typeof(oFyNcL)) {
        oFxNcL += '';
        oFyNcL += '';
      }

    if (oFxNcL < oFyNcL) return -1;
    if (oFxNcL > oFyNcL) return 1;
  }

  return 0;
} // $(document).ready(function() {
//   $("[data-widget='collapse']").click(function() {
//       //Find the box parent........
//       var box = $(this).parents(".box").first();
//       //Find the body and the footer
//       var bf = box.find(".box-body, .box-footer");
//       if (!$(this).children().find(".box-tools").children().hasClass("fa-plus")) {
//           $(this).children().find(".box-tools").children(".fa-minus").removeClass("fa-minus").addClass("fa-plus");
//           bf.slideUp();
//       } else {
//           //Convert plus into minus
//           $(this).children().find(".box-tools").children(".fa-plus").removeClass("fa-plus").addClass("fa-minus");
//           bf.slideDown();
//       }
//   });
// });

/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Icon filters
 */


angular.module('iconFilters', []).filter('makeUrl', function () {
  return function (url) {
    return 'img/icons/icon_' + url + '.svg';
  };
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Text filters
 */

angular.module('textFilters', []).filter('removeDot', function () {
  return function (str) {
    return str.replace('.', '');
  };
});
