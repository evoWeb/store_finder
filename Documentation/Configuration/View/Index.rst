.. include:: /Includes.rst.txt
.. index:: Configuration
.. _view:

====
View
====

.. _view-templateRootPaths:

templateRootPaths
=================

:aspect:`Property`
   templateRootPaths

:aspect:`Data type`
   array of file paths

:aspect:`Description`
   This defines in which path the templates are stored. This is needed to modify the template without modifing files in the extension.

.. code-block:: typoscript
   :caption: EXT:site_package/Configuration/TypoScript/setup.typoscript

   plugin.tx_storefinder.view {
       templateRootPaths {
           100 = EXT:site_package/Resources/Private/Templates/StoreFinder
       }
   }


.. _view-partialRootPaths:

partialRootPaths
================

:aspect:`Property`
   partialRootPaths

:aspect:`Data type`
   array of file paths

:aspect:`Description`
   This defines in which path the partials are stored. This is needed to modify the partials without modifing files in the extension.

.. code-block:: typoscript
   :caption: EXT:site_package/Configuration/TypoScript/setup.typoscript

   plugin.tx_storefinder.view {
       partialRootPaths {
           100 = EXT:site_package/Resources/Private/Partials/StoreFinder
       }
   }
