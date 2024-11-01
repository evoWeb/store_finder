..  include:: /Includes.rst.txt
..  index:: View
..  _view:

====
View
====

..  confval-menu::
    :name: view-reference
    :display: table
    :type:

    ..  _templateRootPaths:

    ..  confval:: templateRootPaths
        :type: array of :ref:`path <t3tsref:data-type-path>`

        This defines in which path the templates are stored. This is needed to
        modify the template without modifying files in the extension.

        .. code-block:: typoscript
           :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

           plugin.tx_storefinder.view {
               templateRootPaths {
                   100 = EXT:my_extension/Resources/Private/Templates/StoreFinder
               }
           }

    ..  _partialRootPaths:

    ..  confval:: partialRootPaths
        :type: array of :ref:`path <t3tsref:data-type-path>`

        This defines in which path the partials are stored. This is needed to
        modify the partials without modifying files in the extension.

        .. code-block:: typoscript
           :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

           plugin.tx_storefinder.view {
               partialRootPaths {
                   100 = EXT:my_extension/Resources/Private/Partials/StoreFinder
               }
           }
