Concatenate Behavior
====================

by bparise on May 22, 2007

Using Model::afterFind($results) is a great way to modify your data to
return newly formatted fields. But, sometimes this can become a
tedious task. The Concatenate Behavior will handle this automatically
for you!


Installation
------------

The most up-to-date source code can be found at:
`http://www.cakecollab.org/concatenate.phps`_

1. Simply create a file named 'concatenate.php' in your
./app/models/behaviors folder and copy the code from the url above
into it and save.

2. Add the following code to the top of the model you wish to add
fields

::

    <?php
    var $actsAs = array(
        'Concatenate' => array(
            'field' => 'concat string',
        ),
    );



Example Usage
~~~~~~~~~~~~~

I will use the Person model throughout this short article.

::

    <?php 
    class Person extends AppModel {
        var $name = 'Person';
        var $actsAs = array(
            'Concatenate' => array(
                // basic concatenation - separating values with a " " space
                'full_name' => 'Person.first_name Person.last_name',
                // or you can add other characters within the string
                'full_name_2' => 'Person.last_name, Person.first_name (Person.id)',
            ),
        );
    }



Limitation
----------

There is only 1 limitation to this behavior: A concatenated field name
you set in your model can't already exist in the model’s table . For
example:

::

    <?php
    // people.email is already a field!
    class Person extends AppModel {
        var $name = 'Person';
        var $actsAs = array(
            'Concatenate' => array(
                'email' => 'Person.first_name <Person.email>',
            ),
        );
    }

[p]Why is this a problem? In the example above we overwrite the
`email` field. If we were to edit a Person the behavior would
automatically return the `email`

field as the new concatenated value "first_name " which in your edit
form would look like "Email: [Brandon ]".

[p] Safety Net: If you set a concatenated field that already exists in
the model the behaviour will output a NOTICE to you and not include
that field!


Generate List Enhancement
-------------------------
[p]One of the coolest parts of this behavior is it allows you to
create new fields to use with Model::generateList() so the “value

.. _http://www.cakecollab.org/concatenate.phps: http://www.cakecollab.org/concatenate.phps
.. meta::
    :title: Concatenate Behavior
    :description: CakePHP Article related to concatenation,generateList,behavior,concat,Behaviors
    :keywords: concatenation,generateList,behavior,concat,Behaviors
    :copyright: Copyright 2007 bparise
    :category: behaviors

