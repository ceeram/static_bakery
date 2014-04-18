Maintain Static Web Pages with Adobe Contribute
===============================================

In a recent CakePHP 1.1 development project I needed to provide end
users with the ability to independently maintain static web pages
using Adobe Contribute. This proved more difficult than I had expected
because of at least four factors a) The URL structure used in a
standard cake app not meeting Contribute requirements; b) The file
extensions used for cake pages; c) The method that Adobe Contribute
uses to confirm that its FTP configuration is pointing to the correct
directory on the web server; and d) The mechanism used by Adobe
Contribute to ensure that 'Guard Pages' are in place to prevent users
from reading draft versions of web pages before they are published.
This solution uses a custom controller to overcome these issues.
I followed the following four steps to get this solution to work for
me:

**1. Configure routes.php**
I made a simple change to routes.php to redirect requests for static
pages, including the site's home page to my custom controller named
display_controller.php:

::

    
    	$Route->connect('/', array('controller' => 'display', 'action' => 'display', 'home.shtml'));
    	$Route->connect('/pages/*', array('controller' => 'display', 'action' => 'display'));

**2. Change file extension for static pages** I then, somewhat
regrettably, changed the file extension on all of the files in the
/views/pages directory from .thtml to .shtml. I could have used .html,
.htm and several other options. However I have been unable to persuade
Contribute to allow me to edit files with a .thtml extension and so
eventually decided to change to a supported file extension.

**3. Implement controller to display static pages**
The following controller code implemented the capability to support
Contribute editing:


Controller Class:
`````````````````

::

    <?php 
    define('SITE_HOME_PAGE','home.shtml');
    define('GUARD_PAGE','index.html');
    define('ADOBE_TEST_FILE_PREFIX','pages/TMP');
    
    class DisplayController extends AppController {
    	var $uses = array();
    	var $helpers = array('Html', 'Javascript', 'Form');
    
     /*
      * This function displays static pages in a manner that will permit them to be edited using Contribute
      */
    function display () {
    	if (empty($this->data)){
    
    		$this->layout = 'default';
    
    		if (isset($_GET['url'])) {
    			$path = $_GET['url'];
    			// If there is a trailing slash drop it
    			if (substr($path,strlen($path)-1) == '/') {$path =substr($path,0,strlen($path)-1);};
    
    			if (is_dir(VIEWS . $path)) {
    				if (substr($path,0,9) == ADOBE_TEST_FILE_PREFIX) {
    					$this->layout = 'blank';
    					$viewFullPath = VIEWS . $path . DS . GUARD_PAGE;
    			 }
    			 else {
    			 	$viewFullPath = VIEWS . $path . DS . SITE_HOME_PAGE;
    			 }
    			}
    			else {
    				if (is_file(VIEWS . $path)) {
    					$viewFullPath = VIEWS . $path;
    				} else {
    					$this->redirect('/pages/' . SITE_HOME_PAGE);
    					exit;
    				}
    			};
    			$this->render(null, null,$viewFullPath);
    		} else { 
    	 $this->redirect('/pages/' . SITE_HOME_PAGE);}
    	}
    }
    }
    ?>

**4. Create a blank layout** You will also note that this controller
action requires a new view layout which I chose to call 'blank'. This
layout renders the page content without any header or footer. This is
required because when checking for Guard Pages, Contribute checks to
ensure that the page it reads over HTTP is exactly the same as thethe
page it has written over FTP. For the sake of completeness the code
for the blank template is as follows:


View Template:
``````````````

::

    
    echo $content_for_layout;

Hopefully this is helpful to someone. Any problems setting this up
please let me know



.. author:: Dino
.. categories:: articles, snippets
.. tags:: contribute,adobe,adobe contribute,Snippets

