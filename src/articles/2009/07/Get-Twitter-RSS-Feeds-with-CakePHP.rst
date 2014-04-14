Get Twitter RSS Feeds with CakePHP
==================================

by Firecreek on July 08, 2009

Using CakePHP model get your RSS twitter feeds with caching and
limits.


Installation
~~~~~~~~~~~~


#. Save this script below into your models directory
#. Change your Twitter ID and Twitter Name
#. Add to your $uses controller variable, public $uses =
   array('Twitter')
#. Add to your controller method,
   $this->set('twits',$this->Twitter->find());
#. Then add to your view



Model Class:
````````````

::

    <?php 
        /**
         * Get Twitter Updates
         *
         * $twits = $this->Twitter->find(array('cache'=>true,'limit'=>8));
         *
         * @author        Darren Moore, zeeneo@gmail.com
         * @link          http://www.zeen.co.uk
         */
        class Twitter extends AppModel
        {
            /**
             * Your Twitter ID
             *
             * @var integer
             * @access public
             */
            public $twitterId = 14210082;
            
            /**
             * Remove your name from posts
             * Set to false to not remove your name, otherwise set to your name
             *
             * @var mixed
             * @access public
             */
            public $twitterName = 'zeeneo';
            
            /**
             * Show replies to people
             *
             * @var boolean
             * @access public
             */
            public $showReplies = false;
            
            /**
             * Twitter RSS URL
             *
             * @var string
             * @access public
             */
            public $rssUrl = 'http://twitter.com/statuses/user_timeline/:twitterId.rss';
            
            /**
             * Turn off table usage
             *
             * @var string
             * @access public
             */
            public $useTable = false;
            
            /**
             * Duration of cache
             *
             * @var string
             * @access public
             */
            public $cacheDuration = '+30 mins';
        
        
            /**
             * Find Twitters
             *
             * @param array $options Options when getting twits, as followed:
             *                          - cache: Force caching on or off
             *                          - limit: Limit number of records returned
             * @access public
             * @return array
             */
            public function find($options = array())
            {
                //Get twits
                if((isset($options['cache']) && $options['cache'] == false) || ($twits = Cache::read('Twitter.lines')) == false)
                {
                    $twits = $this->_getTwits();
                    Cache::set(array('duration' => $this->cacheDuration));
                    Cache::write('Twitter.lines',$twits);
                }
                
                //Set to limit
                if(isset($options['limit']) && count($twits) > $options['limit'])
                {
                    $twits = array_slice($twits, 0, $options['limit']);
                }
                
                return $twits;
            }
            
            /**
             * Get Twitter Lines
             * 
             * @access private
             * @return array
             */
            private function _getTwits()
            {        
                //Get feed
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,String::insert($this->rssUrl,array('twitterId'=>$this->twitterId)));
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $feed = curl_exec($ch);
                curl_close($ch);
                
                if(!$feed) { return false; }
                
                $xml = new SimpleXmlElement($feed);
                
                foreach($xml->channel->item as $item)
                {
                    //
                    $title = (string)$item->title;
                
                    //Skip if it's a reply
                    if(!$this->showReplies && preg_match('/^'.$this->twitterName.': @/',$title))
                        continue;
                
                    //Remove name
                    if($this->twitterName)
                        $title = trim(preg_replace('/^'.$this->twitterName.':/','',$title));
                
                    $out[] = array(
                        'title' => $title,
                        'description' => (string)$item->description,
                        'pubDate' => strtotime($item->pubDate),
                        'link' => (string)$item->link
                    );
                }
                
                return $out;
            }
        
        }
    
    ?>


.. meta::
    :title: Get Twitter RSS Feeds with CakePHP
    :description: CakePHP Article related to Rss,CakePHP,twitter,Models
    :keywords: Rss,CakePHP,twitter,Models
    :copyright: Copyright 2009 Firecreek
    :category: models

