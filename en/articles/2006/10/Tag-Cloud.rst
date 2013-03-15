Tag Cloud
=========

by %s on October 06, 2006

Here's a nice way to do a simple and customizable tag cloud.
First add a tag column to your table.
Add keywords separated by commas.

Here's the component: (nuage.php)

Component Class:
````````````````

::

    <?php 
    <?php
    class NuageComponent extends Object
    {
        var $controller = true;
        
        function startup(&$controller)
        {
            $this->controller = &$controller;
            $Articlez = new Article; //Model name
            $data = $Articlez->query("SELECT article_tags AS tag FROM articles"); //Select only the tags column.
            $this->controller->set('Tagz',$data);
        }
    }
    ?>
    ?>

And here's the element: (nuage.thtml)

View Template:
``````````````

::

    
    <?php
            $mots = array();
            foreach ($Tagz as $_tag) {
    			
    			$tag = $_tag['articles']['tag'];
    			if ($tag):
    			if (stristr($tag, chr(44))) {
    				
    				$tags = explode(chr(44), $tag);
    				
    				foreach($tags as $__tag):
    					if(array_key_exists($__tag, $mots)) $mots[$__tag]++;
    					else $mots[$__tag] = '1';
    				endforeach;
    			}
    
    			else {
    				if(array_key_exists($tag, $mots)) $mots[$tag]++; 
    				else $mots[$tag] = '1';
    			}
    		
    		endif;
    			
    		}
    		
        $max_size = 25; $max_weight = 900; //max font size and max font weight
        $min_size = 5; $min_weight = 100; //min font size and min font weight
        $max_qty = max(array_values($mots)); //the maximum data
        $min_qty = min(array_values($mots)); //the minimum data
        
            $spread = $max_qty - $min_qty;
            if (0 == $spread) { 
                $spread = 1;
            }
    
            $step = ($max_size - $min_size)/($spread);
            $bold = ($max_weight - $min_weight)/($spread);
    
            foreach ($mots as $key => $value) {
    
                $size = round($min_size + (($value - $min_qty) * $step),0);
                $weight = round($min_weight + (($value - $min_qty) * $bold),0);
                
                echo '<a href="/articles/tags/'.$key.'" style="font-weight: '.$weight.'; font-size: '.$size.'pt" title="'.$value.' articles avec le tag '.$key.'">'.$key.'</a> ';
            }
    ?>

Do whatever you want with it and give me your comments.

Hope you like this!

.. meta::
    :title: Tag Cloud
    :description: CakePHP Article related to php,tags,tag cloud,component,Cloud,cake php,xavi,ks,india,ram,wonderful cake,Components
    :keywords: php,tags,tag cloud,component,Cloud,cake php,xavi,ks,india,ram,wonderful cake,Components
    :copyright: Copyright 2006 
    :category: components

