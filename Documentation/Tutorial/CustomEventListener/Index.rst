.. include:: /Includes.rst.txt
.. index:: Tutorial
.. _customEventListener:

=====================
Custom Event Listener
=====================


Adding custom event listener
============================

To add an custom listener, add the code below and replace the following and replace all the
my* with your own values, but keep the event name as it is.

.. code-block:: typoscript
   :caption: EXT:site_package/Configuration/Services.yaml

   services:
     MyVendor\MyExtension\EventListener\MapGetSpecialLocationsListener:
       tags:
         - name: event.listener
           identifier: 'myextension_mapcontroller_fetchspeciallocations'
           method: 'onFetchSpecialLocations'
           event: Evoweb\StoreFinder\Controller\Event\MapGetLocationsByConstraintsEvent
