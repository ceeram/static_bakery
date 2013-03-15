jQuery autocomplete Helper
==========================

by %s on April 12, 2008

I use jQuery becose is fast and concise Javascript library. My helper,
jmycake, use jQuery for AJAX autocomplete.


Requirements:
~~~~~~~~~~~~~

+ jQuery library (download from
  `http://docs.jquery.com/Downloading_jQuery`_
+ CakePHP 1.2 beta
+ The helper code (see below)



Helper:
~~~~~~~

Helper Class:
`````````````

::

    <?php 
    <?php
    class JmycakeHelper extends AppHelper {
    	var $helpers = array('Javascript');
    	var $output ='';
        
    	/*
    	 * $idInput = ID dell'input text su cui fare l'autocomplete
    	 * $modelSearch = Modello/NomeCampo nel quale cercare al stringa inserita nell'input
    	 * $other = Array che contiene l'id del campo da aggiornare ed il nome del campo da prendere dal db
    	 * $numResult = Numero di risultati da mostrare nella lista
    	 * $strlen = Numero di caratteri dopo i quali iniziare le richieste dell'autocomplete
    	 */
    	function autocomplete($idInput,$modelSearch,$other=null,$numResult=7,$strlen=1) {
    		$fields= "";
    		$setBody = "";		
    		$search = explode("/",$modelSearch);
    		if (is_array($other)) { 
    			foreach ($other As $k => $v) {
    				$fields .= $v.',';
    				$setBody .= "$('#".$k."').val(".$v.");";
    			}
    		}
    		$fields .= $search[1];
    
    		$this->output.=$this->Javascript->codeBlock('
    			$("#'.$idInput.'").ready(function(){
    				$("#'.$idInput.'").attr("onkeyup","query_'.$idInput.'(this.value)");
    				$("#'.$idInput.'").attr("autocomplete","off");
    				$("#'.$idInput.'").after("<span id=\"span_'.$idInput.'\" class=\"autocomplete_live\"></span>");
    			});
    		
    			function query_'.$idInput.'(txt) {
        			if(txt.length >= '.$strlen.') {
    					$.post("'.$this->webroot.$this->params["controller"].'/autocomplete", {query: txt, fields: "'.$fields.'", search: "'.$search[1].'", model: "'.$search[0].'", numresult: "'.$numResult.'", rand: "'.$idInput.'"}, function(data){
    						$("#span_'.$idInput.'").html("<ul id=\'ul_'.$idInput.'\' class=\'autocomplete_live\'>"+data+"</ul>");
    						$("#ul_'.$idInput.'").width($("#'.$idInput.'").width());
    						$("#span_'.$idInput.'>ul>li>a").keypress(function(e) {       
    							pressedKey = e.charCode || e.keyCode || -1;
    							switch(pressedKey) {
    								case 38://up
    			                    	position=position-1;
    			                        if (position<0) {
    			                        	position=dimensione-1;
    									}
    			                        $("#span_'.$idInput.'>ul>li>a").eq(position).focus();
    			                        return false;
    								break;
    			                    
    								case 40://down
    									position=position+1;
    			                        if (position>=dimensione) {position=0;}
    			                        	$("#span_'.$idInput.'>ul>li>a").eq(position).focus();
    			                            return false;
    									break;
    			                }
    						});						
    					});	
    				}			
    			}
    			
    			$("#'.$idInput.'").keypress(function(e) {       
    				pressedKey = e.charCode || e.keyCode || -1;
                    dimensione=$("#span_'.$idInput.'>ul>li").size();
                    switch(pressedKey) {
                    	case 38://up
                        	$("#span_'.$idInput.'>ul>li>a").eq($("#span_'.$idInput.'>ul>li").size()-1).focus();
                            position = $("#span_'.$idInput.'>ul>li").size()-1;
    					break;
                    
    					case 40://down
                    		$("#span_'.$idInput.'>ul>li>a").eq(0).focus();
                            position=0;
    					break;
                    }
            	});
    			
    			function set_'.$idInput.'('.$fields.') {
    				'.$setBody.'
    				$("#'.$idInput.'").val('.$search[1].');
    				$("#span_'.$idInput.'").html("");
    			}
    		');
    		return $this->output;
        }
    }
    ?>
    ?>


App Controller:
~~~~~~~~~~~~~~~

In app_controller.php add this function:

::

    
    	function autocomplete() {
    		if ($this->RequestHandler->isAjax() && $this->RequestHandler->isPost()) {
            	$fields = explode(",",$this->params['form']['fields']);
            	$results = $this->{$this->params['form']['model']}->findAll($this->params['form']['search'].' LIKE \'%'.$this->params['form']['query'].'%\'',$fields,$this->params['form']['search'].' ASC',$this->params['form']['numresult']); 
            	$this->set('results',$results);
            	$this->set('fields',$fields);
            	$this->set('model',$this->params['form']['model']);
            	$this->set('input_id',$this->params['form']['rand']);
            	$this->set('search',$this->params['form']['search']);
    			$this->render('autocomplete','ajax','/common/autocomplete');				
        	}
    	}


Autocomplete view:
~~~~~~~~~~~~~~~~~~
And create the common view:


View Template:
``````````````

::

    
    <?php
    	foreach ($results As $k=>$v) {
    		$value='';
    		foreach ($fields As $i =>$j) {
            	$value .= '"'.$v[$model][$j].'",';
            }
            echo "<li onclick='set_".$input_id."(".substr($value,0,strlen($value)-1).")'><a href='#'>".$v[$model][$search]."</a></li>";
    	}
    ?>

Save this view in view/common/autocomplete.ctp.


In you controller:
~~~~~~~~~~~~~~~~~~
Add my herper in $helper array:

::

    
    var $helpers = array('Html','Form','Javascript','Jmycake');



CSS:
~~~~
Include this CSS style sheet in your layout:

::

    
    @CHARSET "UTF-8";
    .autocomplete_live {
    	background:#F0F0F0 none repeat scroll 0%;
    	clear:both;
    	cursor:pointer;
    	display:block;
    	margin:0px;
    	padding:0px;
    	z-index:9999;
    }
    
    .autocomplete_live ul {
    	clear:both;
    	display:block;
    	list-style-type:none;
    	margin:0px;
    	padding:0px;
    	position:absolute;
    	width:100%;
    }
    
    .autocomplete_live li {
    	background:#F0F0F0 none repeat scroll 0%;
    	border-bottom:1px solid #C0C0C0;
    	display:block;
    	height:25px;
    	list-style-type:none;
    	margin:0px;
    	padding:0px;
    }



How-to insert autocomplete in you view:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you have, for example, this view:


View Template:
``````````````

::

    
    <div class="Nation">
    	<h2>Nazioni</h2>    
        <?php echo $form->create('Nation', array('action' => 'test')); ?>
    		<?php echo $form->input('id',array("type"=>"hidden")); ?>
    		<?php echo $form->input('nation'); ?>
            <?php echo $form->input('iso'); ?>
            <?php echo $form->submit('GO'); ?>
        <?php echo $form->end(); ?>
    </div>

For add autocomplete input is easy and fast, add this code in you
view:

::

    
    <?php echo $jmycake->autocomplete('NationNation','Nation/nation',array('NationId'=>'id','NationIso'=>'iso')); ?>

The helper add on input id "NationNation" the autocomplete that make
AJAX request on table "Nation" column name "nation" and, when you
select the result, the helper set the value of input NationId with the
table "id" and the input NationIso with the "iso" value found.

It's possibile to pass other 2 variable on my helper:


#. $numResult: number of result that show in the view
#. $strlen: minimum length of the insert text in the html input

It's all!.

Sorry for my english... :(

.. _http://docs.jquery.com/Downloading_jQuery: http://docs.jquery.com/Downloading_jQuery
.. meta::
    :title: jQuery autocomplete Helper
    :description: CakePHP Article related to autocomplete,Helpers
    :keywords: autocomplete,Helpers
    :copyright: Copyright 2008 
    :category: helpers

