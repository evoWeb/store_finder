.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _configuration:

Configuration
-------------

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   Validation/Index

.. contents::
   :local:
   :depth: 1


plugin.tx_storefinder.settings
==============================

.. container:: ts-properties

  ===================================================== ================================================= ========== ========== ========== ==========
  Property                                              Data types                                        TypoScript Flexform   stdWrap    Default
  ===================================================== ================================================= ========== ========== ========== ==========
  showBeforeSearch_                                     :ref:`data-type-integer`                          yes        yes        no         1
  showAfterSearch_                                      :ref:`data-type-integer`                          yes        yes        no         6
  `mapConfiguration.apiV3Layers <mc-apiV3Layers_>`_     :ref:`data-type-list`                             yes        yes        no         -
  limit_                                                :ref:`data-type-integer`                          yes        yes        no         -
  allowedCountries_                                     :ref:`data-type-list`                             yes        yes        no         -
  categories_                                           :ref:`data-type-list`                             yes        yes        no         -
  categoryPriority_                                     :ref:`data-type-string`                           yes        yes        no         useAsFilterInFrontend
  singleLocationId_                                     :ref:`data-type-integer`                          yes        yes        no         -

  geocodeUrl_                                           :ref:`data-type-string`                           yes                   no         service url
  distanceUnit_                                         :ref:`data-type-string`                           yes                   no         miles
  `mapConfiguration.language <mc-language_>`_           :ref:`data-type-string`                           yes                   no         en
  `mapConfiguration.allowSensors <mc-allowSensors_>`_   :ref:`data-type-boolean`                          yes                   no         1

  showStoreImage_                                       :ref:`data-type-boolean`                          yes        yes        no         1
  resultPageId_                                         :ref:`data-type-integer`                          yes        yes        no         -
  routePageId_                                          :ref:`data-type-integer`                          yes        yes        no         -
  `mapSize.height <ms-height_>`_                        :ref:`data-type-integer`                          yes                   no         400
  `mapSize.width <ms-width_>`_                          :ref:`data-type-integer`                          yes                   no         600
  override_                                             :ref:`data-type-array`                            yes                   no         -
  ===================================================== ================================================= ========== ========== ========== ==========


plugin.tx_storefinder.view
==========================

.. container:: ts-properties

  ===================================================== ================================================= ========== ========== ========== ==========
  Property                                              Data types                                        TypoScript Flexform   stdWrap    Default
  ===================================================== ================================================= ========== ========== ========== ==========
  templateRootPath_                                     :ref:`data-type-string`                           yes        yes        no         -
  partialRootPath_                                      :ref:`data-type-string`                           yes        yes        no         -
  ===================================================== ================================================= ========== ========== ========== ==========


plugin.tx_storefinder.persistence
=================================

.. container:: ts-properties

  ===================================================== ================================================= ========== ========== ========== ==========
  Property                                              Data types                                        TypoScript Flexform   stdWrap    Default
  ===================================================== ================================================= ========== ========== ========== ==========
  storagePid_                                           :ref:`data-type-integer`                          yes                   no         -
  ===================================================== ================================================= ========== ========== ========== ==========


.. _showBeforeSearch:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         showBeforeSearch

   Data type
         integer

   Description
         Defines what should get displayed before the search was triggered

   Default
         1

   Possible values:
         1 & 2 & 4


.. _showAfterSearch:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         showBeforeSearch

   Data type
         integer

   Description
         Defines what should get displayed after the search was triggered

   Default
         6

   Possible values:
         1 & 2 & 4


.. _mc-apiV3Layers:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         mapConfiguration.apiV3Layers

   Data type
         list

   Description
         Select which layers should be rendered in the map

   Default
         none

   Possible values:
         traffic, weather, bicycling, panoramio, kml


.. _limit:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         limit

   Data type
         integer

   Description
         //@todo check if still needed List of country ISO2 codes that may be rendered in country select of search form

   Default
         20


.. _allowedCountries:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         allowedCountries

   Data type
         list

   Description
         List of country ISO2 codes that may be rendered in country select of search form

   Default
         none


.. _categories:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         categories

   Data type
         list

   Description
         List of categories as base to render category tree in search form

   Default
         none


.. _categoryPriority:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         categoryPriority

   Data type
         string

   Description
         List of categories as base to render category tree in search form

   Default
         useAsFilterInFrontend

   Possible values:
         useAsFilterInFrontend, useParentIfNoFilterSelected, limitResultsToCategories


.. _singleLocationId:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         singleLocationId

   Data type
         integer

   Description
         Id of an single location record to get rendered in map without search query

   Default
         none


.. _geocodeUrl:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         geocodeUrl

   Data type
         string

   Description
         If google changes the service url with this configuration its possible to query the service again.
         This setting only works for frontend actions. To encode locations on save in backend please refere to extension manager setting with same key.

   Default
         http://maps.googleapis.com/maps/api/geocode/json?sensor=false


.. _distanceUnit:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         distanceUnit

   Data type
         string

   Description
         Base of distance values given in range select of search form. If miles is set the range gets multiplied with 1.6

   Default
         miles

   Possible values:
         miles, kilometer


.. _mc-language:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         mapConfiguration.language

   Data type
         string

   Description
         ISO2 definition for language to use by google map

   Default
         en


.. _mc-allowSensors:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         mapConfiguration.allowSensors

   Data type
         boolean

   Description
         Defines if browser sensors may get used or not

   Default
         1


.. _showStoreImage:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         showStoreImage

   Data type
         boolean

   Description
         If set the location store image gets rendered in result mapBubble

   Default
         1


.. _resultPageId:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         resultPageId

   Data type
         integer

   Description
         If set the search result gets rendered on a different page

   Default
         none


.. _routePageId:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         routePageId

   Data type
         integer

   Description
         If set the routing result gets display on a different page

   Default
         none


.. _ms-height:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         mapSize.height

   Data type
         integer

   Description
         Default height of map used in inline style

   Default
         400


.. _ms-width:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         mapSize.width

   Data type
         integer

   Description
         Default width of map used in inline style

   Default
         600


.. _override:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         override

   Data type
         array

   Description
         Sometimes the admin want to restrict configuration available in the flexform. With the override its possible to define values that should override the configuration done in the flexform.


.. _templateRootPath:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         templateRootPath

   Data type
         integer

   Description
         This defines in which path the templates are stored. This is needed to modify the template without tempering the extension


.. _partialRootPath:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         partialRootPath

   Data type
         integer

   Description
         This defins in which path the partials are stored. This is needed to modify the partials without tempering the extension


.. _storagePid:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         storagePid

   Data type
         integer

   Description
         This defines the storage page id. In flexform please use the record storage page of the plugin.