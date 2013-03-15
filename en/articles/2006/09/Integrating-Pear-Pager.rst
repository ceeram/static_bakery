Integrating Pear Pager
======================

by %s on September 22, 2006

PEAR (http://pear.php.net) has a decent Pager class that is easy to
integrate in Cake.


1 - Download and install
------------------------
Download the PEAR pager package from
`http://pear.php.net/package/Pager`_. Unpack it and put the libraries
in (e.g.) /vendors/Pear/Pager.



2 - Make a component
--------------------
Make a component "pager.php". This will act as a wrapper between
CakePHP and Pager. This way, we can do all the Pager setup outside of
the controller. Most parameters will probably remain unchanged between
the different invocations. We have the possibility to overwrite
specific parameters later from the controller.


Component Class:
````````````````

::

    <?php 
    class PagerComponent extends Object
    {    
        /**
         * The (calling) controller object.
         *
         * @access public
         * @var object
         */
         var $Controller;
    
         /**
          * The pager object.
          *
          * @access public
          * @var object
          */
         var $Pager;
         
         /**
          * Configuration parameters
          *
          * @access public
          * @var array
          */
         var $params;
    
        /**
         * Component pseudo controller
         *
         * @access public
         * @param object $controller Calling controller object
         * @return void
         */
        function startup(&$controller) {
            $this->Controller =& $controller;
        }
        
        /**
         * Initializes the pager. Must be called before using the component.
         *
         * Takes user configuration and creates pager object ($this->Pager)
         *
         * @access public
         * @param array $config Configuration options for Pager::factory() method
         * @see http://pear.php.net/manual/en/package.html.pager.factory.php
         * @return void
         */
        function init($config)
        {        
            // Get the correct URL, even with admin routes
            $here = array();        
            if (defined('CAKE_ADMIN') && !empty($this->Controller->params[CAKE_ADMIN])) {
                $here[0] = $this->Controller->params[CAKE_ADMIN];
                $here[2] = substr($this->Controller->params['action'], strlen($this->Controller->params[CAKE_ADMIN]) + 1);
            } else {
                $here[2] = $this->Controller->params['action'];
            }
            $here[1] = Inflector::underscore($this->Controller->params['controller']);
            ksort($here);
            $url = implode('/', $here);
                    
            // Set up the default configuration vars
            $this->params = array(
                'mode' => 'Sliding',
                'perPage' => 10,
                'delta' => 5,
                'totalItems' => '',
                'httpMethod' => 'GET',
                'currentPage' => 1,
                'linkClass' => 'pager',
                'altFirst' => 'First page',
                'altPrev '=> 'Previous page',
                'altNext' => 'Next page',
                'altLast' => 'Last page',
                'separator' => '',
                'spacesBeforeSeparator' => 1,
                'spacesAfterSeparator' => 1,
                'useSessions' => false,
                'firstPagePre'	 => '',
                'firstPagePost' => '',
                'firstPageText' => '<img src="'.$this->Controller->base.'/img/first.gif" alt="">',
                'lastPagePre' => '',
                'lastPagePost' => '',
                'lastPageText' => '<img src="'.$this->Controller->base.'/img/last.gif" alt="">',
                'prevImg' => '<img src="'.$this->Controller->base.'/img/prev.gif" alt="">',
                'nextImg' => '<img src="'.$this->Controller->base.'/img/next.gif" alt="">',
                'altPage' => 'Page',
                'clearIfVoid' => true,
                'append' => false,
                'path' => '',
                'fileName' => $this->Controller->base . DS . $url . DS . '%d',
                'urlVar' => '',
            );
            
            vendor('Pear/Pager/Pager');
            
            // Merge with user config
            $this->params = array_merge($this->params, $config);        
     
            // sanitize requested page number
            if (!in_array($this->params['currentPage'], range(1, ceil($this->params['totalItems'] / $this->params['perPage'])))) {
                $this->params['currentPage'] = 1;
            }
            $this->Pager =& Pager::factory($this->params);
            
            // Set the template vars
            $this->Controller->set('pageLinks',   $this->Pager->getLinks());
            $this->Controller->set('currentPage', $this->params['currentPage']);
            $this->Controller->set('isFirstPage', $this->Pager->isFirstPage());
            $this->Controller->set('isLastPage',  $this->Pager->isLastPage());
        }    
    ?>



3 - More Features
-----------------
You should add other template vars as needed. Those you see are just
the most often needed (by me).



4 - Load the component
----------------------
Don't forget to load the component in your application! Set


PHP Snippet:
````````````

::

    <?php 
    <?php var $components = array('Pager'); ?>
    ?>

in your controller.



5 - Controller setup
--------------------

In your posts_controller.php, do the following:


Controller Class:
`````````````````

::

    <?php 
    function index($page = 1) { 
                     
            // setup the pager 
            $params = array( 
                'perPage'     => 10, 
                'totalItems'  => $this->Post->findCount(), 
                'currentPage' => $page, 
            ); 
            $this->Pager->init($params); 
                     
            // get the data 
            $this->set('data', $this->Post->findAll(null, null, 'Post.created DESC', $this->Pager->params['perPage'], $this->Pager->params['currentPage'])); 
        } 
    ?>

I have chosen the more efficient of the two methods available to feed
data to pager. Instead of selecting all records of a table and let
Pager decide which records to show (which can be very memory intensive
with large result sets), we give Pager the total number of items


PHP Snippet:
````````````

::

    <?php 
    $this->Post->findCount();
    ?>

and fetch only the rows we need for our page to display:


PHP Snippet:
````````````

::

    <?php 
    $this->Post->findAll(null, null, 'Post.created DESC', $this->Pager->params['perPage'], $this->Pager->params['currentPage'];
    ?>



6 - In the view
---------------
You will output the pager (if appropriate) in your index.thtml as
follows:


View Template:
``````````````

::

    
    <?php
    // Display pager if there are pages to display
    if ($pageLinks['all']) {
    	echo '<div id="pager" class="pager">Pages:  ' . $pageLinks['all'] . '</div>';
    }
    ?>



7 - The End
-----------

That's all, folks :)

.. _http://pear.php.net/package/Pager: http://pear.php.net/package/Pager
.. meta::
    :title: Integrating Pear Pager
    :description: CakePHP Article related to pear,Pager,Components
    :keywords: pear,Pager,Components
    :copyright: Copyright 2006 
    :category: components

