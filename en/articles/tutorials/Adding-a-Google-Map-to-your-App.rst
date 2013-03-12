

Adding a Google Map to your App
===============================

by %s on June 23, 2007

For anyone looking to easily pop a Google Map into their CakePHP App,
you've found the right spot.

Though the Google Map craze has passed, I have decided to write a
tutorial to implement a Google Map because in my opinion, I don't feel
there has been a clean, flexible method of creating a map. Also, since
this is CakePHP, we like to do things properly.
If you have any questions please send me an email at lababidi (atat)
mooder.org.
I took gwoo's Google Helper class and modified quite a bit to fit in
well with CakePHP. I also wrote a GoogleGeo class to geocode addresses
by using Google's Geocoding service. The example I created below is is
at `http://mooder.org/cakemap/`_ If you prefer to skip the tutorial
and go straight to the code, you can download it here
`http://mooder.org/cakemap/files/app.zip`_ and the SQL here
`http://mooder.org/cakemap/files/points.sql`_.
Enjoy and happy baking!


Step 0 - Downloads
~~~~~~~~~~~~~~~~~~
Grab these necessary pieces:

#. CakePHP! - `http://cakephp.org`_
#. GoogleGeo - `http://mooder.org/googlegeo`_ necessary to geocode an
   address into Latitude/Longitude, drop this file in your app/vendors or
   vendors folder
#. GoogleMapHelper -
   `http://mooder.org/cakemap/files/google_map.php.txt`_ necessary to
   create a map on your Cake app, drop this file into the
   app/views/helpers directory
#. Google Maps API Key - `http://google.com/apis/maps/signup.html`_
   gives you permission to use Google Maps on your site, paste it into
   the certain lines googlegeo.php and in google_map.php



Step 1 - Database Table
~~~~~~~~~~~~~~~~~~~~~~~
In my example app I have created the following table. Make sure you
have latitude and longitude as fields in your table.

::

    
    CREATE TABLE `points` (
      `id` int(11) NOT NULL auto_increment,
      `name` varchar(60) NOT NULL,
      `description` text NOT NULL,
      `street` varchar(60) NOT NULL,
      `city` varchar(60) NOT NULL,
      `state` varchar(2) NOT NULL,
      `zip` varchar(5) NOT NULL,
      `latitude` float NOT NULL,
      `longitude` float NOT NULL,
      `zoom` int(11) NOT NULL,
      `created` datetime NOT NULL,
      `modified` datetime NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 
    AUTO_INCREMENT=1 ;




Step 2 - Bake the MVC
~~~~~~~~~~~~~~~~~~~~~
Bake the Model, Controller, View of your new object. I didn't create
any relations in this example, nor did I worry about validation
criteria but you might have to depending on your application.

::

    
    $ php cake/scripts/bake.php
    
     ___  __  _  _  ___  __  _  _  __      __   __  _  _  ___
    |    |__| |_/  |__  |__] |__| |__]    |__] |__| |_/  |__
    |___ |  | | \_ |___ |    |  | |       |__] |  | | \_ |___
    ---------------------------------------------------------------
    
    Bake -app in /home/.antoine/mooder/bearsontherun.com/cakemap/app (y/n)
    [y] > y
    
    Baking...
    ---------------------------------------------------------------
    Name: app
    Path: /home/.antoine/mooder/bearsontherun.com/cakemap/app
    ---------------------------------------------------------------
    [M]odel
    [C]ontroller
    [V]iew
    
    Please select a class to Bake: (M/V/C)
    > m
    ---------------------------------------------------------------
    Model Bake:
    ---------------------------------------------------------------
    Possible models based on your current database:
    
    1. Point
    
    
    Enter a number from the list above, or type in the name of another model.
    > 1
    
    etc....
    
    ---------------------------------------------------------------
    
    Bake -app in /home/.antoine/mooder/bearsontherun.com/cakemap/app (y/n)
    [y] >
    
    
    Baking...
    ---------------------------------------------------------------
    Name: app
    Path: /home/.antoine/mooder/bearsontherun.com/cakemap/app
    ---------------------------------------------------------------
    [M]odel
    [C]ontroller
    [V]iew
    
    What would you like to Bake? (M/V/C)
    > c
    ---------------------------------------------------------------
    Controller Bake:
    ---------------------------------------------------------------
    Possible Controllers based on your current database:
    1. Points
    
    
    Enter a number from the list above, or type in the name of another controller.
    > 1
    
    Would you like bake to build your controller interactively?
    Warning: Choosing no will overwrite  controller if it exist. (y/n)
    [y] >n
    
    etc........
    
    ---------------------------------------------------------------
    
    
    Bake -app in /home/.antoine/mooder/bearsontherun.com/cakemap/app (y/n)
    [y] >
    
    
    Baking...
    ---------------------------------------------------------------
    Name: app
    Path: /home/.antoine/mooder/bearsontherun.com/cakemap/app
    ---------------------------------------------------------------
    [M]odel
    [C]ontroller
    [V]iew
    
    What would you like to Bake? (M/V/C)
    > v
    ---------------------------------------------------------------
    View Bake:
    ---------------------------------------------------------------
    Possible Controllers based on your current database:
    1. Points
    
    Enter a number from the list above, or type in the name of another controller.
    > 1
    
    Would you like bake to build your views interactively?
    Warning: Choosing no will overwrite  views if it exist. (y/n)
    [y] >
    
    etc....
    



