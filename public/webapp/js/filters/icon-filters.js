/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Icon filters
 */
angular.module('iconFilters', []).filter('makeUrl', function() 
{
  return function(url) {
    return 'img/icons/icon_'+url+'.svg';
  };
});