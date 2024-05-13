.. include:: /Includes.rst.txt

.. _settings:

========
Settings
========

.. contents::
   :local:

.. _settings-showBeforeSearch:

showBeforeSearch
================

:aspect:`Property`
         showBeforeSearch

:aspect:`Data type`
         :ref:`integer <t3tsref:data-type-integer>`

:aspect:`Description`
         Defines what should get displayed before the search was triggered

:aspect:`Default`
         1

:aspect:`Possible values`
         1 & 2 & 4


.. _settings-showAfterSearch:

showAfterSearch
===============

:aspect:`Property`
         showBeforeSearch

:aspect:`Data type`
         :ref:`integer <t3tsref:data-type-integer>`

:aspect:`Description`
         Defines what should get displayed after the search was triggered

:aspect:`Default`
         6

:aspect:`Possible values`
         1 & 2 & 4


.. _settings-apiV3Layers:

apiV3Layers
===========

:aspect:`Property`
         mapConfiguration.apiV3Layers

:aspect:`Data type`
         list

:aspect:`Description`
         Select which layers should be rendered in the map

:aspect:`Default`
         none

:aspect:`Possible values`
         traffic, bicycling, kml


.. _limit:

limit
=====

:aspect:`Property`
         limit

:aspect:`Data type`
         integer

:aspect:`Description`
         //@todo check if still needed List of country ISO2 codes that may be rendered in country select of search form

:aspect:`Default`
         20


.. _allowedCountries:

allowedCountries
================

:aspect:`Property`
         allowedCountries

:aspect:`Data type`
         list

:aspect:`Description`
         List of country ISO2 codes that may be rendered in country select of search form

:aspect:`Default`
         none


.. _categories:

categories
==========

:aspect:`Property`
         categories

:aspect:`Data type`
         list

:aspect:`Description`
         List of categories as base to render category tree in search form

:aspect:`Default`
         none


.. _categoryPriority:

categoryPriority
================

:aspect:`Property`
         categoryPriority

:aspect:`Data type`
         string

:aspect:`Description`
         List of categories as base to render category tree in search form

:aspect:`Default`
         useAsFilterInFrontend

:aspect:`Possible values`
         useAsFilterInFrontend, useParentIfNoFilterSelected, limitResultsToCategories


.. _singleLocationId:

singleLocationId
================

:aspect:`Property`
         singleLocationId

:aspect:`Data type`
         integer

:aspect:`Description`
         Id of an single location record to get rendered in map without search query

:aspect:`Default`
         none


.. _geocoderProvider:

geocoderProvider
================

:aspect:`Property`
         geocoderProvider

:aspect:`Data type`
         string

:aspect:`Description`
         Contains class name of geocoding provider to enable changing to different services

:aspect:`Default`


.. _apiConsoleKey:

apiConsoleKey
=============

:aspect:`Property`
         apiConsoleKey

:aspect:`Data type`
         string

:aspect:`Description`
         Used for geocoding and reverse geocoding of addresses via Google Maps Geocoding API. Must have access for Google Maps Geocoding API and can only be restricted by ip addresses.

:aspect:`Default`


.. _apiConsoleKeyGeocoding:

apiConsoleKeyGeocoding
======================

:aspect:`Property`
         apiConsoleKeyGeocoding

:aspect:`Data type`
         string

:aspect:`Description`
         Used for output map via Google Maps JavaScript API. Must have access for Google Maps JavaScript API and can only be restricted by domains.

:aspect:`Default`


.. _distanceUnit:

distanceUnit
============

:aspect:`Property`
         distanceUnit

:aspect:`Data type`
         string

:aspect:`Description`
         Base of distance values given in range select of search form. If miles is set the range gets multiplied with 1.6

:aspect:`Default`
         kilometer

:aspect:`Possible values`
         miles, kilometer


.. _mc-language:

language
========

:aspect:`Property`
         mapConfiguration.language

:aspect:`Data type`
         string

:aspect:`Description`
         ISO2 definition for language to use by google map

:aspect:`Default`
         en


.. _showStoreImage:

showStoreImage
==============

:aspect:`Property`
         showStoreImage

:aspect:`Data type`
         boolean

:aspect:`Description`
         If set the location store image gets rendered in result mapBubble

:aspect:`Default`
         1


.. _resultPageId:

resultPageId
============

:aspect:`Property`
         resultPageId

:aspect:`Data type`
         integer

:aspect:`Description`
         If set the search result gets rendered on a different page

:aspect:`Default`
         none


.. _ms-height:

mapSize.height
==============

:aspect:`Property`
         mapSize.height

:aspect:`Data type`
         integer

:aspect:`Description`
      :aspect:`Default` height of map used in inline style

:aspect:`Default`
         400


.. _ms-width:

mapSize.width
=============

:aspect:`Property`
         mapSize.width

:aspect:`Data type`
         integer

:aspect:`Description`
      :aspect:`Default` width of map used in inline style

:aspect:`Default`
         600


.. _override:

override
========

:aspect:`Property`
         override

:aspect:`Data type`
         array

:aspect:`Description`
         Sometimes the admin want to restrict configuration available in the flexform.
         With the override its possible to define values that should override the configuration done in the flexform.


.. _disableFetchLocationInAction:

disableFetchLocationInAction
============================

:aspect:`Property`
         disableFetchLocationInAction

:aspect:`Data type`
         array of strings

:aspect:`Description`
         Disabling the fetching of locations based on constraints individually.
         This disables the fetching of locations in map, cachedMap and search action.
         Use this only in combination with a listener for MapGetLocationsByConstraintsEvent or you do not get any output at all.

         0 = map
             this is the default map action

         1 = cachedMap
             this is the cached map action

         2 = search
             this is the search action

:aspect:`Example`
   .. code-block:: typoscript
      :caption: EXT:site_package/Configuration/TypoScript/setup.typoscript

      disableFetchLocationInAction {
        0 = map
        1 = cachedMap
        2 = search
      }
