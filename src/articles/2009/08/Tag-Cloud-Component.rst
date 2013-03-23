Tag Cloud Component
===================

by %s on August 24, 2009

This component will generate an associative array of tags and their
frequency based on the models passed to it.
I'm not a big fan of tag clouds, but I guess quite a lot of people
are, so I set about to create one for a site I'm working on. I decided
that most people will probably have a HABTM relationship between tags
and something else, be it posts, projects, recipes or anything else.

This component will generate an associative array of tags and their
frequency based on the models passed to it. There is also an included
element to output an example cloud based on the data in the array. By
default, the results are cached for 1 day, as the processes of
generating the array is fairly time intensive.

This is version 1, and there are a few known problems:

Caching : When serialising the cloud data, it seems to get sorted by
PHP (or maybe something I missed in the FileEngine?). So, the tag
could will always display in frequency order. I guess that's more of a
tag line that a cloud..

Randomisation : Currently, the way the tag cloud is randomised is a
bad solution, though is acceptable if the cache is only being
generated once every so often. The best way to do it (which I can
think of) would be to include a pre-seeded 'random' field in the tag
model and use that to sort the tags when they come from the database,
so PHP doesn't have to do any shuffling. This is the solution I have
used . One other option would be simply to not store the tag name as
the key of the array, and let php's array_shuffle function do the work
faster.

Please leave any comments, suggestions, fixes or improvements.
Example Use

::

    
    $this->TagCloud->setCaching(true);
    $this->TagCloud->setCacheTime('+ 1 day');
    $this->TagCloud->setTagName('Tag'); // The tag model
    $this->TagCloud->setListName('RecipesTags'); // The name of your HABTM join table
    $this->TagCloud->setFontSizes(array(6,7,8,9,10,11,12,13,14,15,16)); //This is the default
    $this->TagCloud->setRandomize(true) // Mix the cloud up
    $this->set('cloud',$this->TagCloud->generateCloud());



You should set the Caching config at the bottom of your core.php file:

::

    
        //Caching from TagCloud
        Cache::config('tagcloud', array(
        'engine' 	=> 'File',
        'serialize'	=> true,
        'prefix' 	=> ''
        ));



Component Class:
````````````````