Step 3 - GeoCoding
~~~~~~~~~~~~~~~~~~
Modify the controller to add geocoding and helper declaration.
We need add geocoding ability into our controller after a form is
submitted. To do that we need to call the vendor class GoogleGeo, then
pass in the address into the geo() function. The address can be passed
in array or string form but no other items other than the addresscan
be passed it. This function returns an array that contains the
Latitude and Longitude we need. We take the lat/long combo in put that
back into our $this->data variable and store it into the DB via the
Model.
Secondly we need to modify the controller to specify the helpers we
need. Add "GoogleMap" to the array of helpers so the view has the
ability to use the GoogleMap. This is very important. This could have
been done during Step 2/Baking but it's your choice.
After fixing the controller, add a few points to test out it's
functionality and verify it works. Please don't hesitate to ask me any
questions if you are having problems.


Controller Class:
`````````````````

::

    <?php 
    class PointsController extends AppController
    {
        //var $scaffold;
        var $name = 'Points';
        var $helpers = array('Html', 'Form', 'Ajax', 'Javascript','GoogleMap' );
    
        function index() {
            $this->layout = "map";
            $this->Point->recursive = 0;
            $this->set('points', $this->Point->findAll());
        }
    
        function add() {
            if(empty($this->data)) {
                $this->render();
            } else {
                $this->cleanUpFields();
    
                $address = $this->data['Point'];
                unset(
                    $address['name'],$address['description'],
                    $address['latitude'], $address['longitude'],
                    $address['zoom']
                    );
                var_dump($address);
                vendor('googlegeo');
                $googleGeo = new GoogleGeo($address);
                $geo = $googleGeo->geo();
                var_dump($geo);
                $this->data = array_merge($this->data['Point'],$geo);
    
            if($this->Point->save($this->data)) {
                    $this->Session->setFlash('The Point has been saved');
                    //$this->redirect('/points/index');
                } else {
                    $this->Session->setFlash('Please correct errors below.');
                }
            }
        }
    }
    ?>



Step 4 - Adding the Map and more on the view
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

We now need to modify the views to include the map on the 'index'
view, and remove the form inputs for Latitude and Longtitude on the
'add' view. This is the section that requires the most work. But worry
not, it's not too much work, and isn't too complex.


app/views/layouts/map.thtml
```````````````````````````
You'll need to create a layout (ie app/views/layout/map.thtml) for
your actions that require a map. In this layout you'll need to add a
Javascript include to the Google Maps server. The URL used here
includes the Google Maps API Key. Please make sure this key proper.
Use this line of code in your map layout.

