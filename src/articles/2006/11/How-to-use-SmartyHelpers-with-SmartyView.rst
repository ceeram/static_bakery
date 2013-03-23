How to use SmartyHelpers with SmartyView
========================================

by %s on November 05, 2006

Direct SmartyView
([url]http://bakery.cakephp.org/articles/view/124[/url]) usage in Cake
has some limitations, the SmartyHelpers are here to help you out.


Introduction
~~~~~~~~~~~~
This assumes you've installed and have SmartyView functioning
`http://bakery.cakephp.org/articles/view/124`_.

The SmartyHelper classes are a set of wrappers for the helpers in
core. They are based on the php5 reflection api to provide the glue
between the Smarty and the php call.

Cakeforge Package:
`http://cakeforge.org/snippet/detail.php?type=package=31`_
Helper Classes:
SmartyAjax SmartyCache SmartyForm SmartyHtml SmartyJavascript
SmartyNumber SmartySession SmartyText SmartyTime


Calls
`````
To use the relevant helper add it to your Controller's helpers member
var.
e.g.

::

    var $helpers = array('SmartyHelper','SmartyJavascript');

Then in your templates (tpl files) use it as such:

::

    {html func=image path='url/to/image.png'}

Parameters that don't match any expected parameters will be added to
the first parameter that expects an array (often htmlAttributes).
This means

::

    {html func=image path='url/to/image.png' class='image old' border='0'}

will call (essentially)

::

    <?php echo $html->image('url/to/image.png', array('class'=>'image old', 'border'=>'0')); ?>

All SmartyHelper have a special debug parameter: __show_call=true You
can pass __show_call=true to any SmartyHelper method to show how your
call is being interpreted.

::

    {html func=image path='url/to/image.png' class='image old' border='0' __show_call=true}

Generates:

::

    SmartyHtml calling $html->image with these parameters: 
    array(3) {
    
      ["path"]=>
      string(16) "url/to/image.png"
    
      ["htmlAttributes"]=>
      array(2) {
    
        ["class"]=>
        string(9) "image old"
    
        ["border"]=>
        string(1) "0"
    
      }
    
      ["return"]=>
      bool(false)
    
    }
    



Example Usage
~~~~~~~~~~~~~

Smarty (tpl):

::

    {html func=charset charset='test'}
    {html func=css path='style' class='oi' style='text-align: right'}
    {html func=image path='http://manual.cakephp.org/css/images/logo-mini.gif' class='image old' border='0'}
    {html func=link title='title & shit' url='http://www.example.com' align="right" style="styled" confirmMessage='confirmation message' escapeTitle=true}
    
    {html func=image path='http://manual.cakephp.org/css/images/logo-mini.gif' class='image old' border='0' assign='imageTitle'}
    {html func=link title=$imageTitle url='http://www.example.com' escapeTitle=false}
    
    {html func=radio fieldName='Model/field' options_value1='option1' options_value2='option2' inbetween='<hr />' class='radioclass'}

Equivalent without Smarty: Straight php (thtml):

::

    <?= $html->charset('test'); ?>
    <?= $html->css('style','stylesheet',array('class'=>'oi','style'=>'text-align: right')); ?>
    <?= $html->image('http://manual.cakephp.org/css/images/logo-mini.gif', array('class'=>'image old', 'border'=>'0')) ?>
    <?= $html->link('title & shit','http://www.example.com',array('align'=>'right', 'style'=>'styled'), 'confirmation message', true); ?>
    <?= $html->link( $html->image( 'http://manual.cakephp.org/css/images/logo-mini.gif', array('class'=>'image old', 'border'=>'0')), 'http://www.example.com', null, false, false ); ?>
    <?= $html->radio('Model/field', array('value1' => 'option1', 'value2' => 'option2'), '<hr />', array('class'=>'radioclass')); ?>



.. _http://bakery.cakephp.org/articles/view/124: http://bakery.cakephp.org/articles/view/124
.. _=31: http://cakeforge.org/snippet/detail.php?type=package&id=31
.. meta::
    :title: How to use SmartyHelpers with SmartyView
    :description: CakePHP Article related to smartyview,smarty,Helpers
    :keywords: smartyview,smarty,Helpers
    :copyright: Copyright 2006 
    :category: helpers

