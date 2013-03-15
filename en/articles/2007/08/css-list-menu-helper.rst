css list-menu helper
====================

by %s on August 28, 2007

A helper (method) to create a css formatable list-menu.
I was looking for a good way to creating css formated list-menus that
allows me to make an menu-element active. I found this solution
`http://www.thinkingphp.org/2007/07/08/macgyver-menu-for-cakephp-
whats-the-active-menu-item/`_
But i think it's not that good if you want to reuse it. So i have
improved the code. Thanks to gwoo for some suggestions.

Here's my custom helper using the htmlhelper:


Helper Class:
`````````````

::

    <?php 
    class CommunityHelper extends AppHelper
    {
    	var $helpers = array('Html');
    	
    	function menu($links = array(),$htmlAttributes = array(),$type = 'ul')
    	{      
    		$this->tags['ul'] = '<ul%s>%s</ul>';
    		$this->tags['ol'] = '<ol%s>%s</ol>';
    		$this->tags['li'] = '<li%s>%s</li>';
    		$out = array();		
    		foreach ($links as $title => $link)
    		{
    			if($this->url($link) == substr($this->here,0,-1))
    			{
    				$out[] = sprintf($this->tags['li'],' class="active"',$this->Html->link($title, $link));
    			}
    			else
    			{
    				$out[] = sprintf($this->tags['li'],'',$this->Html->link($title, $link));
    			}
    		}
    		$tmp = join("\n", $out);
    		return $this->output(sprintf($this->tags[$type],$this->_parseAttributes($htmlAttributes), $tmp));
    	}
    }
    ?>



View Template:
``````````````

::

    
    <?php
    echo $community->menu(array(
    	'Home' => array('controller' => ''),
    	'Posts' => array('controller' => 'posts'),
        'New Post' => array('controller' => 'posts', 'action' => 'new'),
        'My Profile' => array('controller' => 'users', 'action' => 'myprofile',0 => 'settings')),
        array('class' => 'submenu')
        );
    ?>

And here's the pending ticket `https://trac.cakephp.org/ticket/3144`_
I hope it's going to make its way into cake soon.

Note: I've tested it only in cakephp 1.2.

I hope this helps not only me :)

.. _http://www.thinkingphp.org/2007/07/08/macgyver-menu-for-cakephp-whats-the-active-menu-item/: http://www.thinkingphp.org/2007/07/08/macgyver-menu-for-cakephp-whats-the-active-menu-item/
.. _https://trac.cakephp.org/ticket/3144: https://trac.cakephp.org/ticket/3144
.. meta::
    :title: css list-menu helper
    :description: CakePHP Article related to menu helper,menu,css-menu,menus,list-menu,Helpers
    :keywords: menu helper,menu,css-menu,menus,list-menu,Helpers
    :copyright: Copyright 2007 
    :category: helpers

