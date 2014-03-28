.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _configuration:

Configuration
=============

.. contents::
   :local:
   :depth: 1


plugin.tx_storefinder.settings
------------------------------

.. container:: ts-properties

  ===================================================== ================================================= ========== ========== ========== ==========
  Property                                              Data types                                        TypoScript Flexform   stdWrap    Default
  ===================================================== ================================================= ========== ========== ========== ==========
  showBeforeSearch_                                     :ref:`data-type-integer`                          yes        yes        no         0
  showAfterSearch_                                      :ref:`data-type-boolean`                          yes        yes        no         0
  mapConfiguration-apiV3Layers_                         :ref:`data-type-boolean`                          yes        yes        no         0
  notifyAdminPostCreateAccept_                          :ref:`data-type-boolean`                                                no         0
  notifyAdminPostCreateDecline_                         :ref:`data-type-boolean`                                                no         0
  acceptEmailPostCreate_                                :ref:`data-type-boolean`                                                no         0
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
         1,2,4


// @todo write config doku
plugin.tx_storefinder.settings
	showAfterSearch ts ff-6(1,2,4)
	mapConfiguration.apiV3Layers ts ff-(traffic,weather,bicycling,panoramio,kml)
	allowedCountries ts ff-(iso2)
	categories ts ff
	categoryPriority ts ff-(useAsFilterInFrontend,useParentIfNoFilterSelected,limitResultsToCategories)
	singleLocationId ts ff
	-
	geocodeUrl ts-http://maps.googleapis.com/maps/api/geocode/json?sensor=false for search geocoding / em settings for location geocoding
	limit ts 20
	distanceUnit ts-(miles,kilometer)
	mapConfiguration.language ts en
	mapConfiguration.allowSensore ts 1
	-
	showStoreImage ts ff-1(1)
	resultPageId ts ff
	routePageId ts ff
	mapSize.height ts 600
	mapSize.width ts 400
	validation ts
	override ts

plugin.tx_storefinder.view
	templateRootPath ts ff
	partialRootPath ts ff

plugin.tx_storefinder.persistence
	storagePid ts / Record Storage Page in plugin