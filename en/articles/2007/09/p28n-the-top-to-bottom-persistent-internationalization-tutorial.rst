p28n, the top to bottom persistent internationalization tutorial.
=================================================================

by %s on September 12, 2007

For some developers, allowing a website to support multiple languages
is essential. Luckily cakePHP 1.2 has the foundations available to
make this possible.
Before forging ahead, I'd like a disclaimer. I don't claim this
tutorial to be uniquely mine, it's an amalgamation of techniques from
several pages and sites. Neither is it the most comprehensive and in-
depth guide.

That said, I certainly hope after using this guide you can quickly and
easily implement multiple languages in your cake app without needing
to skip around the place. If I fall short of this and you have
suggestions, leave a comment.

Once you complete this tutorial your site will be able to:

#. display multiple languages
#. allow users to switch languages
#. store language settings in cookies, so returning visitors don't
   need to re-select their preferred language

The sites that I build typically require 3 languages:

#. British English(en-gb)
#. Simplified Chinese(zh-cn)
#. Traditional Chinese(zh-tw)

So throughout this document, I'll be using them as my reference
languages. Your site may support more or less languages.

Step 1: Setup the directories for your messages
-----------------------------------------------

$ cd cake/app/locale/

$ mkdir en_gb

$ mkdir en_gb/LC_MESSAGES

$ mkdir zh_tw

$ mkdir zh_tw/LC_MESSAGES

$ mkdir zh_cn

$ mkdir zh_cn/LC_MESSAGES
This will create the minimum folders required for each language our
site needs to support.

To find your language code(s) refer to `http://api.cakephp.org/1.2
/l10n_8php-source.html#l00180`_

::

    //this is only a sample. don't add this to your code.
    'nl' => array(
    	'language' => 'Dutch (Standard)', 
    	'locale' => 'dut', 
    	'localeFallback' => 'dut', 
    	'charset' => 'utf-8'
    ),
    'pl' => array(
    	'language' => 'Polish', 
    	'locale' => 'pol', 
    	'localeFallback' => 'pol', 
    	'charset' => 'utf-8'
    ),
    'sk' => array(
    	'language' => 'Slovack', 
    	'locale' => 'slo', 
    	'localeFallback' => 'slo', 
    	'charset' => 'utf-8'
    ),

By studying the sample above, we can see that directory names are
actually the locale:

+ 'dut' is the correct directory name(locale) for 'nl'
+ 'pol' is the correct directory name(locale) for 'pl'
+ 'slo' is the correct directory name(locale) for 'sk'


Step 2: Write some strings to translate.
----------------------------------------

View Template:
``````````````

::

    <?php $this->pageTitle = __('pageTitle_home', true); ?>
    
    <h1><?php __('welcome_heading'); ?></h1>
    <?php __('lipsum'); ?><?php __('lipsum'); ?><?php __('lipsum'); ?><br/>
    <?php __('lipsum'); ?><?php __('lipsum'); ?><?php __('lipsum'); ?><br/>
    <?php __('footer_copyright'); ?>

note : The 2nd parameter controls whether a message should be returned
or echo'd
So when you are working with a template, use:

PHP Snippet:
````````````

::

    <?php __('my_name');?>

And when you are working with code, use:

PHP Snippet:
````````````

::

    <?php $name = __('my_name', true);?>


Step 3: Let's build a database from our PHP and templates.
----------------------------------------------------------

$ cd cake/app/

$ cake extract
This will recursively go through all the folders and check both your
.php and .ctp files for all of those __() functions you typed. Once
it's complete, you should have a nice message template file named
default.pot file inside cake/app/locale/

So let's copy this message template file into the right directories
for each language.

$ cd cake/app/locale/

$ cp default.pot locale/en_gb/LC_MESSAGES/default.po

$ cp default.pot locale/zh_tw/LC_MESSAGES/default.po

$ cp default.pot locale/zh_cn/LC_MESSAGES/default.po

