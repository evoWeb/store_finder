.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _configuration:

Breaking Changes
----------------

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
