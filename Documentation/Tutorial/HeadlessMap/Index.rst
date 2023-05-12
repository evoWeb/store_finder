.. include:: /Includes.rst.txt

.. _headlessMapRendering:

======================
Headless Map Rendering
======================


Introduction of middleware
==========================

With the introduction of the StoreFinderMiddleware it's possible to integrate a
headless version of the map.

The middleware provides categories and locations. In addition its possible to
use location and fulltext search in combination with category filtering.

Only the endpoints are provided. You need to implement the javascript yourself.

Template example
================

Below is an example on how to use the configuration and endpoints with your
custom code.

.. code-block:: html
   :caption: Map.html

    <store-finder-map
        showSearch="{sf:bitwiseIf(a: settings.showBeforeSearch, b: 1, then: 1, else: 0)}"
        showList="{sf:bitwiseIf(a: settings.showBeforeSearch, b: 4, then: 1, else: 0)}"
        categoriesEndpoint="{f:uri.typolink(parameter: '/api/storefinder/categories/{cObjectData.uid}')}"
        locationsEndpoint="{f:uri.typolink(parameter: '/api/storefinder/locations/{cObjectData.uid}')}"/>