::

    <?php 
    /**
     * components/tag_cloud.php
     *
     * A CakePHP Component that can generate a tag cloud from your models.
     *
     * Copyright 2009, Will Demaine - http://www.charityware.org
     * Licensed under The MIT License - Modification, Redistribution allowed but must retain the above copyright notice
     * This file is distributed as Charityware. All donations recieved from this software will be donated to charity
     * For more information, please see http://www.charityware.org
     *
     * @link 		http://www.opensource.org/licenses/mit-license.php
     *
     * @package		Tqag Cloud Componenet
     * @created		August 1st 2009
     * @version 	1.0
     */
    
    class tagCloudComponent extends Object
    {
        /**
         * The name of the Tag model.
         * @var String
         */
        private $tagName = 'Tag';
    
        /**
         * The name of the join table in the HABTM relationship with the Tag model
         * @var String
         */
        private $listName = 'ProjectTag';
    
        /**
         * The field in your model that the string representation of the tag is stored.
         * @var String
         */
        private $tagDescription = 'category';
    
        /**
         * True if the tag cloud should be randomised
         * @var bool
         */
        private $random = true;
    
        /**
         * A list of font sizes to be used with the tag cloud.
         * @var Array
         */
        private $sizes = array(6,7,8,9,10,11,12,13,14,15,16);
    
        /**
         * The cakephp tag model derrived from the name.
         * @var Model
         */
        private $tagModel;
    
        /**
         * The cakephp join table model derived from the name
         * @var Model
         */
        private $listModel;
    
        /**
         * Integer to store the highest occurence of any given tag
         * @var int
         */
        private $max = 0;
    
        /**
         * The array representation of the tag cloud
         * @var Array
         */
        private $cloud = array();
    
        /**
         * Should the results be cached?
         * @var bool
         */
        private $cache = true;
    
        /**
         * How long should the cache last
         * @var String
         */
        private $cacheTime = '+1 day';
    
        /**
         * Get the models from Cake by their name
         */
        public function __construct()
        {
            $this->tagModel = ClassRegistry::init($this->tagName);
            $this->listModel = ClassRegistry::init($this->listName);
    
            if (Cache::config('tagcloud') === false)
            {
                Cache::config('tagcloud', array(
                    'engine' 	=> 'File',
                    'serialize' => true,
                    'prefix'	=> ''
                    ));
            }
        }
    
        /*
         * Getters and Setters
         */
    
        /**
         * Sets the name of the Tag Model
         * @param String $name
         */
        public function setTagName($name)
        {
            if (is_string($name))
            {
                $this->tagName = $name;
            }
        }
    
        /**
         * Sets the name of the join table or 'list' model
         * @param String $name
         */
        public function setListName($name)
        {
            if (is_string($name))
            {
                $this->listName = $name;
            }
        }
    
        /**
         * Set the font sizes the tag cloud should use
         * @param Array $sizes
         */
        public function setFontSizes($sizes)
        {
            if (is_array($sizes))
            {
                $this->sizes = $sizes;
            }
        }
    
        /**
         * Set whether the tag cloud should be in a random order or not
         * @param bool $rand
         */
        public function setRandomize($rand)
        {
            if (is_bool($rand))
            {
                $this->random = $rand;
            }
        }
    
        /**
         * Set whether the cache should be used
         * @param bool $bool
         */
        public function setCaching($bool)
        {
            if (is_bool($bool))
            {
                $this->cache = $bool;
            }
        }
    
        /**
         * Set the cache time. Given as a string
         * @param String $time
         */
        public function setCacheTime($time)
        {
            if (is_string($time))
            {
                $this->cacheTime = $time;
            }
        }
    
        /**
         * Generates the cloud from the information given and returns it in array form
         * @return Array The cloud
         */
        public function generateCloud()
        {
            if ($this->cache)
            {
                Cache::set(array('duration' => $this->cacheTime));
                $this->cloud = Cache::read('tag_cloud','tagcloud');
                if ($this->cloud != false)
                {
                    return $this->cloud;
                }
            }
    
            $tags = $this->_getTags();
            foreach ($tags as $tag)
            {
                $count = $this->_findTagCount($tag[$this->tagName]['id']);
                $this->cloud[$tag[$this->tagName][$this->tagDescription]] = array('id' => $tag[$this->tagName]['id'], 'count' => $count, 'size' => $this->_getTagSize($count));
            }
            if ($this->random)
            {
                $this->_writeCache();
                return $this->cloud = $this->_shuffleCloud($this->cloud);
            }
            else
            {
                $this->_writeCache();
                return $this->cloud;
            }
    
        }
    
        /**
         * Write the cloud to the cache if it's turned on
         */
        private function _writeCache()
        {
            if ($this->cache)
            {
                Cache::write('tag_cloud',$this->cloud,'tagcloud');
            }
        }
    
        /**
         * Get an array of all of the tag names from our model
         * @return Array
         */
        private function _getTags()
        {
            return $this->tagModel->find('all');
        }
    
        /**
         * Find the number of times each tag is used by it's id
         * Update the max value if a new high point is reached
         * @param int $id
         * @return int
         */
        private function _findTagCount($id)
        {
            $count = $this->listModel->find('count', array('conditions' => array(strtolower($this->tagName).'_id' => $id)));
            if ($count > $this->max)
            {
                $this->max = $count;
            }
            return $count;
        }
    
        /**
         * Calculate the font size from the number of times the tag is found
         * with respect to the highest occurance of any tag
         * @param int $count
         * @return int
         */
        private function _getTagSize($count)
        {
            $p = round(($count / $this->max) * 10);
            return $this->sizes[$p];
        }
    
        /**
         * Shuffle the array, but preserve the keys
         * @param Array $array
         * @return Array
         */
        private function _shuffleCloud($array)
        {
            $temp = array();
            while (count($array))
            {
                $element = array_rand($array);
                $temp[$element] = $array[$element];
                unset($array[$element]);
            }
            return $temp;
        }
    }
    ?>

Here is the example element which I'm using to render the cloud:


View Template:
``````````````

::

    
    <?php
    foreach ($cloud as $tag => $data):
    ?>
        <span style="font-size:<?php echo $data['size']; ?>px">
        <?php echo $html->link($tag, array('controller' => 'tags', 'action' => 'view', $data['id'])); ?>
        </span>
    <?php
    endforeach;
    ?>


.. meta::
    :title: Tag Cloud Component
    :description: CakePHP Article related to ,Components
    :keywords: ,Components
    :copyright: Copyright 2009 
    :category: components

