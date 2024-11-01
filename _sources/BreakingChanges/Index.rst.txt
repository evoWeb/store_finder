..  include:: /Includes.rst.txt
..  index:: Breaking changes
..  _breaking-changes:

================
Breaking Changes
================

02. August 2024
===============

Replacing CountryRepository with CountryProvider

CountryRepository relayed on EXT:static_info_tables. As the core now is
providing countries, the installation of that extension is not needed
anymore.

An install tool migration to update the locations records is provided.

The sf:form.selectCountries viewHelper got dropped in favor of f:form.countrySelect.
If you override a partial. You need to get the country select in sync
with the provided one.

20. Mai 2024
============

Result of location repository functions are either location model or array of
location models.

Removed QueryBuilderHelper. The queryBuilder gets assignment to the query as
statement and let Extbase take care of the conversion.

The country selector does not take string indexes anymore. If you used isoCodeA2
or isoCodeA3 in TypoScript before, you need to change your Search.html partial
to only use {constraint.country.uid} as value and set the optionValueField="uid"
argument of the sf:form.selectCountries ViewHelper.

The countryValueType setting in TypoScript is dropped.

Refactor geocode service to use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory
instead of creating of using Http\Adapter\Guzzle7\Client directly


17. February 2023
=================

Remove allowed on standard page
-------------------------------
Records of attributes and locations are not allowed on standard pages anymore.

29. August 2022
===============

The import command is refactored and the arguments and options are cleaned up.
Please read the :ref:`docs <importCommand>` for the changes

15. February 2021
=================

Rename property
---------------
Rename content to contentElement in location model

01. Mai 2020
============

Refactor signal slots to PSR-14 events
--------------------------------------
All slots are replaced with events
Evoweb\StoreFinder\Controller\MapController mapActionWithConstraint with
MapGetLocationsByConstraintsEvent

03. October 2019
================

Drop migration wizard
---------------------
Dropped support of LocationMigrationWizard. It's now more then three years that
locator is not really supported anymore. Who every wants to migrate locations
should use version 3.x and upgrade afterwards.

Change geocoding
----------------
Change to use geocoder-php/geocoder for geocoding locations. By this a hole
spectrum of `providers/geocoders <https://github.com/geocoder-php/Geocoder#world>`_
are available now

Migration steps
---------------
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

.. code-block:: javascript
   :caption: EXT:my_extension/Resources/Public/JavaScript/map.js

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

Migration steps
---------------
Check for {location.nameRaw} and {location.cityRaw} and replace it with
{location.nameRaw -> f:format.json()} and {location.cityRaw -> f:format.json()}

All {location.* -> f:format.json()} usage may not be wrapped in " or '. If present remove those.