note : at this point in time, you can freely edit the default.po
files(they're just text) and start translating strings. Changes made
to these files will automatically be rendered in your views.

Here are some short snippets from my default.po files.

::

    // locale/zh_cn/LC_MESSAGES/default.po
    msgid "footer_copyright"
    msgstr "教育局 © 2007. 版权所有"
    
    // locale/zh_tw/LC_MESSAGES/default.po
    msgid "footer_copyright"
    msgstr "教育局 © 2007. 版權所有"
    
    // locale/en_gb/LC_MESSAGES/default.po
    msgid "footer_copyright"
    msgstr "Education Bureau © 2007. All rights reserved."


Step 4: Change the default language
-----------------------------------
A fresh install of cakePHP is set to use American English, so for the
rest of us: we need that changed.

::

    // config/bootstrap.php
    define(DEFAULT_LANGUAGE, 'zh-tw');


Step 5: Let users change the language
-------------------------------------

Component Class:
````````````````

::

    <?php 
    class P28nComponent extends Object {
    	var $components = array('Session', 'Cookie');
    
    	function startup() {
    		if (!$this->Session->check('Config.language')) {
    			$this->change(($this->Cookie->read('lang') ? $this->Cookie->read('lang') : DEFAULT_LANGUAGE));
    		}
    	}
    
    	function change($lang = null) {
    		if (!empty($lang)) {
    			$this->Session->write('Config.language', $lang);
    			$this->Cookie->write('lang', $lang, null, '+350 day'); 
    		}
    	}
    }
    ?>

Thanks Nasko for pointing out that Cookie->write() does not accept
timestamps

Controller Class:
`````````````````

::

    <?php 
    class P28nController extends AppController {
    	var $name = 'P28n';
    	var $uses = null;
    	var $components = array('P28n');
    
    	function change($lang = null) {
    		$this->P28n->change($lang);
    
    		$this->redirect($this->referer(null, true));
    	}
    
    	function shuntRequest() {
    		$this->P28n->change($this->params['lang']);
    
    		$args = func_get_args();
    		$this->redirect("/" . implode("/", $args));
    	}
    }
    ?>


Controller Class:
`````````````````

::

    <?php 
    //app_controller.php
    class AppController extends Controller {
    	var $components = array('P28n');
    }
    ?>

The final piece of code, are some custom routes that need to be added
to cake/app/config/routes.php

::

    <?php
    //route to switch locale
    Router::connect('/lang/*', array('controller' => 'p28n', 'action' => 'change'));
    
    //forgiving routes that allow users to change the lang of any page
    Router::connect('/eng?/*', array(
    	'controller' => "p28n",
    	'action' => "shuntRequest",
    	'lang' => 'en-gb'
    ));
    
    Router::connect('/zh[_-]tw/*', array(
    	'controller' => "p28n",
    	'action' => "shuntRequest",
    	'lang' => 'zh-tw'
    ));
    
    Router::connect('/zh[_-]cn/*', array(
    	'controller' => "p28n",
    	'action' => "shuntRequest",
    	'lang' => 'zh-cn'
    ));
    ?>


Step 6: Links to change language
--------------------------------

View Template:
``````````````

::

    <h1><?php __('welcome_heading'); ?></h1>
    <?php __('lipsum'); ?><?php __('lipsum'); ?><?php __('lipsum'); ?><br/>
    <?php __('lipsum'); ?><?php __('lipsum'); ?><?php __('lipsum'); ?><br/>
    <?php __('footer_copyright'); ?>
    
    <!-- these links will change the language, but allow the user to stay on this page //-->
    <?php echo $html->link($html->image('en_gb.gif'), '/lang/en-gb', null, null, false); ?>
    <?php echo $html->link($html->image('zh_tw.gif'), '/lang/zh-tw', null, null, false); ?>
    <?php echo $html->link($html->image('zh_cn.gif'), '/lang/zh-cn', null, null, false); ?>
    
    <!-- these links will change the language, then forward the user to the /news page //-->
    <?php echo $html->link($html->image('en_gb.gif'), '/en-gb/news', null, null, false); ?>
    <?php echo $html->link($html->image('zh_tw.gif'), '/zh-tw/news', null, null, false); ?>
    <?php echo $html->link($html->image('zh_cn.gif'), '/zh-cn/news', null, null, false); ?>


Step 7: All done.
-----------------
Assuming I have included all the right code and not forgotten
anything, you should now be fully functional^^


Further reading
---------------
A popular cross-platform GUI tool for managing .po files is poEdit
`http://www.poedit.net/`_ Gettext and cakePHP supports much more than
word for word, literal translations. Check out __(), __c(), __d(),
__dc(), __dcn(), __dn(), __n()
`http://api.cakephp.org/1.2/basics_8php.html`_
..and finally, remember that utf-8 is your friend. treat it well, and
it'll reciprocate.

.. _http://www.poedit.net/: http://www.poedit.net/
.. _http://api.cakephp.org/1.2/basics_8php.html: http://api.cakephp.org/1.2/basics_8php.html
.. _http://api.cakephp.org/1.2/l10n_8php-source.html#l00180: http://api.cakephp.org/1.2/l10n_8php-source.html#l00180
.. meta::
    :title: p28n, the top to bottom persistent internationalization tutorial.
    :description: CakePHP Article related to i18n,UTF8,multibyte,l10n,language support,p28n,multiple languages,Tutorials
    :keywords: i18n,UTF8,multibyte,l10n,language support,p28n,multiple languages,Tutorials
    :copyright: Copyright 2007 
    :category: tutorials

