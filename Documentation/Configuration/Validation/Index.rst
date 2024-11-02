..  include:: /Includes.rst.txt
..  index:: Validation
..  _validation:

==========
Validation
==========

Purpose of Validators
=====================

Validators are a mechanism to ensure that all information given by the user
meet the expectation of the extension. Either if the values make sense in terms
of format like zip or in required information value like city.

If values need to be enforce your should use the
`RequiredValidator <required-validator>`_ because
this validator does not only check if the value of the configured field is
filled but it also serves as a signal for the Required Partial to render the
corresponding flag.

All validators are optional, could be set single or may be even assigned multiple
times to a field. Despite the concept of extbase you are free to choose how many
validators should take care of a value.

Different possibilities of assigning validators
===============================================

In case you want to have no validation at all for a field was was configured
by default as required you need to empty the associated validators. Look at the
example given where the validators of the email gets removed.

Remove validators
-----------------

..  code-block:: typoscript
    :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

    plugin.tx_storefinder.settings.validation.zipcode >

If only one validator is needed you could assign it directly to the field like
below.

Assign only one validator
-------------------------

..  code-block:: typoscript
    :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

    plugin.tx_storefinder.settings.validation.zipcode = Evoweb\StoreFinder\Validation\Validator\RequiredValidator

And finally it's possible to have multiple validators for one field like in the
next example.

Assign multiple validators
--------------------------

..  code-block:: typoscript
    :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

    plugin.tx_storefinder.settings.validation.city {
        1 = Evoweb\StoreFinder\Validation\Validator\RequiredValidator
        2 = StringLength
    }

Regarding validator it's possible to have values attached to the assigned one.
This is beneficial if you want to check against conditions that are not equal
in different cases. One point where this is shown best with is the length of
passwords.

In total you have three possible combination of validator assignments for each
field that you use in your form. You have none, one and multiple validators.
And in case of a validator present you can add options too override the default
that is set in the validator.

Special validators
==================

The ConstraintValidator is not meant to be used for field validation. This validator
is a special construct to make the configuration via TypoScript possible. All
others are free to combine. If a validator is only suited for a certain field
it will be mentioned in the detail configuration.

Prefixing needed for non extbase validators
===========================================

To use the extension validators you need to prefix them in the TypoScript with
Evoweb\StoreFinder\Validation\Validator\ . For all validators without this
prefix the validation assumes that they are extbase specific validators and use
them as such.

Secondly this makes it possible to use custom validators that do not come with
extbase or store_finder. Just code your validator and make it available for auto
loading (either in an extbase standard path or via ext_autoload.php). Afterwards
you are ready to use your validator like in the following example.

Custom validator usage
----------------------

..  code-block:: typoscript
    :caption: EXT:my_extension/Configuration/TypoScript/setup.typoscript

    plugin.tx_storefinder.settings.validation.create.city {
        1 = Evoweb\StoreFinder\Validation\Validator\RequiredValidator
    }

Available validators
====================

Beside the validators that come with extbase and which are although available
in the different processes, the registration come with a set of specific ones
that are tailored to the special need. The following lists all validators
which are suited for the usage on fields.

..  confval-menu::
    :name: validator-reference
    :display: table
    :type:
    :Default:

    ..  _required-validator:

    ..  confval:: RequiredValidator
        :type: :ref:`string <t3tsref:data-type-string>`

        This validator serves two purpose. First of check if the field contains
        a value and that it is not empty. Second the rendering uses this
        validator as condition to render required sign or not.
