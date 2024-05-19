.. include:: /Includes.rst.txt

.. _customEventListener:

=====================
Custom Event Listener
=====================


Adding custom event listener
============================

To add an custom listener, add the code below and replace the following and replace all the
my* with your own values, but keep the event name as it is.

.. code-block:: php
   :caption: EXT:your_extension/Classes/EventListener/MapGetSpecialLocationsListener.php

   namespace MyVendor\MyExtension\EventListener;

   use Evoweb\StoreFinder\Controller\Event\MapGetLocationsByConstraintsEvent;
   use TYPO3\CMS\Core\Attribute\AsEventListener;

   class MapGetSpecialLocationsListener
   {
      #[AsEventListener('myextension_mapcontroller_fetchspeciallocations', MapGetLocationsByConstraintsEvent::class)]
      public function __invoke(MapGetLocationsByConstraintsEvent $event): void
      {
      }
   }
