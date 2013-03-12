

Threaded Lists
==============

by %s on September 24, 2006

Ever needed a tree of sections with an unlimited depth? Here's a quick
guide to findAllThreaded().


Why would I need this?
----------------------
Let's say you wanted your website organised into sections like this:


#. [li] Art

    #. [li] Film [li] Music

        #. [li] Jazz [li] Pop

   [li] History

    #. [li] Archaeology [li] War
   [li] Science

    #. [li] Biology [li] Chemistry [li] Physics
   [li] Technology

    #. [li] Computing

        #. [li] Hardware [li] Software
       [li] Engineering

How would you store that in a relational database? Simple - each
'node' has a 'parent'. So, the Pop section would have a parent of
Music and Music would have a parent of Art . You'd get something like
this:

::

    Partial table example
    
    id     name            parent_id
    ----------------------------------------
    1      Art             0
    2      Music           1
    3      Pop             2

Notice how top level sections have a parent_id of 0.

That's just what I need! What do I do now?
------------------------------------------
I thought you'd never ask. First you need to make a table in your
database. I'm going to call this 'sections':


SQL:
````

::

    CREATE TABLE `sections` (
      `id` int(11) NOT NULL auto_increment,
      `name` varchar(255) NOT NULL,
      `parent_id` int(11) NOT NULL,
      PRIMARY KEY  (`id`)
    );
    
    INSERT INTO `sections` (`id`, `name`, `parent_id`) VALUES 
    (1, 'Art', 0),
    (2, 'Film', 1),
    (3, 'Music', 1),
    (4, 'Jazz', 3),
    (5, 'Pop', 3),
    (6, 'History', 0),
    (7, 'Archaeology', 6),
    (8, 'War', 6),
    (9, 'Science', 0),
    (10, 'Biology', 9),
    (11, 'Chemistry', 9),
    (12, 'Physics', 9),
    (13, 'Technology', 0),
    (14, 'Computing', 13),
    (15, 'Hardware', 14),
    (16, 'Software', 14),
    (17, 'Engineering', 13);

Now, we need to make the model for this table. Save this as
app/models/section.php :


Model Class:
````````````

::

    <?php 
    class Section extends AppModel
    {
      var $name = 'Section';
    }
    ?>

Next, we need to pull data out of the database using our controller.
Save this as app/controllers/sections_controller.php :


Controller Class:
`````````````````

::

    <?php 
    class SectionsController extends AppController
    {
      var $name = 'Sections';
      
      function index()
      {
        $this->set('data', $this->Section->findAllThreaded(null, null, 'name'));
      }
    }
    ?>

We need a view for the index action. Save this as
app/views/sections/index.thtml :


View Template:
``````````````

::

    <h1>List of sections</h1>
    <pre><?php print_r($data); ?></pre>

You should see an array with everything in your sections table
organised with the right children. Looks a bit scary though...


Ok, so I have an array.. how do I make this into a nice HTML list?
------------------------------------------------------------------
I've written a simple helper that will convert the array into a lovely
list. Save this as app/views/helpers/tree.php :

Helper Class:
`````````````

::

    <?php 
    class TreeHelper extends Helper
    {
      var $tab = "  ";
      
      function show($name, $data)
      {
        list($modelName, $fieldName) = explode('/', $name);
        $output = $this->list_element($data, $modelName, $fieldName, 0);
        
        return $this->output($output);
      }
      
      function list_element($data, $modelName, $fieldName, $level)
      {
        $tabs = "\n" . str_repeat($this->tab, $level * 2);
        $li_tabs = $tabs . $this->tab;
        
        $output = $tabs. "<ul>";
        foreach ($data as $key=>$val)
        {
          $output .= $li_tabs . "<li>".$val[$modelName][$fieldName];
          if(isset($val['children'][0]))
          {
            $output .= $this->list_element($val['children'], $modelName, $fieldName, $level+1);
            $output .= $li_tabs . "</li>";
          }
          else
          {
            $output .= "</li>";
          }
        }
        $output .= $tabs . "</ul>";
        
        return $output;
      }
    }
    ?>

Now change your controller so it includes this helper:
app/controllers/sections_controller.php :

Controller Class:
`````````````````

::

    <?php 
    class SectionsController extends AppController
    {
      var $name = 'Sections';
      var $helpers = array('Html', 'Tree');
    
      function index()
      {
        $this->set('data', $this->Section->findAllThreaded(null, null, 'name'));
      }
    }
    ?>

And change your view so it uses this helper, instead of just dumping
the array:

app/views/sections/index.thtml :

View Template:
``````````````

::

    <h1>List of sections</h1> 
    
    <?php echo $tree->show('Section/name', $data); ?>

'Section/name' is in the format 'Model/fieldname' just like with the
HTML input helpers. You should now have a nice list of sections from
your database!


How do I add new sections to this list then?
--------------------------------------------
You can simply add rows to the database, or make an add action. Here's
one I baked earlier:

app/controllers/sections_controller.php :

Controller Class:
`````````````````

::

    <?php 
    class SectionsController extends AppController
    {
      var $name = 'Sections';
      var $helpers = array('Html', 'Tree');
      
      function index()
      {
        $this->set('data', $this->Section->findAllThreaded(null, null, 'name'));
      }
      
    	function add()
    	{		
    	
    	  $sectionArray = $this->Section->generateList(null, 'name');
    		$this->set('sectionArray', $sectionArray);
    		
    		if(empty($this->data))
    		{
    			$this->render();
    		}
    		else
    		{
    			$this->cleanUpFields();
    			if($this->Section->save($this->data))
    			{
    				$this->Session->setFlash('The Section has been saved');
    				$this->redirect('/sections/index');
    			}
    			else
    			{
    				$this->Session->setFlash('Please correct errors below.');
    			}
    		}
    	}
    
    }
    ?>

app/views/sections/index.thtml :

View Template:
``````````````

::

    <h1>List of sections</h1> 
    
    <?php echo $tree->show('Section/name', $data); ?>
    
    <?php echo $html->link('Add Section', '/sections/add');?>

app/views/sections/add.thtml :

View Template:
``````````````

::

    <h2>New Section</h2>
    <form action="<?php echo $html->url('/sections/add'); ?>" method="post">
    
    <?php if(is_array($sectionArray)) { ?>
    <div class="optional"> 
      <label for="SectionParentId">Parent Section</label>
      <?php echo $html->selectTag('Section/parent_id', $sectionArray);?>
    </div>
    <?php } ?>
    
    <div class="required"> 
    	<label for="SectionName">Section</label>
     	<?php echo $html->input('Section/name', array('size' => '60'));?>
    	<?php echo $html->tagErrorMsg('Section/name', 'Please enter the name.');?>
    </div>
    
    <div class="submit">
    	<?php echo $html->submit('Add');?>
    </div>
    
    </form>



What?! You mean I didn't have to copy and paste all that code?
--------------------------------------------------------------
`Click here to download full source code`_ (6 KB)
Hope you enjoyed the article and happy baking!

.. _Click here to download full source code: http://www.amusd.com/code/threaded.zip
.. meta::
    :title: Threaded Lists
    :description: CakePHP Article related to hierarchical,lists,helpers,tree,threaded,MrRio,Tutorials
    :keywords: hierarchical,lists,helpers,tree,threaded,MrRio,Tutorials
    :copyright: Copyright 2006 
    :category: tutorials

