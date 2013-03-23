Handle database connection errors
=================================

by %s on July 08, 2009

If like me you always wanted to handle database connection problem
(like displaying a specific message in production or development
mode), here is a way to achieve it.
As far as I understood (for cake 1.2 beta revision version 1157),
cakephp initiates database connection when the controller loads
models. But it fails to find out there is no working connection with
the database and doesnt display the message, so we will address that
issue.

We simply copy the part of cake/libs/model/model.php into
app/app_model.php:

::

    
    <?php
    /**
     * Application model for Cake.
     *
     * Add your application-wide methods in the class below, your models
     * will inherit them.
     *
     * @package		cake
     * @subpackage	cake.app
     */
    class AppModel extends Model{
    /**
     * Sets the DataSource to which this model is bound
     *
     * @param string $dataSource The name of the DataSource, as defined in Connections.php
     * @return boolean True on success
     * @access public
     */
    	function setDataSource($dataSource = null) {
    		if ($dataSource != null) {
    			$this->useDbConfig = $dataSource;
    		}
    		$db =& ConnectionManager::getDataSource($this->useDbConfig);
    
    		if (!empty($db->config['prefix']) && $this->tablePrefix === null) {
    			$this->tablePrefix = $db->config['prefix'];
    		}
    
    		if (empty($db) || $db == null || !is_object($db) || !$db->isConnected()) {
    			$this->log('Cannot connect to database (using model: '.$this->name.')');
    			$params = array('name' => _('Database connection error'), 
    			'message' => _("We couldn't reach the database server. Please try again later"));
    			return $this->cakeError('error', array($params));
    		}
    	}
    }
    ?>


What we changed from the original setDataSource():
1/ the test $db->isConnected() that will check if the database is
connected if $db exists
2/ the cakeError() call with our params. Note that we need to use the
'error' method to make the error message displayed even in production
mode, which also means the view file will be
app/views/errors/error404.ctp.

Another note: you need to be careful of your beforeRender() hooks in
your controllers if you are using Models inside them. In that case you
can always use the following part of the code to check if your
connection is active in the controller:

::

    
    <?php
    	function isDBConnected()
    	{
    		$datasource = ConnectionManager::getDataSource('default');
    		return $datasource->isConnected();
    	}

Please feel free to correct that code if Im wrong since I was still
using cake 1.2 beta as the time of the writing of this micro tutorial.


.. meta::
    :title: Handle database connection errors
    :description: CakePHP Article related to database,mysql,error,connection,Snippets
    :keywords: database,mysql,error,connection,Snippets
    :copyright: Copyright 2009 
    :category: snippets

