To switch layout
================

by k_k_ramji on October 01, 2008

To switch the layout in cake php
Hi ,

I just create a one code to switch layout when open the site

just paste these code into your appcontroller

here have two layouts iphone and default_normal

Check this 'Cookie','RequestHandler' two components are available in
ur app controller
if not available please add it then

::

    
    var $components = array('Auth', 'Cookie','RequestHandler');
    var $layout = '';

then below code add in beforefilter function

::

    
    function beforeFilter(){
    
    //your some other code appear here
    
    
    
    if(isset($_REQUEST['layout']))
                {
                    $this->layout = $_REQUEST['layout'];
                    $this->Cookie->write('Layout', $this->layout, true,'+2 weeks');
                }
                else
                {
                    if($this->Cookie->read('Layout')!="")
                        $this->layout =$this->Cookie->read('Layout');
                    else
                        $this->layout ="default_normal";
                }
    
    
    
    
    }


if u want to switch layout into iphone
just type
`http://www.domain.com?layout=iphone`_

.. _http://www.domain.com?layout=iphone: http://www.domain.com?layout=iphone

.. author:: k_k_ramji
.. categories:: articles, general_interest
.. tags:: Layouts,iPhone,switch layout,iphone layout,layout
switch,General Interest

