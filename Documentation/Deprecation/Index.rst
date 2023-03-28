.. include:: /Includes.rst.txt

.. _deprecation:

===========
Deprecation
===========

Version 6.0.0
=============

Replaced ViewHelper
-------------------
The binary handling is replaced in the Partials/Map.html

.. code-block:: html
   :caption: Before

   <f:if condition="{sf:format.binaryAnd(base: 1, content: settings.showBeforeSearch)} == 1">


.. code-block:: html
   :caption: After

   <sf:bitwiseIf a="{comparisonValue}" b="1">


Removed ViewHelper
------------------
The `<sf:removeEscaping>` ViewHelper is not used any more and will get removed
