

Eclipse Code Completion in views using $this
============================================

by %s on March 27, 2012

If you are having problems getting eclipse to autocomplete helpers in
views. Try this
Create this file ( helper_complete.php ) add to your app/View
directory, refresh your project and all should work. ( it does for me
anyway! ) Add in helpers as you need

PHP Snippet:
````````````

::

    <?php 
    App::uses('AppHelper', 'Helper');
    /**
     * this Helper
     *
     * @property Html $Html
     * @property Session $Session
     * @property Form $Form
     */
    class this extends AppHelper
    {
    	var $Html;
    	var $Session;
    	var $Form;
    	
    	public function __contruct()
    	{
    		$this->Html = new HtmlHelper($View);
    		$this->Session = new SessionHelper($View);		
    		$this->Form = new FormHelper($View);		
    	}
    }
    
    $this = new this();
    ?>


.. meta::
    :title: Eclipse Code Completion in views using $this
    :description: CakePHP Article related to autocomplete,Eclipse,intellisense,Articles
    :keywords: autocomplete,Eclipse,intellisense,Articles
    :copyright: Copyright 2012 
    :category: articles

