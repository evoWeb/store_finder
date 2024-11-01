..  include:: /Includes.rst.txt
..  index:: Templating
..  _templating:

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
   :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

    plugin.tx_storefinder.view {
        templateRootPath =
        partialRootPath =
        layoutRootPath =
    }

ViewHelper
==========

Beside the default ViewHelper of fluid the extension comes with three
additional ViewHelper. These are used to render the search like the
form.selectCountries ViewHelper, the map like the minify ViewHelper and
select what to render based on the configuration like the
format.binaryAnd ViewHelper.

They are used by including the namespace in the file in which the
ViewHelper get used.

Register Namespace
==================

Add the xmlns to the html tag in the template

.. code-block:: html
   :caption: EXT:my_extension/Resources/Private/Templates/Map/Map.html

    xmlns:sf="http://typo3.org/ns/Evoweb/StoreFinder/ViewHelpers"

minify ViewHelper
=================

This ViewHelper gets used to minify the rendered json of the locations
in the result map. The purpose is to reduce the traffic and clean the
source code.
So in stead of making the template unreadable the output gets minified
on rendering time.

Example ViewHelper
------------------

.. code-block:: html
   :caption: EXT:my_extension/Resources/Private/Templates/Map/Map.html

    var mapConfiguration = {<sf:minify>active: true,
            <f:for each="{settings.mapConfiguration}" as="configuration" key="name" iteration="loop">{name}: '{configuration}',</f:for>
        center: {
            lat: <f:format.number decimals="7">{center.latitude}</f:format.number>,
            lng: <f:format.number decimals="7">{center.longitude}</f:format.number>
        },
        zoom: '{center.zoom}'
    </sf:minify>};

Example output
--------------

.. code-block:: html
   :caption: EXT:my_extension/Resources/Private/Templates/Map/Map.html

    var mapConfiguration = {active:true,apiV3Layers:'',language:'de',center:{lat:50.1125089,lng:8.6521548},zoom:'11'}

format.binaryAnd ViewHelper
===========================

To be able to select which partial should be rendered its necessary to
compare with binary and if the part is check in the plugin. As the f:if
ViewHelper is not able to do so, a special ViewHelper is needed for that.

Basically what this means is, that the setting value, in this case
showBeforeSearch, is formatted with a logical and for comparison like in
the example below. Here we check if the list should be rendered because
in the plugin the binary value 4 stands for list.

Example ViewHelper
------------------

.. code-block:: html
   :caption: EXT:my_extension/Resources/Private/Templates/Map/Map.html

    <f:if condition="{sf:format.binaryAnd(base: 4, content: settings.showBeforeSearch)} == 4">...</f:if>

form.selectCountries
====================

The countries select ViewHelper fetches the countries from the country
provider and renders each country as option. All attributes from
the fluid standard form.select are supported. Beside that if the optional
attribute allowedCountries is set, only countries matching it get rendered.
allowedCountries accepts a comma seperated list of ISO2 country codes.

Example ViewHelper
------------------

.. code-block:: html
   :caption: EXT:my_extension/Resources/Private/Templates/Map/Map.html

    <sf:form.selectCountries property="country" id="sfrCountry"
        optionValueField="alpha2IsoCode" allowedCountries="{0: 'DE', 1: 'AT'}" />
