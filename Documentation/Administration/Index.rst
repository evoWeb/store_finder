.. include:: /Includes.rst.txt

.. _administration:

==============
Administration
==============

The part for administrator is fairly simple. Just install the extension with

.. code-block:: bash
   :caption: Enter on shell

   composer require evoweb/store-finder

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


CORS settings:
==============

- for backend rendering in locations records the content security policy header
  needs to be modified to allow loading images via https

  .. code-block:: apache

    img-src 'self' data: https:;

- for frontend rendering of google AND OS map the csp header needs to be
  modified to allow script files via https

  .. code-block:: apache

    script-src 'self' 'unsafe-inline' blob: data: https:;

- in addition the frontend rendering of OS map needs the csp header to allow
  loading style files via https

  .. code-block:: apache

    style-src 'self' 'unsafe-inline' https:;
