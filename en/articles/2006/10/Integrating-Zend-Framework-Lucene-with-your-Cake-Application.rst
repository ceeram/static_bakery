

Integrating Zend Framework Lucene with your Cake Application
============================================================

by %s on October 16, 2006

This is a short tutorial that teaches you how to integrate Zend
Framework's Lucene implementation (100% PHP) to your application. It
requires your server to have PHP5 installed, since ZF only runs on
PHP5, and is likelly to be deprecated very soon.


Introduction
~~~~~~~~~~~~

These days, each web application requires FULLTEXT search. MySQL has a
nice native implementation, PostgreSQL has one too, Lucene, Ferret
(ruby port of Lucene) are just to name a few.

However, when working on a personal project, I faced a difficulty that
MySQL's InnoDB engine has: it doesn't have FULLTEXT support. There is
also no release date for this feature, giving me no choice but to look
for an alternative.

Lucene seemed to be the tool for the job. Fortunatelly, ZF has this
covered with their search library, which is based on Lucene. It has
its drawbacks too:

1. It doesn't update (as far as I know). To update the index, you have
to rebuild it

2. It is still in preview phase. The code on this article is likelly
to change

3. As of yet, it doesn't support UTF8 nativelly. There is a "quick
fix" (read temporary sollution) at
http://framework.zend.com/manual/en/zend.search.charset.html



Getting the framework
~~~~~~~~~~~~~~~~~~~~~

First, you need to download the framework. Head to
`http://framework.zend.com/`_ and download the latest preview.

You will need the following files:

::

    library/Zend/Exception.php
    library/Zend/Search/

Extract those files to your vendors directory so that the structure is
like the one bellow:

::

    <base directory>/vendors/Zend/Exception.php
    <base directory>/vendors/Zend/Search



Indexing your content
~~~~~~~~~~~~~~~~~~~~~

Ideally, you would have a bake task to do the indexing part. Since
CakePHP 1.2 isn't out yet, we'll have an indexer.php that will do the
trick. It could be called by a cron job once a day or more (deppending
on your need). This file should also reside outside your webroot
folder (/app/webroot), so we'll put it on /app.

Here's the code for indexer.php:

::

    
    <?php
    
    // Add your vendor directory to the includepath. ZF needs this.
    ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__) . '/vendors');
    
    // Require the Lucene Class
    require_once('Zend/Search/Lucene.php');
    
    // Establish your connection to the database
    mysql_connect('localhost', 'user', 'p4ssw0rd');
    mysql_select_db('documents');
    
    // Create a new index. This folder has to be readable by the httpd user
    // I will use the cache directory to store the index data
    $indexPath = dirname(__FILE__) . '/app/tmp/cache/index';
    $index = new Zend_Search_Lucene($indexPath, true);
    
    // Lets get some records to add to the index
    $documents_rs = mysql_query('SELECT * FROM documents');
    while($document = mysql_fetch_object($documents_rs)) {
        // Create a new searchable document instance
        $doc = new Zend_Search_Lucene_Document();
    
        // Add some information
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('document_id', $document->id));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('document_created', $document->created));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('document_updated', $document->updated));
        $doc->addField(Zend_Search_Lucene_Field::Text('document_title', $document->title));
        $doc->addField(Zend_Search_Lucene_Field::Text('document_description', $document->description));
        
        // Add the document to the index
        $index->addDocument($doc);
    }
    
    // Commit the index
    $index->commit();
    ?>

You will, of course, need to adapt this code to your application's
needs. To know what field type to use in each sittuation, read
`http://framework.zend.com/manual/en/zend.search.html#zend.search
.index-creation.understanding-field-types`_.

Call the indexer.php file from the command line for now, to create an
initial index of your data. As i've stated earlier, you can use it as
a cron job.


Querying your index
~~~~~~~~~~~~~~~~~~~

Now that the content is indexed, you need to query it. I have made a
simple component that allows you to do just that. Save this file under
app/controllers/components/lucene.php:


Component Class:
````````````````

::

    <?php 
    // I'm not sure this is a good idea inside Cake, but I had no problems so far
    ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . VENDORS);
    vendor('Zend' . DS . 'Search' . DS . 'Lucene');
    
    class LuceneComponent extends Object {
    	var $controller = true;
    	var $index = null;
    	
    	function startup(&$controller) {
    	}	
    
    	// Get the index object
    	function &getIndex() {
    		if(!$this->index) {
    			$this->index = new Zend_Search_Lucene(TMP . DS . 'lucene');
    		}
    		return $this->index;
    	}
    	
    	// Executes a query to the index and returns the results
    	function query($query) {
    		
    		$index =& $this->getIndex();
    		$results = $index->find($query);
    		return $results;
    	}
    }
    ?>

Now, all you need is to call it from your controller. Here's an
example:


Controller Class:
`````````````````

::

    <?php 
    class SearchController extends AppController {
    	var $name = 'Search';
    	var $components = array('lucene');
    	var $helpers = array('html');
    
    	function documents() {
    		if(!empty($this->data)) {
    			$documents = $this->lucene->query($this->data['Search']['terms']);
    			$this->set('results', $documents);
    		}
    	}
    }
    ?>

And, the corresponding view:

::

    
    <?php echo $html->formTag('/search/documents'); ?>
    Search: 
    <?php echo $html->input('Search/terms'); ?>
    <?php echo $html->submit(); ?>
    
    </form>
    
    <?php if(isset($results)): ?>
      <h1>Search results: found <?php echo count($results); ?> document(s):</h1>
      <?php foreach($results as $result): ?>
        <h3><?php echo $result->document_title; ?> - <?php echo $document->score; ?></h3>
        <p>
          <?php echo $result->document_description; ?>
          <hr>
          <a href="/documents/view/<?php echo $result->document_id; ?>">View document</a>
        </p>
      <?php endforeach; ?>
    <?php endif; ?>

I would advise you to read the Search component's manual section on
this, since it has lots of details on querying the index. Go to
`http://framework.zend.com/manual/en/zend.search.html`_ to read it.

Good luck, and let me know how it worked out for you.

.. _http://framework.zend.com/: http://framework.zend.com/
.. _http://framework.zend.com/manual/en/zend.search.html: http://framework.zend.com/manual/en/zend.search.html
.. _http://framework.zend.com/manual/en/zend.search.html#zend.search.index-creation.understanding-field-types: http://framework.zend.com/manual/en/zend.search.html#zend.search.index-creation.understanding-field-types
.. meta::
    :title: Integrating Zend Framework Lucene with your Cake Application
    :description: CakePHP Article related to search,lucene,Tutorials
    :keywords: search,lucene,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

