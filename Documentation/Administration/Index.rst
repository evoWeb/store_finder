.. include:: /Includes.rst.txt
.. index::
   Administration
.. _administration:

==============
Administration
==============

The part for administrator is fairly simple. Just go to the extension manager,
switch to "Get extensions" and enter "store_finder" in the search field on top.
Install the extensions and you are done for a new installation.

If you use a different google maps key, like for a business account, you need to
configure the extension in the em. Just hit the gear on the line of the
installed store_finder and enter the api console key in the designated field.

In case the google maps geocode url changes and the extension has no update for,
that the url can be changed in the same configuration part. Just enter the url
in the field Url used for geocode.


Configuration:
==============

.. figure:: Images/admin_config.png
   :alt: Search form
   :align: left


Migrate from locator:
=====================

The extension supports an update script that is able to migrate locator records
to the own tables. If the update scripts gets executed the start migration button
needs to be pushed. Afterwards there are migrated records in every folder that
contained the locator records previously.
