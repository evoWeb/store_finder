.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _tutorial:

Tutorial
--------


Installation
============

To install the extension go to the "Admin Tools" section and select the "Extension Manager". On the top
selectbox choose "Manage Extensions" and enter store_finder in the search field. Click add to install
the extensions.

In case your organization have a api console key click the cog afterwards and enter the code in the
configuration field.

In rare case that the api url changes and the extensions was not update quick enough you can change the
url used for the geocooding in the same configuration. Be aware that this url only gets used in the
geocoding process. For the search in the frontend you need to change it in TypoScript too.


TypoScript
==========

In general its a good idea to add the include static of the extension to you typoscript record. Otherwise
its not possible to use the country selector which is used in the default template.


Structure
=========

Location records can only be added in folders thats why you need to add atleast one folder for storage.
Additionaly you need a page with the store_finder plugin. In the plugin configure your needs. To use
locations of the created folder choose it as record storage page on tab behaviour.


Templating
==========

To change the frontend templates you most likely need to change the partials. These contain of search, map
and list part. Copy the folder from the extension to your local path like the fileadmin. To use these copies
you can change the path either in the plugin field "Partial path" or via TypoScript:


::

	plugin.tx_storefinder.view.partialRootPath = ./yourpath/Partials/

