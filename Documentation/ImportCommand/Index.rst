..  include:: /Includes.rst.txt
..  index::  Import command
..  _importCommand:

==============
Import command
==============

By using the import command its possible to import locations from a a spreadsheet
into a storage folder. If given references to attributes, categories and files are
created.

Arguments
=========

* fileName
    StorageId (most likely 1) and path and filename of excel file that should be
    imported relatively to the storage (fileadmin) Eg: 1:/user_upload/locations.xlsx

Options
=======

* --storagePid -s
    Page id of storage folder. Default is 1.
* --clearStorageFolder -c
    Flag if storage folder should be emptied before importing
* --columnMap -o
    Json encoded column map array.

    ..  code-block:: json
        :caption: Default

        {"A":"import_id","B":{0:"name",1:"storeid"}},"C":"address","D":"city","E":"zipcode","F":"country","G":"state","H":"person","I":"url","J":"image"}

* --attributeMap -a
    Json encoded attribute map array.

    ..  code-block:: json
        :caption: Default

        {"K":{"att1":1}}

* --categoryMap -t
    Json encoded attribute map array.

    ..  code-block:: json
        :caption: Default

        {"L":{"cat1":1}}

Transformation
==============

* the first line will always be ignored.
* transformation is in order of attributes, categories, location fields.
* every column in one of the three maps is imported in a location field
* country, state, image, media, icon are special fields
  * country is a ISO3 code for a country and is stored in the country field as uid of the static_country record
  * state is a zones code for a state and is stored in the state field as uid of the static_country_zones record
  * image is a file path and name relative to file storage like 1:/user_upload/test.jpg and is referenced by sys_file_reference
  * media is a file path and name relative to file storage like 1:/user_upload/video.mp4 and is referenced by sys_file_reference
  * icon is a file path and name relative to file storage like 1:/user_upload/icon.jpg and is referenced by sys_file_reference
* references to attributes, categories and files are removed if not present any more

Importing constraints
=====================

* if import_id is set updating locations is possible
* it's always an incremental import
* if a full import should be performed the flag clearStorageFolder needs to be true, then the folder gets emptied before importing
* it's possible to import multiple references for attributes, categories and files
* by adding multiple columns containing file information:
  H, I, J
  "1:/user_upload/image1.jpeg","1:/user_upload/image1.jpeg","1:/user_upload/image1.jpeg"
  and change the configuration object like:
  {..."H":"image","I":"image","J":"image"...}
  The result is, that the locations has three images referenced

Examples of import command calls
================================

..  code-block:: bash
    :caption: Simple command call

    vendor/bin/typo3 storefinder:import --storagePid=202 --clearStorageFolder=1 filename

..  code-block:: bash
    :caption: Command call with column map

    vendor/bin/typo3 storefinder:import --storagePid=4 --columnMap="{\"A\":\"import_id\",\"B\":\"name\",\"D\":\"city\"}" "1:/user_upload/ExportExcel.xlsx"

..  code-block:: bash
    :caption: Multiline command call

    vendor/bin/typo3 storefinder:import \
      -c \
      --storagePid=2
      -o"{\"A\":\"import_id\",\"B\":\"name\",\"D\":\"city\"}" \
      "1:/user_upload/ExportExcel.xlsx"
