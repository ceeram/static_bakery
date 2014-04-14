Layout your cake with jOOmla templates
======================================

by LazyCoder on April 05, 2007

Want free professional layouts for your cake? How to translate a
jOOmla template to a cakephp layout with very little effort.
If you want a layout for your new cake app and don't want to waste
your time you can benefit of thousands of templates made for jOOmla.
The translation from joomla templates to cake layout is a very very
simple.

Let me explain from start:
A jOOmla template is composed of this main files and dirs

[indent] index.php - main template file
css/template_css.css - stylesheet file
images/* - all the images needed from stylesheet and main file
[indent] this can vary but this is the minimum requirements.

The first thing to do is to move the css/* files in your webroot/css
then move the images dir in your webroot.

webroot
|----css
|........template_css.css
|........cake.generic.css
|
|----img/*
|
|----images/*
If you wish to have only one dir for all your images then you must
change all reference to images/ with img/ in your index.php and
template_css.css
Now move your index.php file to your cakeroot/views/layout and rename
it to default.thtml
Ok this is the 50% of job :).
Now we must modify the default.thtml file to meet cake requirements.
This a simple joomla template (2 columns i used for one of my clients)
You can view how it renders `here`_

::

    
    <?php
    defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );
    ob_start("accSub_ob_callback");
    // needed to seperate the ISO number from the language file constant _ISO
    $iso = split( '=', _ISO );
    // Master paths:
    $path 		= $mosConfig_live_site .'/templates/dara';
    $path_images 	= $path .'/images';
    ?>
    < !DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <?php
    if ( $my->id ) {
    	initEditor();
    }
    ?>
    <meta http-equiv="Content-Type" content="text/html; <?php echo _ISO; ?>" />
    <?php mosShowHead(); ?>
    <link rel="shortcut icon" href="<?php echo $mosConfig_live_site;?>/images/favicon.ico" />
    </link><link rel="stylesheet" type="text/css" href="<?php echo $path; ?>/css/template_css.css" />
    <!--[if lt IE 7]>
    	</link><link rel="stylesheet" type="text/css" href="<?php echo $path; ?>/css/template_css_ie.css" />
    < ![endif]-->
    </link></meta></head>
    <body <?php if($option == 'com_frontpage' || $option == '') { echo ' id="home"'; } ?>>
    
    <div id="site">
    	<div id="shadow">
    		<div id="wrapper">
    			<div id="masthead">
    				<h1><?php echo $mosConfig_sitename; ?></h1>
    				<!--<a href="#" title="Click here to sign up now." id="signUpNow">Sign up now!-->
    			</div>
    
    			<div id="mainMenu">
    				<?php mosLoadModules ( 'header', -2 ); ?>
    				<br class="clearing" />
    			</div>
    
    			<div id="content">
    				<?php if($option == 'com_frontpage' || $option == '') { ?>
    				<div id="homead">
    					<?php mosLoadModules ( 'banner', -2 ); ?>
    				</div>
    				<?php } ?>
    				<div id="subMenu"><?php mosLoadModules ( 'left', -2 ); ?></div>
    				<div id="main"><?php mosMainBody(); ?></div>
    				<br class="clearing" />
    			</div>
    
    			<div id="footer">
    			  <a taget="_blank" href="http://www.joomla.org/">jOOmla cms</a>
    			   | Powered by <a taget="_blank" href="http://www.nospace.net/">nospace</a>
                </div>
    		</div>
      	</div>
    </div>
    <map name="HomeMap">
    <area href="index.php?lang=it" shape="polygon" coords="138, 76, 324, 76, 361, 38, 183, 37">
    </area><area href="index.php?lang=en" shape="polygon" coords="142, 130, 327, 129, 359, 93, 186, 93">
    </area><area href="index.php?lang=pt" shape="polygon" coords="141, 184, 330, 184, 364, 148, 187, 147">
    <!--
    <area href="index.php?lang=es" shape="polygon" coords="142, 239, 328, 239, 365, 202, 186, 202">
    -->
    </area></map>
    </body>
    </html>
    <?php ob_end_flush(); ?>

The translation is very simple:
Replace mosMainBody(); jOOmla functions with echo content_for_layout;
And if needed replace the mosLoadModules() functions with
renderElements()

Here the revised template for cake:

::

    
    < !DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <!-- cakePHP header Begin -->
    <title>CakePHP(tm) : <?php echo $title_for_layout;?></title>
    <link rel="icon" href="<?php echo $this->webroot . 'favicon.ico';?>" type="image/x-icon" />
    </link><link rel="shortcut icon" href="<?php echo $this->webroot . 'favicon.ico';?>" type="image/x-icon" />
    <?php 
      echo $html->css('template_css');
      echo $html->css('cake.generic');
      if(isset($javascript)) {
        echo $javascript->link('jquery-latest.js') . "\n";
      }
      echo isset($head) ? $head->registered() : '';
      echo "\n";
    ?>
    <!-- cakePHP header End -->
    </link></head>
    <body id="home">
    <div id="site">
    	<div id="shadow">
    		<div id="wrapper">
    			<div id="masthead">
    			</div>
    			<div id="mainMenu">
    				<!-- cakePHP Top Menu -->
    				<?php echo $this->renderElement('menus/topMenu'); ?>
    				<br class="clearing" />
    			</div>
    			<div id="content">
    				<div id="homead">
    					<?php echo $this->renderElement('banner'); ?>
    				</div>
    				<div id="subMenu">
    					<!-- cakePHP left Menu -->
    					<?php echo $this->renderElement('menus/leftMenu'); ?>
    				</div>
    				<div id="main">
    				    <!-- cakePHP contents -->
    					<?php $session->flash(); ?>
    					<?php echo $content_for_layout; ?>
    				</div>
    				<br class="clearing" />
    			</div>
    			<div id="footer">
    			  <a taget="_blank" href="http://www.joomla.org/">jOOmla cms</a>
    			   | Powered by <a taget="_blank" href="http://www.nospace.net/">nospace</a>
    			   | and <a taget="_blank" href="http://www.cakephp.org/">cakephp</a>
    			</div>
    		</div>
    	</div>
    </div>
    </body>
    </html>

if you want links to tons of free templates go to `jOOmla forum`_ it
is a very good resource also for making templates that render the same
on `different browser`_

.. _here: http://www.italplan.com/
.. _jOOmla forum: http://forum.joomla.org/index.php/topic,25306.0.html
.. _different browser: http://forum.joomla.org/index.php/topic,88778.0.html
.. meta::
    :title: Layout your cake with jOOmla templates
    :description: CakePHP Article related to Layouts,stylesheet,Template,Tutorials
    :keywords: Layouts,stylesheet,Template,Tutorials
    :copyright: Copyright 2007 LazyCoder
    :category: tutorials

