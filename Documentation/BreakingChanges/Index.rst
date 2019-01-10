.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _configuration:

Breaking Changes
----------------

10. January 2019
================
As of the location model does not escapeJsonString any properties anymore. With this getNameRaw and
getCityRaw are dropped.

Migration steps:
________________
Check for {location.nameRaw} and {location.cityRaw} and replace it with
{location.nameRaw -> f:format.json()} and {location.cityRaw -> f:format.json()}

All {location.* -> f:format.json()} usage may not be wrapped in " or '. If present remove those.


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
