Automatic Layout Switcher
=========================

This component allows you to have two layouts for one site and
switches between them automatically based on the domain.


Download
````````
You can download a zip of the component at
`http://sandbox.pseudocoder.com/demo/layout`_.


Example
```````
`http://sandbox.pseudocoder.com`_ and `http://sandbox.siteamonth.com`_
are actually the same site. Using the LayoutSwitcher component changes
the design based on which URL your are visiting.


Usage
`````

#. CakePHP either 1.1 or 1.2
#. Download the component and unzip the contents to
   app/controllers/components.
#. Include the component in your /app/app_contoller.php so that it
   will work on every page.

::

    var $components = array('LayoutSwitcher');  

#. Create layout files for each domain you want to have a distinct
   look. The templates should be named domain.thtml and placed in the
   normal layout folder, /app/views/layouts. For example the sites
   referenced above have layouts sandbox.pseudocoder.com.thtml and
   sandbox.siteamonth.com.thtml. Leave off the "www.".



Tips
````

#. Make a symlink from default.thtml to the layout to be defaulted to
   in case someone reaches the site from some other url.
#. Organize your CSS in a similar way to your layouts. The two
   stylesheets for the example sites are pseduocoder.style.css and
   sitamonth.style.css. You can have a third, common.style.css, that is
   included first for styles that are common to both sites.
#. You can always override the automatically selected layout in your
   controller in the same way you'd override the default layout.
#. This component doesn't work if cache is enabled. All domains will
   get the cached view.



Component Class:
````````````````

::

    <?php 
    /*
     * Layout Switcher CakePHP Component
     * Copyright (c) 2007 Matt Curry
     * www.PseudoCoder.com
     *
     * @author      mattc <matt@pseudocoder.com>
     * @version     1.0
     * @license     MIT
     *
     */
    
    class LayoutSwitcherComponent extends Object {
      var $controller;
      var $components = array('RequestHandler');
    
      function startup(&$controller) {
        uses('Folder');
    
        $this->controller = $controller;
    
        //get the domain used
        $domain = $_SERVER['HTTP_HOST'];
    
        //remove any www.
        $domain = str_replace('www.', '', $domain);
    
        //check if a layout exists for this server
        $folder =& new Folder(LAYOUTS);
        $files = $folder->find($domain . '.(thtml|ctp)');
    
        //should only be one match
        if (count($files) != 1) {
          return;
        }
    
        //set the layout
        //only if not ajax
        if(!$this->RequestHandler->isAjax()) {
          $this->controller->layout = $domain;
        }
      }
    }
    ?>



.. _http://sandbox.siteamonth.com: http://sandbox.siteamonth.com
.. _http://sandbox.pseudocoder.com: http://sandbox.pseudocoder.com/
.. _http://sandbox.pseudocoder.com/demo/layout: http://sandbox.pseudocoder.com/demo/layout

.. author:: mattc
.. categories:: articles, components
.. tags:: component,switch,Components

