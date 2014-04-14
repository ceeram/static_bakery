ObjectizeBehavior - Lightweight ORM goodness
============================================

by taylor.luk on December 15, 2007

This Behavior allows CakePHP Model to return object instance instead
of data array, its on CakePHP 2.0 roadmap and I bring you as a
Behavior. It shows the power of extending CakePHP to provider simpler
data access. It offers nicer data access in View layer and this is
also the build block for me to design and implement public access API
for my CakePHP application.


Background
----------
CakePHP's MVC design works great for the application i have developed,
Currently Model return a data container in array, it works great since
associative array can be easily accessed, iterate in View.

::

    
      Array(
        'Customer' => Array (
            'first_name' => 'Taylor',
            'last_name' => 'Luk'
          ),
        'Profile' => Array (
            'interests' => 'sports, programming, automobile, movie'
        )

Unfortunately, mycurrent CakePHP application involved implementing a
public API to provide a wide range of formalized data access, it has
to be secure and easy to use, therefore, this is not the ideal
solution.


#. It's static data, if you try to pre built a data array large enough
   to contains all the content and all possible associated data is not
   efficient at all, since one user could be using only a subset of data
   whereas a different user wants a different subset
#. You may want to only allow a subset of data to be public
   accessible, of course, you can query only what its required in
   controller but that is just not DRY enough
#. If you have deal with large data set you will know that accessing a
   deep associative array in View could quite ugly.



Prologue
--------
If anyone look at the to-do list on CakePHP 2.0 milestone you will see
Change Models to return object instances instead of arrays .
The idea is been circulating in my mind for a while and having to see
how great they
work in other web framework (Django, Rails) i have decide to implement
it as a Model Behavior without hacking any of the CakePHP core.


#. It allows using object instance of data in View
#. Helping CakePHP and the core team to explore the problem space,
   hopefully gather feedback from interested party



The code
~~~~~~~~


Features
````````

#. Allow model to return object instance also allows to switch between
   standard data array or object instance easily. (more example in
   following section)
#. Dynamically link object methods calls to whitelisted model methods
   . Great! so model methods can now be very useful in View.
   getRelatives() as $person) :?> any one?
#. Access control to all property/method access, important for public
   API.
#. Optional string representation of object when you implement
   Model::readable() method to return a human readable string and it will
   be string representation of that object. For example will print out
   first namd and last name of a person
#. inspect() helper function to inspect object structure - this is a
   replacement to php print_r to only output public attributes, it can be
   used as "debug(inspect($objectinstance))"



How it works
````````````
Technically, this is briefly is how this behavior works.


+ Use Set::__map() method to perform data array to object mapping,
  "DataObject" class is the custom skeleton for all object instances
+ Visit the object tree structure recursively to associate/link Model,
  Access setting respectively
+ Model::id property will be set using respective id field in data
  array as a reference to current row in database table. It makes user
  defined Model method actually useful later on (example section)


::

    
    //$person - a object instance of Person model
    DataObject Object (                     // <- Bind Person model
        'name' => 'peter white',
        'age'  => 17,
        'sex'  => 'M'
        'addresses' => array(               // <- Person hasMany Address, addresses as alias makes sense to me.
            [0] => DataObject Object (      // <- bind Address model
                 'address' => 'Unit 7, 111 Prince st',
                 'city'    => 'Sydney'
                 'postcode'=> '2035',
                 'state'   => 'NSW',
                 'country' => 'Australia'
            )
        )
    )



Requirement
~~~~~~~~~~~
PHP5 + CakePHP 1.2 pre-beta+


Required Hack
+++++++++++++

UPDATE: hack is no longer required after SVN version r6123 since its
included in Revision 6123 of 1.2 branch.

To me this is actually a fix i submitted
`https://trac.cakephp.org/ticket/3689`_ to make object mapping work
correctly Make following changes Set::__map() method in
cake/lib/set.php
change following around line 220

