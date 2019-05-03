/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Text filters
 */
angular.module('textFilters', []).filter('removeDot', function() 
{
  return function(str) {
    return str.replace('.', '');
  };
});