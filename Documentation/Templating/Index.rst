.. include:: /Includes.rst.txt
.. index::
   Templating
.. _templating:

==========
Templating
==========


Templates, partials and layouts
===============================

Like every other extbase extension its possible to configure the fluid
templates, partials and layout path via typoscript. Beside that is also
possible to configure the templates and partials path in the plugin.


Example:
________

.. code-block:: typoscript
   :caption: EXT:site_package/Configuration/TypoScript/setup.typoscript

    plugin.tx_storefinder.view {
        templateRootPath =
        partialRootPath =
        layoutRootPath =
    }


Viewhelper
==========

Beside the default viewhelpers of fluid the extension comes with three
additional viewhelpers. These are used to render the search like the
form.selectCountries viewhelper, the map like the minify viewhelper and
select what to render based on the configuration like the
format.binaryAnd viewhelper.

They are used by including the namespace in the file in which the
viewhelper get used.


Register Namespace:
===================

Add the xmlns to the html tag in the template

.. code-block:: html
   :caption: EXT:site_package/Resources/Private/Templates/Map/Map.html

    xmlns:sf="http://typo3.org/ns/Evoweb/StoreFinder/ViewHelpers"


minify Viewhelper
=================

This viewhelper gets used to minify the rendered json of the locations
in the result map. The purpose is to reduce the traffic and clean the
source code.
So in stead of making the template unreadable the output gets minified
on rendering time.


Example viewhelper:
___________________

.. code-block:: html
   :caption: EXT:site_package/Resources/Private/Templates/Map/Map.html

    var mapConfiguration = {<sf:minify>active: true,
            <f:for each="{settings.mapConfiguration}" as="configuration" key="name" iteration="loop">{name}: '{configuration}',</f:for>
        center: {
            lat: <f:format.number decimals="7">{center.latitude}</f:format.number>,
            lng: <f:format.number decimals="7">{center.longitude}</f:format.number>
        },
        zoom: '{center.zoom}'
    </sf:minify>};


Example output:
_______________

.. code-block:: html
   :caption: EXT:site_package/Resources/Private/Templates/Map/Map.html

    var mapConfiguration = {active:true,apiV3Layers:'',language:'de',center:{lat:50.1125089,lng:8.6521548},zoom:'11'}


format.binaryAnd Viewhelper
===========================

To be able to select which partial should be rendered its necessary to
compare with binary and if the part is check in the plugin. As the f:if
viewhelper is not able to do so, a special viewhelper is needed for that.

Basically what this means is, that the setting value, in this case
showBeforeSearch, is formatted with a logical and for comparison like in
the example below. Here we check if the list should be rendered because
in the plugin the binary value 4 stands for list.


Example viewhelper:
___________________

.. code-block:: html
   :caption: EXT:site_package/Resources/Private/Templates/Map/Map.html

    <f:if condition="{sf:format.binaryAnd(base: 4, content: settings.showBeforeSearch)} == 4">...</f:if>


form.selectCounrtries
=====================

The countries select viewhelper fetches the countries from
static_info_tables and renders each country as option. All attributes from
the fluid standard form.select are supported. Beside that if the optional
attribute allowedCountries is set, only countries matching it get rendered.
allowedCountries accepts a comma seperated list of ISO2 country codes.


Example viewhelper:
___________________

.. code-block:: html
   :caption: EXT:site_package/Resources/Private/Templates/Map/Map.html

    <sf:form.selectCountries property="country" id="sfrCountry" optionValueField="isoCodeA3" allowedCountries="{0: 'DE', 1: 'AT'}" />