::

    
        function __map($array, $class, $primary = false) {
            $out = new $class;
            if (is_array($array)) {
                foreach ($array as $key => $value) {
                    if (is_numeric($key) && is_array($value)) {
                        $out = (array)$out;           // <= From this
                    ...

To this

::

    
        function __map($array, $class, $primary = false) {
            $out = new $class;
            if (is_array($array)) {
                foreach ($array as $key => $value) {
                    if (is_numeric($key) && is_array($value)) {
                        if (is_object($out)) {
                            $out = get_object_vars($out);  // <=  To this
                        }
                        ...



Download, Config, Install
~~~~~~~~~~~~~~~~~~~~~~~~~
I have posted on cake bin for now, may move to Cakeforge or my own svn
later on.
Download the code and place it in app/models/behaviors/objectize.php
`http://bin.cakephp.org/saved/25937`_
Simple Install

Model Class:
````````````

::

    <?php 
    class Post extends AppModel {
      var $name = 'Post';
      var $actsAs = array('Objectize');
    }
    ?>

Advanced Install if you want to allow Object instance to access model
methods, you can define the optional setting as following.

Model Class:
````````````

::

    <?php 
    class Post extends AppModel {
      var $name = 'Post';
      var $actsAs = array(
          'Objectize' => array('allow'=> 'getRelated, getNext, getPrev');
          
      ));
      
      function getRelated() {...}
      function getNext() {...}
      function getPrev() {...}
    }
    ?>

Behavior Settings

#. allow - safe guard methods access
#. deny - block property access

Both setting can use either array or comma separated string, and yes
"*" indicates all.


Usage
~~~~~
ObjectizeBehavior is extremely flexible, there are a few ways to
return object instance and following section shows its usage in
controller.


+ Setting "objectize => true" in query field or condition field



limitation
``````````
It altered behavior slightly.

#. belongsTo association mapped to a different structure, now its
   associated object is a child of current object, following is a example
   of Profile <-belongsTo-> User Original
#. HABTM "with association" is new to CakePHP 1.2, since the new dummy
   model is not a real class. its data will be ignored and merge to its
   parent element
#. Please consider this as a beta quality, please use with caution



example
~~~~~~~
Note: I did not follow Cake's standard in association naming, I used
alternative alias such as person hasMany addresses because it makes
sense to me.

Create sample database tables `http://bin.cakephp.org/saved/25904`_

Model Class:
````````````

::

    <?php 
    // app/models/customer.php
    class Customer extends AppModel {
        var $actsAs = array('Objectize');
        
        var $hasMany = array('addresses' => array('className'=>'Address'));
        
        var $hasOne = array(
               'profile' => array('className'=>'Profile'),
             'primary_address' => array('className' => 'Address',
                                      'foreignKey'=>'customer_id',
                                   'conditions' => 'is_primary = 1')
        );
    
        function readable() {
            if (!$this->data) {
               $this->read();
            }
            extract($this->data[$this->name]);
            return "{$first_name} {$last_name}";  
        }
    }
    
    //app/models/address.php
    class Address extends AppModel {
        var $actsAs = array('Objectize'=>array('allow'=>'*'));
    }
    
    // app/models/profile.php
    class Profile extends AppModel {
        var $actsAs = array(
           'Objectize' => array('allow'=>'getInterests')
        );
    
        var $belongsTo = array('customer'=>array('className'=>'Customer'));
    
        function getInterests() {
            $results = $this->find(array(
                'Profile.id' => $this->id,
                'objectize' => false
            ), 'interests');
    
            if (!empty($results)) {
                return set::normalize($results[$this->alias]['interests'], false);
            }
            return array();
        }
    }
    
    ?>



Controller Class:
`````````````````

::

    <?php 
    // app/controllers/customers.php
    class CustomersController extends AppController {
        var $name = 'Customers';
        var $uses = array('Customer');
        
        function index() {
        $customers = $this->Customer->findAll(array('objectize'=>true));
        
        /*
          // Alternitively
        $this->Customer->returnObject = true;
        $this->Customer->findAll();
        
        
          // Or even 
          
        $this->Customer->find('all', array('objectize'=>true));
        */
        
            debug(inspect($customers));
            die;
        }
        
        function check($id = 1) {
        
        $this->Customer->read(null, $id);
        
        $customer = $this->Customer->objectInstance();
        
        debug(inspect($customers));
        die;
        }
    }
    ?>



quick bench
~~~~~~~~~~~

This a crappy benchmark run on a very simple application with 3 Models
and around 20 entries. Following is the time and peak memory footprint
compare to standard Model::find operation.

::

    
    Case 1 : 
      Standard
      Customer::FindAll 
      Time: 0.132060050964
      Peak: 4,778,576 bytes
    
    
    Case 2 :
      Loaded Objectize only
      Customer::findAll
      Time: 0.145238876343
      Peak: 4,895,912 bytes
    
    
    Case 3 :
      Loaded objectize, map to object
      Customer::findAll(array('objectize'=>true));
      Time: 0.15016078949
      Peak: 4,895,912 bytes

Luckly no surprises here so far


summery
-------

In conclusion, This Behavior was developed to satisfy my urgent need.
I am sharing this to the community and help whoever may find it
useful.
I really like to hear some official opinion from the core team.

Object instance as data container is definitely the future, it offers
greater power and control compare to Array data type without too much
performance hit when its implemented correctly.
CakePHP matures as version 1.2 is just around the corner, I look
forward for more exciting "automagic" once we drop PHP4 completely.

So everyone please feel free to speak up, suggest, post bug in the
comments.


.. _https://trac.cakephp.org/ticket/3689: https://trac.cakephp.org/ticket/3689
.. _http://bin.cakephp.org/saved/25904: http://bin.cakephp.org/saved/25904
.. _http://bin.cakephp.org/saved/25937: http://bin.cakephp.org/saved/25937
.. meta::
    :title: ObjectizeBehavior - Lightweight ORM goodness
    :description: CakePHP Article related to behavior,ORM,objectize,object instance,Behaviors
    :keywords: behavior,ORM,objectize,object instance,Behaviors
    :copyright: Copyright 2007 taylor.luk
    :category: behaviors

