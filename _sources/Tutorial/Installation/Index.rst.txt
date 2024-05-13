.. include:: /Includes.rst.txt

.. _installation:

============
Installation
============


Installation
============

To install the extension go to the "Admin Tools" section and select the "Extension Manager". On the top
selectbox choose "Manage Extensions" and enter store_finder in the search field. Click add to install
the extensions.

In case your organization have a api console key click the cog afterwards and enter the code in the
configuration field.

In rare case that the api url changes and the extensions was not update quick enough you can change the
url used for the geocooding in the same configuration. Be aware that this url only gets used in the
geocoding process. For the search in the frontend you need to change it in TypoScript too.


TypoScript
==========

In general its a good idea to add the include static of the extension to you typoscript record. Otherwise
its not possible to use the country selector which is used in the default template.


Structure
=========

Location records can only be added in folders that's why you need to add at least one folder for storage.
Additionally you need a page with the store_finder plugin. In the plugin configure your needs. To use
locations of the created folder choose it as record storage page on tab behaviour.


Templating
==========

To change the frontend templates you most likely need to change the partials. These contain of search, map
and list part. Copy the folder from the extension to your local path like the fileadmin. To use these copies
you can change the path either in the plugin field "Partial path" or via TypoScript:

.. code-block:: typoscript
   :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

   plugin.tx_storefinder.view.partialRootPaths.50 = ./yourpath/Partials/


API Keys
========

If you need to geocode coordinates, either on saving a store record or in bulk with the geocode console command
you need to add an additional key in TypoScript constants apiConsoleKeyGeocoding. This key will never be visible
to any visitors of your page.

There are two keys needed because the key used in geocoding may not be restricted at all. But you also dont
want to deliver pages with an key that is not restricted to the domain in frontend rendering, because then it
would be possible that others use the key and by that causes you to pay for usage that wasn't by your page.

So, it's advised to have both keys
 - apiConsoleKey (protected by restriction for your domains)
 - apiConsoleKeyGeocoding (unrestricted for the usage in php)

To obtain a key please visit https://developers.google.com/maps/gmp-get-started


Set default coordinates
=======================

In TypoScript setup it's possible to set defaultConstraints these are filled in the contraints object if no
search was requested. In the example below the zoom, latitude and longitude values are set and then the
coordinates are used to render search results that are near of them.

.. code-block:: typoscript
   :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript:

   plugin.tx_storefinder.settings {
      showLocationsForDefaultConstraint = 1
      defaultConstraint {
         zoom = 15
         latitude = 51.5135
         longitude = 7.464
      }
   }


Use caching map action
======================

As of version 6.1.0 a cached map action is available. To use it with custom templates it importand to
copy the ``Templates/Map/CachedMap.html`` to your sitepackage.

In addition to that it's importand to add the ``<sf:cache location="{location}"/>`` ViewHelper to the
``Partials/Locations.html`` to be able to clear cache on additing a location record without manually click
the clear cache flash.
