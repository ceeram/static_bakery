Form posting with '_GET' parameters
===================================

by Marcelius on October 26, 2008

A while ago, I was working on a searchengine for my site. When a
search is performed, the url should show all the parameters so it is
possible for user to add the search to their favorites. This code
snippet shows how I accomplished this. More details of this problem
are described here: [url]http://groups.google.com/group/cake-
php/browse_thread/thread/d1c13c9338b8fe26[/url]
The trick is to submit a form using 'POST', and than redirect to an
URL with named parameters (cake's alternative to regular _GET
variables) as suggested by Stephen Orr. I havn't fully tested it so if
you find any bugs, your free to make a comment about it.

Here's my code:

Controller Class:
`````````````````

::

    <?php 
    class MySearchController extends AppController{
    	function search(){
    		if (empty($this->data)){
    			if (!empty($this->params["named"])){
    				//unserialize passed params
    				$this->data = $this->unSerializeData($this->params["named"]);
    
    				//do search logic
    				$this->doSearchLogic();
    			} else {
    				//do nothing, show search form
    			}
    		} else {
    			//serialize and redirect
    			$searchParams = $this->serializeData($this->data);
    			$this->redirect(array("action"=>"index", $searchParams));
    		}
    	}
    
            /**
             * Converts named params to array
             *
             * @param array $namedParams
             * @return array
             */
            function unSerializeData($namedParams){
                $result = array();
     
                if (is_array($namedParams)){
                    foreach($namedParams as $fieldName=>$value){
                        $field = explode(".", $fieldName);
                        if (count($field) == 2){
                            $result[$field[0]][$field[1]] = $value;
                        } elseif(count($field) == 1) {
                            $result[$field[0]] = $value;
                        }
                    }
                }
     
                return $result;
            }
     
     
            /**
             * Converts the this->data array to named parameters
             *
             * @param array $data
             * @return string
             */
            function serializeData($data){
                $result = array();
     
                if (is_array($data)){
                    foreach ($data as $model=>$values) {
                        if (is_array($values)){
                            foreach($values as $name=>$val){
                                $result[] = sprintf("%s.%s:%s", $model, $name, $val);
                            }
                        } else {
                            $result[] = sprintf("%s:%s", $model, $values);
                        }
                    }
                }
     
                $result = implode("/", $result);
     
                return $result;
            }
    }
    ?>



.. meta::
    :title: Form posting with '_GET' parameters
    :description: CakePHP Article related to GET,form,posting,Snippets
    :keywords: GET,form,posting,Snippets
    :copyright: Copyright 2008 Marcelius
    :category: snippets