View Template:
``````````````

::

    
    <?php 
    $key = "PASTE KEY HERE";
    $url = "http://maps.google.com/maps?file=api&v=2&key=$key";
    echo $javascript->linkOut($url); 
    ?>



app/views/points/index.thtml
````````````````````````````
On the app/views/points/index.thtml view you need to do a few things.
First let's start by just having the map display. I have added the
block of php code right below the H2 header. These lines of code
produce a map centered on latitude:38.9206, longitude:-77.1845 (which
is near my hometown). You may change it to whatever location you want
it to be centered. The map can also be resized using the $style
variable.

View Template:
``````````````

::

    
    <div class="points">
    <h2>List Points</h2>
    <?php
    $avg_lat = -77.1845;
    $avg_lon = 38.9206;
    $default = array('type'=>'0','zoom'=>13,'lat'=>$avg_lat,'long'=>$avg_lon);
    echo $googleMap->map($default, $style = 'width:100%; height: 800px' );
    ?>
    
    <table cellpadding="0" cellspacing="0">
    <tr>
        <th>Id</th>
        <th>Name</th>     <th>Description</th>
        <th>Street</th>
        <th>City</th>
        <th>State</th>
        <th>Zip</th>
        <th>Latitude</th>
        <th>Longitude</th>
        <th>Zoom</th>
        <th>Created</th>
        <th>Modified</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($points as $point): ?>
    <tr>
        <td><?php echo $point['Point']['id']; ?></td>
        <td><?php echo $point['Point']['name']; ?></td>
        <td><?php echo $point['Point']['description']; ?></td>
        <td><?php echo $point['Point']['street']; ?></td>
        <td><?php echo $point['Point']['city']; ?></td>
        <td><?php echo $point['Point']['state']; ?></td>
        <td><?php echo $point['Point']['zip']; ?></td>
        <td><?php echo $point['Point']['latitude']; ?></td>
        <td><?php echo $point['Point']['longitude']; ?></td>
        <td><?php echo $point['Point']['zoom']; ?></td>
        <td><?php echo $point['Point']['created']; ?></td>
        <td><?php echo $point['Point']['modified']; ?></td>
        <td nowrap>
            <?php echo $html->link('View','/points/view/' . $point['Point']['id'])?>        <?php echo $html->link('Edit','/points/edit/' . $point['Point']['id'])?>        <?php echo $html->link('Delete','/points/delete/' . $point['Point']['id'], null, 'Are you sure you want to delete id ' . $point['Point']['id'])?>
        </td>
    </tr>
    <?php endforeach; ?>
    </table>
    
    <ul class="actions">
        <li><?php echo $html->link('New Point', '/points/add'); ?></li>
    </ul>
    </div>
    

After this is done, look at it. Congratulate yourself on a good job!
You now have a map on your application. But we're not done yet. Let's
add some markers and functionality. By clicking on the markers, an
info window (comic-book-like speech window) will pop up with writing
or images inserted. These info window are just HTML. We will be able
to create and mold these windows as we wish, putting relevant
information in them.
I've placed a foreach loop that loops through all our Point objects.
In this loop a title and and html body of the InfoWindow is created.
You have complete freedom to place anything you'd like here. Place an
image, a form, links, text, etc.
The Points are passed into the addMarkers() function. This function
takes the title and html we created and lays out markers and
InfoWindows to pop up when the markers are clicked. Also the
addMarkers function adds a js property to the Point object
($Point['Point']['js']) that is a JavaScript function. This function
acts the same as clicking on a marker. Use it if you wish. I did in
the table.
I added some style to the table rows (tr) for hovering. I'd like the
rows to be clicked bringing up the InfoWindow of the respective point.
Again, you have free will to design this in any way you'd like.
I did some other style variations on this view such as the DIVs, the
table, and some spans. I don't need to go anymore into detail about
this. You get the gist.
To add the javascript functionality to the table rows (tr), I added an
onclick method using the js property from the Point object as
mentioned above. I also took off many of the columns in the table. The
information does not need to be shown and can be placed in the
InfoWindow.


