..  include:: /Includes.rst.txt
..  index:: Event listener
..  _eventlistener:

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

..  code-block:: php
    :caption: MapGetLocationsByConstraintsEventListener.php

    namespace Evoweb\StoreFinder\EventListener;

    use Evoweb\StoreFinder\Controller\Event\MapGetLocationsByConstraintsEvent;
    use TYPO3\CMS\Core\Attribute\AsEventListener;

    class MapGetLocationsByConstraintsEventListener
    {
        #[AsEventListener('storefinder_mapcontroller_locationsfetched', MapGetLocationsByConstraintsEvent::class)]
        public function __invoke(MapGetLocationsByConstraintsEvent $event): void
        {
        }
    }

More explanation for your custom event listener can be found in the `tutorial <_customEventListener>`

Additional configuration
========================

Performance improvement
-----------------------

When using event listeners it's adviced to set typoscript setup
`plugin.tx_storefinder.settings.disableLocationFetchLogic = 1`
to improve performance. By doing so, all default fetching will be disabled.

..  code-block:: typoscript
    :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

    plugin.tx_storefinder.settings.disableLocationFetchLogic = 1

Listener must follow the symfony/event concept [https://symfony.com/doc/current/event_dispatcher.html]

Radius not automatically detected
---------------------------------

If you use a custom location loading mechanism, the location::distance property will be emtpy. Atleast if you do not
fill them on your own. In case the distance is empty the default zoom level detection has nothing to work with. In
this case, you need to set the zoom level in the template OR the radius in TypoScript

..  code-block:: typoscript
    :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

    plugin.tx_storefinder.settings.defaultConstraint.radius = 20

Modify locations in StoreFinderMiddleware
=========================================

It's possible to change locations records from store finder middleware before
sending the json response. Use the ModifyMiddlewareLocationsListener as an
example of how to change values.

The registration got simplified in TYPO3 13.x

..  code-block:: php
    :caption: ModifyMiddlewareLocationsEventListener.php

    use Evoweb\StoreFinder\Middleware\Event\ModifyMiddlewareLocationsEvent;
    use TYPO3\CMS\Core\Attribute\AsEventListener;
    class ModifyMiddlewareLocationsEventListener
    {
        #[AsEventListener('your-extension-identifier', ModifyMiddlewareLocationsEvent::class)]
        public function __invoke(ModifyMiddlewareLocationsEvent $event): void
        {
        }
    }
