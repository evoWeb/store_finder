.. include:: /Includes.rst.txt

.. _eventlistener:

==============
Event listener
==============


Introduction
============

It is possible to use various custom event listener to use custom location loading logic. If you need
a modified sorting or locations based on other constraints then the provided, it is possible to override
the loading completly.


Adding event listeners
======================

Add the following code to the Configuration/Services.yaml of your sitepackage to use the example listener.

.. code-block:: typoscript
   :caption: EXT:my_extension/Configuration/Services.yaml

   services:
     Evoweb\StoreFinder\EventListener\MapGetAllLocationsListener:
       tags:
         - name: event.listener
           identifier: 'storefinder_mapcontroller_locationsfetched'
           method: 'onLocationsFetchedEvent'
           event: Evoweb\StoreFinder\Controller\Event\MapGetLocationsByConstraintsEvent

More explanation for your custom event listener can be found in the `tutorial <_customEventListener>`

Additional configuration
========================

Performance improvement
-----------------------

When using event listeners it's adviced to set typoscript setup
`plugin.tx_storefinder.settings.disableLocationFetchLogic = 1`
to improve performance. By doing so, all default fetching will be disabled.

.. code-block:: typoscript
   :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

   plugin.tx_storefinder.settings.disableLocationFetchLogic = 1

Listener must follow the symfony/event concept [https://symfony.com/doc/current/event_dispatcher.html]

Radius not automatically detected
---------------------------------

If you use a custom location loading mechanism, the location::distance property will be emtpy. Atleast if you do not
fill them on your own. In case the distance is empty the default zoom level detection has nothing to work with. In
this case, you need to set the zoom level in the template OR the radius in TypoScript

.. code-block:: typoscript
   :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

   plugin.tx_storefinder.settings.defaultConstraint.radius = 20

Modify locations in StoreFinderMiddleware
=========================================

It's possible to change locations records from store finder middleware before
sending the json response. Use the ModifyMiddlewareLocationsListener as an
example of how to change values.

.. code-block:: Services.yaml
  Evoweb\StoreFinder\EventListener\ModifyMiddlewareLocationsListener:
    tags: ['event.listener']
