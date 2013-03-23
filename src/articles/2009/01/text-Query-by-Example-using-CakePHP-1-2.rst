text Query by Example using CakePHP 1.2
=======================================

by %s on January 27, 2009

Here is how to build a simple query interface using the CakePHP 1.2
framework. It allows you to query text in any field in a table.
Queries are case insentive and the wild card is % It is not a full
"Query By Example" implementation, it just matches strings in text
fields.
1. Create table and load DB. I use PostgreSQL 8.3. In this example the
table is named "assets".
CREATE TABLE assets (
id serial PRIMARY KEY,
hostname VARCHAR(50),
owner VARCHAR(50),
memory VARCHAR(50),
tag_number VARCHAR(50),
created TIMESTAMP NOT NULL default now(),
modified TIMESTAMP NOT NULL default now()
);
GRANT SELECT, UPDATE, INSERT, DELETE ON assets to pertz;
GRANT SELECT, UPDATE, USAGE ON assets_id_seq to pertz;
2. Run the './cake bake' script and create the following in this
order:
a. Model for Asset
b. Controller for Assets
i. Answer y to: Would you like to include some basic class methods
(index(), add(), view(), edit())?
ii. Accept defaults for other questions.
c. View for Assets
i. Answer y to: Would you like to create some scaffolded views (index,
add, view, edit) for this controller?
ii. Answer n to: Would you like to create the views for admin routing?
(y/n)
3. cd ../../app/controllers

Add the functions search and searchresults to assets_controller.php.
Add var $paginate as shown.

Controller Class:
`````````````````

::

    <?php 
    class AssetsController extends AppController {
            var $name = 'Assets';
            var $helpers = array('Html', 'Form');
            var $paginate = array(
                    'limit' => 25,
                    'contain' => array('Asset')
                    );
            function index() {
                    $this->Asset->recursive = 0;
                    $this->set('assets', $this->paginate());
            }
            function search(){
            # just display search form which is a modified copy of the baked edit form
            # Need to use sessions to get paginator to remember search criteria
                    if($this->Session->check($this->name.'.search'))
                            $this->Session->del($this->name.'.search');
            }
            function searchresults() { # target action of the search function.
                    # copied index.ctp to searchresults.ctp
                    if(empty($this->data))
                            $search = $this->Session->read($this->name.'.search');
                    else    {
                            $search = Set::filter($this->postConditions($this->data,'ILIKE'));
                            $this->Session->write($this->name.'.search', $search);
                            }
                    $this->Asset->recursive = 0;
                    $this->set('assets', $this->paginate('Asset',$search));
            }
    ...
    ?>

4. cd ../../app/views/assets;cp edit.ctp search.ctp;cp index.ctp
searchresults.ctp
5. Edit search.ctp to specify new action of 'searchresult' and others
in diff below:
$ diff edit.ctp search.ctp

::

    
    2c2,3
    < <?php echo $form->create('Asset');?>
    ---
    > <?php echo 'Query on any field, case insensitive, use % as a wildcard';?>
    > <?php echo $form->create('Asset',array('action' => 'searchresults'));?>
    4c5
    <               <legend><?php __('Edit Asset');?></legend>
    ---
    >               <legend><?php __('Search Asset');?></legend>
    6d6
    <               echo $form->input('id');
    24d23
    <               <li><?php echo $html->link(__('Delete', true), array('action'=>'delete', $form->value('Asset.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $form->value('Asset.id'))); ?></li>

6. No changes needed for searchresults.ctp
7. Add search link to all views (except search itself):

::

    
    <li><?php echo $html->link(__('Search Assets', true), array('action'=>'search')); ?></li>

That's it.
WARNING: Allowing ad-hoc queries on any column in a table which is not
indexed can lead to significant performance issues in large databases.
If you want to restrict the columns that can be queried, remove the
unwanted ones from search.ctp.

PostgreSQL supports the non-standard ILIKE operator for case
insensitive matches. Other DBs may not.
I did try using the using the LIKE SQL operator, but found that it has
the side effect of adding %% to every field in the query which
prevents the Set::filter() from removing the empty fields.

I've only been using CakePHP for a couple weeks so it took several
hours to figure this out.
Hats off the the bakers at CakePHP for providing a great set of tools!


.. meta::
    :title: text Query by Example using CakePHP 1.2
    :description: CakePHP Article related to search,session,paginate,psql,qbe,postgresql,postconditions,setfilter,Snippets
    :keywords: search,session,paginate,psql,qbe,postgresql,postconditions,setfilter,Snippets
    :copyright: Copyright 2009 
    :category: snippets

