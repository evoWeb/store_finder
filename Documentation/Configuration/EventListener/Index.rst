.. include:: /Includes.rst.txt
.. index:: Configuration
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
   :caption: EXT:site_package/Configuration/Services.yaml

   services:
     Evoweb\StoreFinder\EventListener\MapGetAllLocationsListener:
       arguments: ['@Evoweb\StoreFinder\Domain\Repository\LocationRepository']
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

When using event listeners it's advised to add the desired action names to
`plugin.tx_storefinder.settings.disableFetchLocationInAction {}`
to improve performance. By doing so, all default fetching will be disabled
in individually selected actions.

.. code-block:: typoscript
   :caption: EXT:site_package/Configuration/TypoScript/setup.typoscript

   plugin.tx_storefinder.settings.disableFetchLocationInAction {
     0 = map
     1 = cachedMap
     2 = search
   }

Listener must follow the symfony/event concept [https://symfony.com/doc/current/event_dispatcher.html]

Radius not automatically detected
---------------------------------

If you use a custom location loading mechanism, the location::distance property will be emtpy. Atleast if you do not
fill them on your own. In case the distance is empty the default zoom level detection has nothing to work with. In
this case, you need to set the zoom level in the template OR the radius in TypoScript

.. code-block:: typoscript
   :caption: EXT:site_package/Configuration/TypoScript/setup.typoscript

   plugin.tx_storefinder.settings.defaultConstraint.radius = 20
