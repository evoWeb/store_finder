..  include:: /Includes.rst.txt
..  index:: Settings
..  _settings:

========
Settings
========

..  confval-menu::
    :name: settings-reference
    :display: table
    :type:
    :Default:

    ..  _showBeforeSearch:

    ..  confval:: showBeforeSearch
        :type: :ref:`integer <t3tsref:data-type-integer>`
        :Default: 1
        :Possible values: 1 & 2 & 4

        Defines what should get displayed before the search was triggered. Must
        be used as binary flags

        - 1 show search
        - 2 show map
        - 4 show list

    ..  _showAfterSearch:

    ..  confval:: showAfterSearch
        :type: :ref:`integer <t3tsref:data-type-integer>`
        :Default: 6
        :Possible values: 1 & 2 & 4

        Defines what should get displayed after the search was triggered. Must
        be used as binary flags

        - 1 show search
        - 2 show map
        - 4 show list

    ..  _apiV3Layers:

    ..  confval:: apiV3Layers
        :type: list of :ref:`string <t3tsref:data-type-string>`
        :Possible values: traffic, bicycling, kml

        Select which layers should be rendered in the map

    ..  _limit:

    ..  confval:: limit
        :type: :ref:`integer <t3tsref:data-type-integer>`
        :Default: 20

        Amount of records per page in the result list

    ..  _allowedCountries:

    ..  confval:: allowedCountries
        :type: list of :ref:`string <t3tsref:data-type-string>`

        List of country ISO2 codes that may be rendered in country select of search form

    ..  _categories:

    ..  confval:: categories
        :type: list of :ref:`integer <t3tsref:data-type-integer>`

        List of categories as base to render category tree in search form

    ..  _categoryPriority:

    ..  confval:: categoryPriority
        :type: :ref:`string <t3tsref:data-type-string>`
        :Default: useAsFilterInFrontend
        :Possible values: useAsFilterInFrontend, useParentIfNoFilterSelected, limitResultsToCategories

        List of categories as base to render category tree in search form

    ..  _singleLocationId:

    ..  confval:: singleLocationId
        :type: :ref:`integer <t3tsref:data-type-integer>`

        Id of an single location record to get rendered in map without search query

    ..  _geocoderProvider:

    ..  confval:: geocoderProvider
        :type: :ref:`string <t3tsref:data-type-string>`

        Contains class name of geocoding provider to enable changing to different services

    ..  _apiConsoleKey:

    ..  confval:: apiConsoleKey
        :type: :ref:`string <t3tsref:data-type-string>`

        Used for geocoding and reverse geocoding of addresses via Google Maps
        Geocoding API. Must have access for Google Maps Geocoding API and can
        only be restricted by ip addresses.

    ..  _apiConsoleKeyGeocoding:

    ..  confval:: apiConsoleKeyGeocoding
        :type: :ref:`string <t3tsref:data-type-string>`

        Used for output map via Google Maps JavaScript API. Must have access for
        Google Maps JavaScript API and can only be restricted by domains.

    ..  _mapId:

    ..  confval:: mapId
        :type: :ref:`string <t3tsref:data-type-string>`

        Map id to identify the map and configure it's rendering

    ..  _googleLibraries:

    ..  confval:: googleLibraries
        :type: list of :ref:`string <t3tsref:data-type-string>`

        Used to define what modules should be loaded. The modules core, map and marker are always added this list.

    ..  _distanceUnit:

    ..  confval:: distanceUnit
        :type: :ref:`string <t3tsref:data-type-string>`
        :Default: kilometer
        :Possible values: kilometer, miles

        Base of distance values given in range select of search form. If miles is set the range gets multiplied with 1.6

    ..  _language:

    ..  confval:: language
        :type: :ref:`string <t3tsref:data-type-string>`
        :Default: en
        :Possible values: All possible ISO2 language values

        ISO2 definition for language to use by google map

    ..  _showStoreImage:

    ..  confval:: showStoreImage
        :type: :ref:`boolean <t3tsref:data-type-boolean>`
        :Default: 1

        If set the location store image gets rendered in result mapBubble

    ..  _resultPageId:

    ..  confval:: resultPageId
        :type: :ref:`integer <t3tsref:data-type-integer>`

        If set the search result gets rendered on a different page. If empty
        the current page is used

    ..  _mapSize-height:

    ..  confval:: mapSize.height
        :type: :ref:`integer <t3tsref:data-type-integer>`
        :Default: 400

        Default height of map used in inline style

    ..  _mapSize-width:

    ..  confval:: mapSize.width
        :type: :ref:`integer <t3tsref:data-type-integer>`
        :Default: 600

        Default width of map used in inline style

    ..  _override:

    ..  confval:: override
        :type: array of options
        :Default: 600

        Sometimes the admin want to restrict configuration available in the flexform.
        With the override its possible to define values that should override the configuration done in the flexform.

    ..  _disableFetchLocationInAction:

    ..  confval:: disableFetchLocationInAction
        :type: array of :ref:`string <t3tsref:data-type-string>`
        :Possible values: map, cachedMap, search

        Disabling the fetching of locations based on constraints individually.
        This disables the fetching of locations in map, cachedMap and search action.
        Use this only in combination with a listener for
        MapGetLocationsByConstraintsEvent or you do not get any output at all.

        - **map** the default map action
        - **cachedMap** the cached map action
        - **search** the search action

        :Example:

        ..  code-block:: typoscript
            :caption: EXT:site_package/Configuration/TypoScript/setup.typoscript

            disableFetchLocationInAction {
                0 = map
                1 = cachedMap
                2 = search
            }
