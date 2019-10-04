.. include:: ../../Includes.txt


.. _geocoding:

Geocoding provider
------------------

.. contents::
   :local:
   :depth: 2

Changing geocoding provider
===========================

By using the geocoder-php/geocoder package it's possible to change the geocoding provider.

As default the geocoder-php/google-maps-provider package is required. But it's possible to
require different packages in the project composer.json. A list of available provider can be
found here [https://github.com/geocoder-php/Geocoder#address]

This can be achieved by

::

   composer require geocoder-php/nominatim-provider


Extension configuration
=======================

If you choose to use a different providers it's important to set the provider classname in the
field geocoderProvider:

::

   Geocoder\Provider\Nominatim\Nominatim

TypoScript constants
====================

If you want to use the additional provider for geocoding search results too, you need to change the constant

::

   plugin.tx_storefinder.geocoderProvider = Geocoder\Provider\Nominatim\Nominatim
