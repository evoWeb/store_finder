.. include:: ../Includes.txt


.. _configuration:

Breaking Changes
----------------


01. Mai 2020
============
Refactor signal slots to PSR-14 events
--------------------------------------
All slots are replaced with events
Evoweb\StoreFinder\Controller\MapController mapActionWithConstraint with MapGetLocationsByConstraintsEvent



03. October 2019
================
Drop migration wizard
_____________________
Dropped support of LocationMigrationWizard. It's now more then three years that locator is not really supported
anymore. Who every wants to migrate locations should use version 3.x and upgrade afterwards.

Change geocoding
________________
Change to use geocoder-php/geocoder for geocoding locations. By this a hole spectrum of providers/geocoders
are available now [ https://github.com/geocoder-php/Geocoder#world ]

Migration steps:
________________

Please check the extension configuration whether the api key assignment still works



31. January 2019
================
Drop already deprecated GeocodeLocationsTask in favor of GeocodeLocationsCommandController

Deprecate GeocodeLocationsCommandController to be replaced with GeocodeLocationsCommand once support for
TYPO3 8.7 gets dropped.



18. May 2019
============
As of now configuration.insertSingleViewInto is deprecated and removed.

Please use configuration.renderSingleViewCallback instead. This should contain a callback function
which renders the single view element. As parameter location and infoWindowTemplate are available.
In addition a configuration.handleCloseButtonCallback should be provided.

Example:

::
   configuration.renderSingleViewCallback = function (location, infoWindowTemplate) {
         location['information']['staticMapCenter'] = encodeURIComponent(location.information.address) + ',+'
            + encodeURIComponent(location.information.zipcode) + ',+'
            + encodeURIComponent(location.information.city) + ',+'
            + encodeURIComponent(location.information.country);

         html = infoWindowTemplate.render(location.information);

         var $singleView = $('.yourSingleView');
         if ($singleView.hasClass('show')) {
            $singleView.hide();
            $singleView.removeClass('show');
         }
         $singleView.html(html);
         $singleView.show();
         $singleView.addClass('show');

         $('body').trigger('initializeTabs');
   };

   configuration.handleCloseButtonCallback = function (button) {
         var $singleView = button.parents('.yourSingleView');
         $singleView.hide();
         $singleView.removeClass('show');
   }



10. January 2019
================
As of the location model does not escapeJsonString any properties anymore. With this getNameRaw and
getCityRaw are dropped.

Migration steps:
________________
Check for {location.nameRaw} and {location.cityRaw} and replace it with
{location.nameRaw -> f:format.json()} and {location.cityRaw -> f:format.json()}

All {location.* -> f:format.json()} usage may not be wrapped in " or '. If present remove those.
