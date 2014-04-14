Gravatar Helper
===============

by predominant on August 20, 2009

From the Gravatar Website: A gravatar, or globally recognized avatar,
is quite simply an avatar image that follows you from weblog to weblog
appearing beside your name when you comment on gravatar enabled sites.
Avatars help identify your posts on web forums, so why not on weblogs?
Gravatars are a great addition to any blog, or indeed any user
submitted content. This helper is designed to provide gravatar output
without the fuss


Introduction
~~~~~~~~~~~~

This is my first community posted code. Criticism welcome, but please
be kind.

There isn't much to this Helper, so I will jump right in.

The code is available at the end of this article, and is also hosted
on Github: `http://github.com/predominant/CakePHP-Goodies`_


Usage
~~~~~

More than likely you just want to use the thing... Well, its as simple
as this:


Including the helper in your application
````````````````````````````````````````

As with any helper, ensure that it is included in the helpers array in
your controller. This will allow you access to it when you need it, in
your views.


Controller
++++++++++

::

    class MyController extends AppController {
      public $helpers = array('Gravatar');
      // ...
    }



View
++++

Basic Gravatar with default settings:

::

    <?php echo $gravatar->image('someone@cakeisawesome.com'); ?>


Altering the default gravatar:

::

    <?php echo $gravatar->image(
      'someone@cakeisawesome.com',
      array(
        'default' => 'identicon'
      ); ?>


Altering the default gravatar with a custom image:

::

    <?php echo $gravatar->image(
      'someone@cakeisawesome.com',
      array(
        'default' => 'http://mysite.com/defaultavatar.png'
      )); ?>


Changing the gravatar size:

::

    <?php echo $gravatar->image(
      'someone@cakeisawesome.com',
      array(
        'default' => 'identicon',
        'size' => 120
      ); ?>


Adjusting the gravatar ratings to display:

::

    <?php echo $gravatar->image(
      'someone@cakeisawesome.com',
      array(
        'rating' => 'x'
      ); ?>


Including image filename extension on the generated URL:

::

    <?php echo $gravatar->image(
      'someone@cakeisawesome.com',
      array(
        'default' => 'identicon',
        'ext' => true
      ); ?>



Options
~~~~~~~

The options and their valid values are as follows:

default

+ 'identicon' (sample: `http://www.gravatar.com/avatar/3b3be63a4c2a439
  b013787725dfce802?d=identicon`_)
+ 'monsterid' (sample: `http://www.gravatar.com/avatar/3b3be63a4c2a439
  b013787725dfce802?d=monsterid`_)
+ 'wavatar' (sample: `http://www.gravatar.com/avatar/3b3be63a4c2a439b0
  13787725dfce802?d=wavatar`_)
+ URL to image (example: `http://mysite.com/defaultavatar.png`_)



size

+ Minimum value 1
+ Maximum value 512

In the event that your Gravatar size is outside these bounds, the
helper will adjust it to be size 1 or 512, depending which side of the
allowed range your specified value was.


rating

+ 'g'
+ 'pg'
+ 'r'
+ 'x'

If rating is either invalid or not specified, Gravatars will
automatically be delivered for the 'g' rating. These ratings should be
reasonably self explainatory.


ext

+ true
+ false

If not supplied, an image filename extension will not be included as
part of the gravatar generation.



Helper Code
~~~~~~~~~~~

Helper Class:
`````````````

::

    <?php 
    <?php
    App::import(array('Security', 'Validation'));
    
    /**
     * CakePHP Gravatar Helper
     *
     * A CakePHP View Helper for the display of Gravatar images (http://www.gravatar.com)
     *
     * @copyright Copyright 2010, Graham Weldon
     * @license http://www.opensource.org/licenses/mit-license.php The MIT License
     * @package goodies
     * @subpackage goodies.tests.cases.helpers
     *
     */
    class GravatarHelper extends AppHelper {
    
    /**
     * Gravatar avatar image base URL
     *
     * @var string
     * @access private
     */
    	private $__url = array(
    		'http' => 'http://www.gravatar.com/avatar/',
    		'https' => 'https://secure.gravatar.com/avatar/'
    	);
    
    /**
     * Hash type to use for email addresses
     *
     * @var string
     * @access private
     */
    	private $__hashType = 'md5';
    
    /**
     * Collection of allowed ratings
     *
     * @var array
     * @access private
     */
    	private $__allowedRatings = array('g', 'pg', 'r', 'x');
    
    /**
     * Default Icon sets
     *
     * @var array
     * @access private
     */
    	private $__defaultIcons = array('none', 'identicon', 'monsterid', 'wavatar', '404');
    
    /**
     * Default settings
     *
     * @var array
     * @access private
     */
    	private $__default = array(
    		'default' => null,
    		'size' => null,
    		'rating' => null,
    		'ext' => false);
    
    /**
     * Helpers used by this helper
     *
     * @var array
     * @access public
     */
    	public $helpers = array('Html');
    
    /**
     * Constructor
     *
     * @access public
     */
    	public function __construct() {
    		// Default the secure option to match the current URL.
    		$this->__default['secure'] = env('HTTPS');
    	}
    
    /**
     * Show gravatar for the supplied email address
     *
     * @param string $email Email address
     * @param array $options Array of options, keyed from default settings
     * @return string Gravatar image string
     * @access public
     */
    	public function image($email, $options = array()) {
    		$imageUrl = $this->url($email, $options);
    		unset($options['default'], $options['size'], $options['rating'], $options['ext']);
    		return $this->Html->image($imageUrl, $options);
    	}
    
    /**
     * Generate image URL
     *
     * @param string $email Email address
     * @param string $options Array of options, keyed from default settings
     * @return string Gravatar Image URL
     * @access public
     */
    	public function url($email, $options = array()) {
    		$options = $this->__cleanOptions(array_merge($this->__default, $options));
    		$ext = $options['ext'];
    		$secure = $options['secure'];
    		unset($options['ext'], $options['secure']);
    		$protocol = $secure === true ? 'https' : 'http';
    
    		$imageUrl = $this->__url[$protocol] . $this->__emailHash($email, $this->__hashType);
    		if ($ext === true) {
    			// If 'ext' option is supplied and true, append an extension to the generated image URL.
    			// This helps systems that don't display images unless they have a specific image extension on the URL.
    			$imageUrl .= '.jpg';
    		}
    		$imageUrl .= $this->__buildOptions($options);
    		return $imageUrl;
    	}
    
    /**
     * Sanitize the options array
     *
     * @param array $options Array of options, keyed from default settings
     * @return array Clean options array
     * @access private
     */
    	private function __cleanOptions($options) {
    		if (!isset($options['size']) || empty($options['size']) || !is_numeric($options['size'])) {
    			unset($options['size']);
    		} else {
    			$options['size'] = min(max($options['size'], 1), 512);
    		}
    
    		if (!$options['rating'] || !in_array(mb_strtolower($options['rating']), $this->__allowedRatings)) {
    			unset($options['rating']);
    		}
    
    		if (!$options['default']) {
    			unset($options['default']);
    		} else {
    			if (!in_array($options['default'], $this->__defaultIcons) && !Validation::url($options['default'])) {
    				unset($options['default']);
    			}
    		}
    		return $options;
    	}
    
    /**
     * Generate email address hash
     *
     * @param string $email Email address
     * @param string $type Hash type to employ
     * @return string Email address hash
     * @access private
     */
    	private function __emailHash($email, $type) {
    		return Security::hash(mb_strtolower($email), $type);
    	}
    
    /**
     * Build Options URL string
     *
     * @param array $options Array of options, keyed from default settings
     * @return string URL string of options
     * @access private
     */
    	private function __buildOptions($options = array()) {
    		$gravatarOptions = array_intersect(array_keys($options), array_keys($this->__default));
    		if (!empty($gravatarOptions)) {
    			$optionArray = array();
    			foreach ($gravatarOptions as $key) {
    				$value = $options[$key];
    				$optionArray[] = $key . '=' . mb_strtolower($value);
    			}
    			return '?' . implode('&', $optionArray);
    		}
    		return '';
    	}
    }
    ?>
    ?>



Final Note
~~~~~~~~~~

My final note is with regard to the options provided to the helper.
Given that the HTML helper already deals extensively with images, it
is used to process the actual image tage and return it. Thus,
providing any Html Helper image options will ensure they are passed
through the gravatar component and onto the Html component, rendering
as you would naturally expect from the core Html helper.

Comments and suggestions are encouraged.

If you are using this on your site, let me know!

Code also available on Github: `http://github.com/predominant/CakePHP-
Goodies`_


.. _http://www.gravatar.com/avatar/3b3be63a4c2a439b013787725dfce802?d=wavatar: http://www.gravatar.com/avatar/3b3be63a4c2a439b013787725dfce802?d=wavatar
.. _http://github.com/predominant/CakePHP-Goodies: http://github.com/predominant/CakePHP-Goodies
.. _http://www.gravatar.com/avatar/3b3be63a4c2a439b013787725dfce802?d=identicon: http://www.gravatar.com/avatar/3b3be63a4c2a439b013787725dfce802?d=identicon
.. _http://mysite.com/defaultavatar.png: http://mysite.com/defaultavatar.png
.. _http://www.gravatar.com/avatar/3b3be63a4c2a439b013787725dfce802?d=monsterid: http://www.gravatar.com/avatar/3b3be63a4c2a439b013787725dfce802?d=monsterid
.. meta::
    :title: Gravatar Helper
    :description: CakePHP Article related to image,blog,gravatar,icon,avatar,predominant,Helpers
    :keywords: image,blog,gravatar,icon,avatar,predominant,Helpers
    :copyright: Copyright 2009 predominant
    :category: helpers

