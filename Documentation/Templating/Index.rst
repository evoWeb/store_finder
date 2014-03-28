.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _templating:

Templating
----------

Templates, partials and layouts
_______________________________

Like every other extbase extension its possible to configure the fluid
templates, partials and layout path via typoscript. Beside that is also
possible to configure the templates and partials path in the plugin.

Example:
========

::

    plugin.tx_storefinder.view {
        templateRootPath =
        partialRootPath =
        layoutRootPath =
    }

Viewhelper
__________

Beside the default viewhelpers of fluid the extension comes with three
additional viewhelpers. These are used to render the search like the
form.selectCountries viewhelper, the map like the minify viewhelper and
select what to render based on the configuration like the
format.binaryAnd viewhelper.

They are used by including the namespace in the file in which the
viewhelper get used.

Register Namespace:
===================

::

    {namespace sf=Evoweb\StoreFinder\ViewHelpers}

minify Viewhelper
_________________

This viewhelper gets used to minify the rendered json of the locations
in the result map. The purpose is to reduce the traffic and clean the
source code.
So in stead of making the template unreadable the output gets minified
on rendering time.

Example viewhelper:
===================

::

    var mapConfiguration = {<sf:minify>active: true,
            <f:for each="{settings.mapConfiguration}" as="configuration" key="name" iteration="loop">{name}: '{configuration}',</f:for>
        center: {
            lat: <f:format.number decimals="7">{center.latitude}</f:format.number>,
            lng: <f:format.number decimals="7">{center.longitude}</f:format.number>
        },
        zoom: '{center.zoom}'
    </sf:minify>};

Example output:
===============

::

    var mapConfiguration = {active:true,apiV3Layers:'',language:'de',allowSensore:'1',center:{lat:50.1125089,lng:8.6521548},zoom:'11'}

format.binaryAnd Viewhelper
___________________________

To be able to select which partial should be rendered its necessary to
compare with binary and if the part is check in the plugin. As the f:if
viewhelper is not able to do so, a special viewhelper is needed for that.

Basicly what this means is, that the setting value, in this case
showBeforeSearch, is formated with a logical and for comparison like in
the example below. Here we check if the list should be rendered because
in the plugin the binary value 4 stands for list.

Example viewhelper:
===================

::

    <f:if condition="{sf:format.binaryAnd(base: 4, content: settings.showBeforeSearch)} == 4">...</f:if>


form.selectCounrtries
_____________________

The countries select viewhelper fetches the countries from
static_info_tables and renders each country as option. All attributes from
the fluid standard form.select are supported. Beside that if the optional
attribute allowedCountries is set, only countries matching it get rendered.
allowedCountries accepts a comma seperated list of ISO2 country codes.

Example viewhelper:
===================

::

    <sf:form.selectCountries property="country" id="sfrCountry" optionValueField="isoCodeA3" allowedCountries="{0: 'DE', 1: 'AT'}" />

