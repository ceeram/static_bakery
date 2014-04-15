cakeinfo() helper for cakePHP
=============================

by Siegfried on March 13, 2007

As a newbie in cakePHP it is often hard to get all the constants right
and not always easy to recognise, what is already defined in the
cakePHP core of constants.
Sometimes you know, there is a constant in cakePHP, that delivers the
right path or the right information out of the box. But do you always
remember?

This little helper is useful, if you want to check all your path and
constant informations to make sure everything is running smooth.


Helper Class:
`````````````

::

    <?php 
    class ConstHelper extends AppHelper {
    
      function cakeinfo() {
          $output = '';
          $output .= '<h2>Core Defines</h2>';
          $output .= 'ACL_CLASSNAME = '.ACL_CLASSNAME.'<br>';
          $output .= 'ACL_FILENAME = '.ACL_FILENAME.'<br>';
          $output .= 'AUTO_SESSION = '.AUTO_SESSION.'<br>';
          $output .= 'CACHE_CHECK = '.CACHE_CHECK.'<br>';
          if (defined('CAKE_ADMIN')) {
              $output .= 'CAKE_ADMIN = '.CAKE_ADMIN.'<br>';
          }
          $output .= 'CAKE_SECURITY = '.CAKE_SECURITY.'<br>';
          $output .= 'CAKE_SESSION_COOKIE = '.CAKE_SESSION_COOKIE.'<br>';
          $output .= 'CAKE_SESSION_SAVE = '.CAKE_SESSION_SAVE.'<br>';
          $output .= 'CAKE_SESSION_STRING = '.CAKE_SESSION_STRING.'<br>';
          $output .= 'CAKE_SESSION_TABLE = '.CAKE_SESSION_TABLE.'<br>';
          $output .= 'CAKE_SESSION_TIMEOUT = '.CAKE_SESSION_TIMEOUT.'<br>';
          $output .= 'COMPRESS_CSS = '.COMPRESS_CSS.'<br>';
          $output .= 'DEBUG = '.DEBUG.'<br>';
          $output .= 'LOG_ERROR = '.LOG_ERROR.'<br>';
          $output .= 'MAX_MD5SIZE = '.MAX_MD5SIZE.'<br>';
          $output .= 'WEBSERVICES = '.WEBSERVICES.'<br>';
    
          $output .= '<h2>Webroot Configurable Paths</h2>';
          $output .= 'CORE_PATH = '.CORE_PATH.'<br>';
          $output .= 'WWW_ROOT = '.WWW_ROOT.'<br>';
          $output .= 'ROOT = '.ROOT.'<br>';
          $output .= 'WEBROOT_DIR = '.WEBROOT_DIR.'<br>';
    
          $output .= '<h2>Paths</h2>';
          $output .= 'APP = '.APP.'<br>';
          $output .= 'APP_DIR = '.APP_DIR.'<br>';
          $output .= 'APP_PATH = '.APP_PATH.'<br>';
          $output .= 'CACHE = '. CACHE.'<br>';
          $output .= 'CAKE = '.CAKE.'<br>';
          $output .= 'COMPONENTS = '.COMPONENTS.'<br>';
          $output .= 'CONFIGS = '.CONFIGS.'<br>';
          $output .= 'CONTROLLER_TESTS = '.CONTROLLER_TESTS.'<br>';
          $output .= 'CONTROLLERS = '.CONTROLLERS.'<br>';
          $output .= 'CSS = '.CSS.'<br>';
          $output .= 'ELEMENTS = '.ELEMENTS.'<br>';
          $output .= 'HELPER_TESTS = '.HELPER_TESTS.'<br>';
          $output .= 'HELPERS = '.HELPERS.'<br>';
          $output .= 'INFLECTIONS = '.INFLECTIONS.'<br>';
          $output .= 'JS = '.JS.'<br>';
          $output .= 'LAYOUTS = '.LAYOUTS.'<br>';
          $output .= 'LIB_TESTS = '.LIB_TESTS.'<br>';
          $output .= 'LIBS = '.LIBS.'<br>';
          $output .= 'LOGS = '.LOGS.'<br>';
          $output .= 'MODEL_TESTS = '.MODEL_TESTS.'<br>';
          $output .= 'MODELS = '.MODELS.'<br>';
          $output .= 'SCRIPTS = '.SCRIPTS.'<br>';
          $output .= 'TESTS = '.TESTS.'<br>';
          $output .= 'TMP = '.TMP.'<br>';
          $output .= 'VENDORS = '.VENDORS.'<br>';
          $output .= 'VIEWS = '.VIEWS.'<br>';
    
          return $output;
      }
    
    
    }
    ?>



.. author:: Siegfried
.. categories:: articles, helpers
.. tags:: configuration,cake,config,constants,Helpers