::

    
    <h2>List Points</h2>
    <div style="float:left;width:80%;">
    <?php
    $avg_lat = -77.1845;
    $avg_lon = 38.9206;
    
    foreach($points as $n=>$point){
        $points[$n]['Point']['title'] = "<b>".$point['Point']['name']."</b>";
        $points[$n]['Point']['html'] = $point['Point']['description'];
    
        }
    
    $default = array('type'=>'0','zoom'=>13,'lat'=>$avg_lat,'long'=>$avg_lon);
    echo $googleMap->map($default, $style = 'width:100%; height: 800px' );
    //if(isset($points)){
        echo $googleMap->addMarkers($points);
    //  }
    
    
    ?>
    </div>
    <style>
    tr:hover {
        cursor: pointer;
        background: #904428;
        }
    </style>
    
    <div style="float:left;width:15%">
        <span style="font-size:12pt;font-weight:bold;background:#239855;"><?php echo $html->link('Create A New Point', '/points/add'); ?></span>
    <table cellpadding="0" cellspacing="0">
    <tr>
        <th>Name</th>
        <th>City</th>
        <th>State</th>
    </tr>
    <?php foreach ($points as $point): ?>
    <tr onclick="<?php echo $point['Point']['js'] ?>"  >
        <td><?php echo $point['Point']['name'] ?></td>
        <td><?php echo $point['Point']['city'] ?></td>
        <td><?php echo $point['Point']['state'] ?></td>
    </tr>
    <?php endforeach; ?>
    </table>
    
    </div>


app/views/points/add.thtml
``````````````````````````
This last file you need to modify is very simple. Just remove the
Latitude and Longtitude inputs. This is to prevent confusion for
anyone inputing data. Do this on edit.thtml as well if you set up the
edit() method in the controller to geocode as well.

View Template:
``````````````

::

    
    <div class="optional">
        <?php echo $form->labelTag('Point/latitude', 'Latitude');?>
        <?php echo $html->input('Point/latitude', array('size' => '60'));?>
        <?php echo $html->tagErrorMsg('Point/latitude', 'Please enter the Latitude.');?>
    </div>
    <div class="optional">
        <?php echo $form->labelTag('Point/longitude', 'Longitude');?>
        <?php echo $html->input('Point/longitude', array('size' => '60'));?>
        <?php echo $html->tagErrorMsg('Point/longitude', 'Please enter the Longitude.');?>
    </div>
    



Step 5 - Add Points
~~~~~~~~~~~~~~~~~~~
You're finished! All that's left is to add some points in to see your
creation in its final stage. Add things like Grandma's house, the
nearest Chipotle or any other place you'd like.

If you have any questions please don't hesitate sending me an email at
lababidi (atat) mooder.org. Enjoy.

.. _http://cakephp.org: http://cakephp.org/
.. _http://google.com/apis/maps/signup.html: http://google.com/apis/maps/signup.html
.. _http://mooder.org/googlegeo: http://mooder.org/googlegeo
.. _http://mooder.org/cakemap/: http://mooder.org/cakemap/
.. _http://mooder.org/cakemap/files/google_map.php.txt: http://mooder.org/cakemap/files/google_map.php.txt
.. _http://mooder.org/cakemap/files/app.zip: http://mooder.org/cakemap/files/app.zip
.. _http://mooder.org/cakemap/files/points.sql: http://mooder.org/cakemap/files/points.sql
.. meta::
    :title: Adding a Google Map to your App
    :description: CakePHP Article related to google,map,Google Maps,geocoding,Tutorials
    :keywords: google,map,Google Maps,geocoding,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

