..  include:: /Includes.rst.txt
..  index:: Administration
..  _administration:

==============
Administration
==============

The part for administrator is fairly simple. Just install the extension with

.. code-block:: bash
   :caption: Enter on shell

   composer require evoweb/store-finder

In case the google maps geocode url changes and the extension has no update for,
that the url can be changed in the same configuration part. Just enter the url
in the field Url used for geocode.

Configuration:
==============

..  figure:: Images/admin_config.png
    :alt: Search form

CORS settings:
==============

- for backend rendering in locations records the content security policy header
  needs to be modified to allow loading images via https

  ..  code-block:: apache

      img-src 'self' data: https:;

- for frontend rendering of google AND OS map the csp header needs to be
  modified to allow script files via https

  ..  code-block:: apache

      script-src 'self' 'unsafe-inline' blob: data: https:;

- in addition the frontend rendering of OS map needs the csp header to allow
  loading style files via https

  ..  code-block:: apache

      style-src 'self' 'unsafe-inline' https:;
