Writing an easy to use A/B test helper with CakePHP and jquery
==============================================================

Knowing what is driving the user experience is key to the success of
an application. Subtle changes in the interface can cause dramatic
shifts in user behavior. Here, A/B tests display two (or more)
language choices or color choices to a user (any HTML). Metrics are
measured in two ways - 1) did the user click on the button at all and
2) how long did it take them to find it from the moment the page has
started loading? See [url=http://en.wikipedia.org/wiki/A/B_testing]
Wikipedia [/url] and
[url=http://www.slideshare.net/startuplessonslearned/eric-ries-lean-
startup-presentation-for-web-20-expo-april-1-2009-a-disciplined-
approach-to-imagining-designing-and-building-new-products] an Eric
Ries presentation [/url] for a more complete explanation of A/B tests.


Background
~~~~~~~~~~

At our company, we already used google analytics and heat maps to
track certain user behaviors, as well as other reports derived from
more general data. Other analytics, by themselves, don't tell us
specifically what change to the interface caused these results. We're
already able to measure how small changes in language can impact the
responsiveness of the user.

Summary
~~~~~~~

The A/B tests here are all about getting users to click on a piece of
content. In this code the A/B tests are designed to measure three
factors - how many times did a test display, how many times did a user
click on the test, and how long did it take.

Here's a breakdown of what the code does:

+ On page load the the view helper displays either content A or
  content B and records one test or the other as being displayed.
+ If the user clicks on a link, a cookie is set and the test result is
  retrieved on next page load. If the clickable area is not a link away
  from the page, then the A/B test result is sent right away.
+ There is a small admin page to display results, as an example

Note: Often A/B tests will have a control case as well. We do not
include a control in our A/B tests. Multivariate tests and controls
could easily be added to the code below.


Prerequisites
~~~~~~~~~~~~~

This could be written with any javascript library. We prefer jQuery;
partly for historical reasons, but also a good record of
interoperability with other libraries.


+ Running instance of CakePHP 1.2x or higher (tested in v 1.2.5)
+ Jquery 1.3.2
+ Jquery Cookie `http://plugins.jquery.com/project/cookie`_
+ assumes you have admin routing set up - see
  `http://book.cakephp.org/view/46/Routes-Configuration`_ and
  `http://cakephp.org/screencasts/view/5`_
+ assumes you the Auth component enabled with a users table set up -
  see `http://book.cakephp.org/view/172/Authentication`_




Code
~~~~

The database table is fairly straight forward. More stats could be
added like tracking IP address, browser type, or other conditions to
help differentiate the users.

::

    
    CREATE TABLE `abtests` (
      `id` bigint(12) unsigned NOT NULL auto_increment,
      `created` datetime NOT NULL,
      `user_id` int(10) default NULL,
      `test_name` varchar(64)   NOT NULL,
      `test_result` tinytext  NOT NULL,
      `seconds` double NOT NULL,
      `clicked` tinyint(1) unsigned NOT NULL default '0',
      PRIMARY KEY  (`id`),
      KEY `test_name` (`test_name`(32),`test_result`(1)),
      KEY `viewed` (`clicked`)
    ) ENGINE=InnoDB   ;

The model is used for a couple of helper functions for the admin
section, displaying of test results.
Place in app/models/abtest.php

Model Class:
````````````

::

    <?php 
     class Abtest extends AppModel {
    	var $name = 'Abtest';
    
    
        /**
         * retrieve a list of tests, total clicks, % clicks A/B averages seconds per A/B
         */
        function getList(){
            $result_array = array();
            $options['conditions'] = array( 'clicked' => 1 );
            $options['group'] = array('test_name' , 'test_result');
            $options['fields'] = array('test_name' , 'test_result'
                , 'AVG(seconds) as seconds' , 'COUNT(*) as total' );
    
            $results = $this->find( 'all' , $options);
    
            foreach($results  as $result ){
                if (empty ( $result_array[$result['Abtest']['test_name']]['total_views'])) {
                    $result_array[$result['Abtest']['test_name']]['total_views'] = $this->getTotalViews ($result['Abtest']['test_name'] );
                }
    
                $result_array[$result['Abtest']['test_name']][$result['Abtest']['test_result']]['seconds'] = round($result[0]['seconds']);
                $result_array[$result['Abtest']['test_name']][$result['Abtest']['test_result']]['total'] = $result[0]['total'];
    
            }
            return $result_array;
        }
    
        private function getTotalViews ( $test_name ) {
            $conditions = array( 'test_name' => $test_name , );
            return $this->find('count' , array('conditions' => $conditions , 'group' => 'test_name' ));
        }
    	 
    }
    
    ?>


The component does the bulk of the work to keep controllers using AB
test simpler. If a user id is present, a test choice is chosen at
random based on the user id, if no user_id is present, then the choice
is made at random depending on a session_id(). Generally not a good
idea to have the same user getting both results.
The rest is wrapping some model functions.

Place the file in app/controllers/components/abtest_handler.php


Component Class:
````````````````

::

    <?php 
     /**
     * Component available to the View helper and Controllers
     * involved in setting up and recieving tests
     *
     * @package default
     * @access public
     */
    
     class AbtestHandlerComponent extends Object {
        var $name = 'AbtestHandler';
        
        /**
         * the calling controller
         * @access protected
         */
        var $_controller;
    
        /**
         * the user id
         * @access private
         */
        var $__user_id;
    
        /**
         * the array of tests to pass to the view
         * @access private
         */
        var $__abtest_data;
    
        /**
         * system chosen test choice
         * @access protected
         */
        var $_aorb;
    
    
        function startup( &$controller ) {
            $this->_controller = $controller;
            $this->__abtest_data = array();
            $this->_controller->loadModel('Abtest');
            $this->__user_id = $controller->Auth->user('id');
            $this->_aorb = $this->choose();
        }
    
        function beforeRender(){
            $this->_controller->set( 'abtest_data' , $this->__abtest_data );
        }
    
        /**
         * Used in the controller to setup the test
         *
         * @param string $testname - the name of the test (human readable)
         * @return int id of the new abtest
         */
        function prep( $testname ){
            //TODO set error
            if (empty($testname)) return;
            $this->_controller->Abtest->create();
            $data['Abtest']['test_name']  = $testname;
            $data['Abtest']['test_result']  = $this->_aorb;
            $data['Abtest']['user_id']  =  $this->__user_id;
            $this->_controller->Abtest->save($data['Abtest']);
            //queue up the list of tests
            $this->__abtest_data[$testname]['aorb'] = $this->_aorb;
            $this->__abtest_data[$testname]['abtest_id'] = $this->_controller->Abtest->id;
        }
    
        /**
         * take in the results of the test
         *
         * @param int $abtest_id the id record for thest
         * @param float $seconds number of secons sent
         * 
         * @return boolean result
         */
        function record( $abtest_id , $seconds ){
            App::import('Sanitize');
            $this->_controller->Abtest->id = intval( $abtest_id );
            $data['Abtest']['clicked'] = 1;
            $data['Abtest']['seconds'] =  Sanitize::clean( $seconds );
            return $this->_controller->Abtest->save($data);
        }
    
        /**
         * choose to display test A or B
         *
         * @return char a literal representation of a or b
         */
        function choose( ){
            //public page, use the php_session to determine which test to show
            $cake_cookie = $this->__getUniqueSessionID();
            if ( empty( $this->__user_id ) ) {
                //get integers only from the hash
                if (!empty($cake_cookie)){
                    preg_match_all('/(\d)/', $cake_cookie , $matches );
                    //make a new int - keep it at a length of 5
                    $newint = substr(implode('' , $matches[0]), 0, 5 ) ;
                } else {
                    $newint = 2; //default
                }
                
                //https://trac.cakephp.org/wiki/Developement/CodingStandards#TernaryOperator
                if ($newint % 2 == 0 ) {
                    return 'a';
                } else {
                    return 'b';
                }
            } else {
                if ($this->__user_id % 2 == 0) {
                    return 'a';
                } else {
                    return 'b';
                }
            }
        }
    
        /**
         * get a session id for a psuedo user id
         *
         * @access private
         */
        function __getUniqueSessionID(){
            return session_id();
        }
     }
    ?>

The controller handles the admin page and receiving of the Ajax post
from the javascript.

apps/controllers/abtests_controller.php

Controller Class:
`````````````````

::

    <?php 
     /**
     * Controller for recieving AB test messages
     *
     * @package default
     * @access public
     */
    class AbtestsController extends AppController {
        var $components = array( 'AbtestHandler', 'RequestHandler', 'Auth' );
        var $helpers = array( 'Html', 'Javascript', 'Form');
        var $uses = array('Abtest');
    /**
     * Allow abtests posts from any page
     *
     * @return void
     * @access public
     */
        function beforeFilter(){
            parent::beforeFilter();
            $this->Auth->allow( 'send' );
        }
    
        /**
         * receives results of abtest via Ajax only
         *
         * @return void
         * @access public
         */
        function send(){
            Configure::write('debug' , 0);
            $this->layout = false;
    
            if ($this->RequestHandler->isAjax()) {
                //TODO security for submits per minute
                $this->set('isAjax',true);
                //record result
                if ( !empty($this->params['form']['id'])
                    && !empty($this->params['form']['seconds']) ) {
                    $this->AbtestHandler->record( $this->params['form']['id'] , $this->params['form']['seconds'] );
                }
            }
        }
    
        /**
         * list the result of tests
         * @todo given a test id show more detailed results
         * @return void
         * @access public
         */
        function admin_index(  ){
            $abtests = $this->Abtest->getList();
            $this->set('abtests',$abtests);
        }
    }
    ?>

The view helper ensures proper html is generated to trigger the
javascript for the test. See more documation in code.
app/views/helpers/abtest.php


Helper Class:
`````````````

::

    <?php 
    <?php
    class AbtestHelper extends AppHelper{
        /**
         * Wraps a variable piece of content with a span for
         * the identification of clickable A/B tests on the site.
         *
         * Both items of content are passed in, one is chosen to display.
         *
         * The fourth param is important - if user leaves the page because of a click,
         * then this must be set to true. Not all clicks leave a page, but if they
         * do the click must be recorded before the window.location changes.
         *
         * @param array $abtest_data set via component
         * @param string $testname the name of the test
         * @param string option for content a
         * @param string option for content b
         * @param boolean is the user leaving the page after click, eg href
         *
         * @return string  the html
         * 
         * Usage:
         *  $abtest->rendertest( $abtest_data, 'click test' , 'Click here' , 'Don\'t click here' );
         *
         */
        function rendertest( &$abtest_data, $testname , $contentA, $contentB, $leaving_page = true ){
            $abtest_id = $abtest_data[$testname]['abtest_id'];
            $aorb = $abtest_data[$testname]['aorb'];
            $leaving = ($leaving_page) ? 'leaving' : '';
    		$str = '<span class="abtest '.$leaving.'" id="'.$abtest_id.'" >';
    		$str .= ($aorb == 'a') ? $contentA : $contentB ;
            $str .= '</span>';
    		return $this->output($str);
    	}
    }
    ?>

Javascript plays a central role. If the click is committed, then the
cookie is set, and retrieved at next page load. Using JQuery is a
matter of choice, and there is no reason not to use the built in $ajax
helper and set the cookie yourself. This file is app/webroot/js/ab.js

::

    
    var J = jQuery.noConflict();
    var ABSAVE_URL = '/abtests/send';
    var thisdate = new Date();
    var tstart =  thisdate.getTime();
    
    J(document).ready(function(){
        //check if this cookie is set
        var abtest_id = J.cookie('abtest');
    
        if (abtest_id != '' && abtest_id != 'null'){
            var seconds = J.cookie('abtest-seconds') ;
            //reset
            J.cookie('abtest' , '');
            J.cookie('abtest-seconds' , '') ;
            bl_send_abtest( abtest_id ,seconds );
        }
    
        //prep all element
        J('.abtest').click(function(e){
            try {
                var end = new Date();
                var tend = end.getTime() ;
                var seconds = (tend - tstart) / 1000 ;
                var id = J(this).attr('id');
                var leaving_page = J(this).is('.leaving');
                if ( !leaving_page ) {
                    bl_send_abtest( id , seconds );
                } else {
                    J.cookie('abtest' , id);
                    J.cookie('abtest-seconds' , seconds);
                }
            } catch (error) {
                 
            }
        });
    });
    
    
    function bl_send_abtest( id, seconds ){
        J.post( ABSAVE_URL ,{
            'seconds' : seconds ,
            'id': id
        } , function(data){
             return true;
        });
    }


The following view provides a very simple admin report in
app/views/abtests/admin_index.ctp
You should place blank file in views/abtests/send.ctp.


View Template:
``````````````

::

    
    <div id="contentA">
    <h2>Test results</h2>
    <table>
    <tr><th>Test Name</th><th>Total Views</th><th>Total Clicks</th><th>A (and time to click in seconds avg)</th><th>B (and time to click in seconds avg)</th></tr>
    <?php foreach ($abtests as $name => $abtest): ?>
    <?php
        $a_total = (isset($abtest['a']['total'])) ? $abtest['a']['total'] : 0 ;
        $b_total = (isset($abtest['b']['total'])) ? $abtest['b']['total'] : 0 ;
        $total_clicks = $a_total + $b_total;
    ?>
        <tr>
        <td><?php echo $name; ?></td>
        <td><?php echo $abtest['total_views']; ?></td>
        <td><?php echo $total_clicks; ?></td>
        <td>
        <?php if (isset($abtest['a'])): ?>
            <?php echo $abtest['a']['total']; ?> (<?php echo ($abtest['a']['total'] / $total_clicks) * 100 ?>%) at <?php echo $abtest['a']['seconds']; ?> seconds
       <?php else: ?>
        0
       <?php endif; ?>
        </td>
        <td>
        <?php if (isset($abtest['b'])): ?>
            <?php echo $abtest['b']['total']; ?> (<?php echo ($abtest['b']['total'] / $total_clicks) * 100 ?>%)  at <?php echo $abtest['b']['seconds']; ?> seconds </td></tr>
        <?php else: ?>
        0
        <?php endif; ?>
    <?php endforeach; ?>
    </table>
    </div>



Usage
~~~~~

If you don't have jquery you'll need to include jquery min (like
`http://jqueryjs.googlecode.com/files/jquery-1.3.2.min.js`_ ) and the
ab.js files in app/webroot/js via the html/javascript helper in the
layout or view where the abtests are run.

Following a standard admin setup - assumes that app/config/routes has
something like this:

::

    
    Router::connect('/admin', array( 'controller' => 'admin', 'action' =>  'index', 'index', 'layout'=>'default'));

And

::

    Configure::write('Routing.admin', 'admin');

is set in app/config/core.php

In a layout layout in the html include the files in this order AFTER
any prototype libraries to prevent javascript namespace collisions. In
the case of this example your layout is app/views/layouts/default.ctp

::

    
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    
    <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?php echo $title_for_layout?></title>
    <?php echo $javascript->link('prototype'); ?>
    <?php echo $javascript->link('scriptaculous.js?load=effects,dragdrop'); ?>
    <?php echo $javascript->link('jquery-1.3.2.min'); ?>
    <?php echo $javascript->link('jquery.cookie'); ?>
    <?php echo $javascript->link('ab'); ?>
    </head>

In your controller ( or app_controller.php if you plan to use a lot )

::

    
       var $components = array('AbtestHandler' , 'RequestHandler', 'Auth'); //new
       var $helpers = array('Abtest', 'Html', 'Javascript', 'Form' , 'Admin');
       
       
       function myfunction(){
            $this->AbtestHandler->prep('Signup'); //new
       }
       

and use the following in the view for clicks where the user will leave
the page:

::

    
    <?php
       $testname = 'Signup'; //note this matches the test name from the controller function
       $contentA = 'Please sign up.';
       $contentB = 'Signup now' ;
       echo $abtest->rendertest( $abtest_data, $testname, $contentA, $contentB) ;
    
    ?>

Or if the click just triggers some DHTML on the page

::

    
    <?php
       $testname = 'Open up that hidden div';  
       $contentA = 'Please click me';
       $contentB = 'Click me NOW' ;
       echo $abtest->rendertest( $abtest_data, $testname, $contentA, $contentB , false ) ;
    ?>



Conclusion
~~~~~~~~~~

As a beginning user to CakePHP, it's clear the framework already
provides a rapid application development. Part of gathering
information from users about what to iterate on can be enhanced by the
quantitative data provided by A/B tests.

Thanks to the CakePHP community on IRC for reviewing the article.


.. _http://book.cakephp.org/view/46/Routes-Configuration: http://book.cakephp.org/view/46/Routes-Configuration
.. _http://plugins.jquery.com/project/cookie: http://plugins.jquery.com/project/cookie
.. _http://jqueryjs.googlecode.com/files/jquery-1.3.2.min.js: http://jqueryjs.googlecode.com/files/jquery-1.3.2.min.js
.. _http://cakephp.org/screencasts/view/5: http://cakephp.org/screencasts/view/5
.. _http://book.cakephp.org/view/172/Authentication: http://book.cakephp.org/view/172/Authentication

.. author:: hendler
.. categories:: articles, helpers
.. tags:: helpers,testing,jquery,abtests,Helpers

