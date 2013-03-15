

Doublelist Helper
=================

by %s on August 01, 2009

I've nowhere found a doublelist helper for cakephp...
You need to include prototype to use it!

Just create the Helper, include it in your controller and add the JS
code to your JS file.

here is a sample how to use it:


View Template:
``````````````

::

    
    echo $doublelist->create('Search.county_cities', $cities, $citiesSelected);

$cities and $citiesSelected are just simple list-arrays
(City->find('list'))

if you want more control of each element, you can use the getXXX
functions.

Have fun!


Helper Class:
`````````````

::

    <?php 
    class DoublelistHelper extends AppHelper {
        var $helpers = array('Html', 'Form', 'Javascript');
        var $name = ""; //the select box name/id
    
        /**
         * sets the name. thats important if the doublelist gets build one by one
         * @param string $val
         */
        function setName($val) { $this->name = $val; }
    
        /**
         * if the doublelist gets build one by one, this function checks if the name is set
         * @return bool
         */
        function checkName() {
            if(empty($this->name)) {
                echo "Please execute setName first!";
                return false;
            }
            return true;
        }
    
        /**
         * returns a complete doublelist in a table
         * @param string $name      the name of the select box
         * @param array $options    options in the unassociated select box
         * @param array $selected   options in the associated select box
         * @return string
         */
        function create($name, $options=array(), $selected=array()) {
            $this->name = $name;
    
            foreach($selected as $key => $val) {
                if(isset($options[$key])) unset($options[$key]);
            }
    
            $ua_select = $this->getUaSelect($options);
            $a_select = $this->getASelect($selected);
            $uaButton = $this->getUaButton();
            $aButton = $this->getAButton();
    
            $out = '<div class="doublelist">';
            $out .= '<table cellspacing="0" cellpadding="0">';
            $out .= '<tr>';
            $out .= '<td>'.$ua_select.'</td>';
            $out .= '<td>'.$aButton.'<br /><br />'.$uaButton.'</td>';
            $out .= '<td>'.$a_select.'</td>';
            $out .= '</tr>';
            $out .= '</table>';
            $out .= '</div>';
            $out .= $this->getJS();
            return $out;
        }
    
        /**
         * returns the unassociated select box
         * @param array $options   the options for select
         * @param string $class    the css class for select
         * @return string
         */
        function getUaSelect($options=array(), $class="") {
            if(!$this->checkName()) return false;
            $style = 'width:170px; height:200px;';
            $ua_select = $this->Form->select($this->name.'_ua', $options, array(), array('id' => $this->name.'_ua',
                                                                            'multiple' => true,
                                                                            'style' => $style,
                                                                            'class' => $class),
                                                                        false);
            return $ua_select;
        }
    
        /**
         * returns the associated select box
         * @param array $options   the options for select
         * @param string $class    the css class for select
         * @return string
         */
        function getASelect($options=array(), $class="") {
            if(!$this->checkName()) return false;
            $style = 'width:150px; height:200px;';
            $a_select = $this->Form->select($this->name, $options, array(), array('id' => $this->name,
                                                                            'multiple' => true,
                                                                            'style' => $style,
                                                                            'class' => $class),
                                                                        false);
            return $a_select;
        }
    
        /**
         * returns the unassociate button
         * @param string $value   the button value
         * @param array $params
         * @return string
         */
        function getUaButton($value="<<", $params=array()) {
            if(!$this->checkName()) return false;
            if(empty($params['onclick'])) {
                $params['onclick'] = "dl_unassign('".$this->name."_ua', '".$this->name."');";
            }
            $ua_button = $this->Form->button($value, $params);
            return $ua_button;
        }
    
        /**
         * returns the associate button
         * @param string $value   the button value
         * @param array $params
         * @return string
         */
        function getAButton($value=">>", $params=array()) {
            if(!$this->checkName()) return false;
            if(empty($params['onclick'])) {
                $params['onclick'] = "dl_assign('".$this->name."_ua', '".$this->name."');";
            }
            $a_button = $this->Form->button($value, $params);
            return $a_button;
        }
    
        /**
         * returns a javascript, to select all options in the associated select box before submit
         * @return string
         */
        function getJS() {
            //"this" is the form. So this script must be within the form
            return $this->Javascript->codeBlock("Event.observe(this, 'submit', function(event){dl_onSubmit('".$this->name."')});");
        }
    }
    ?>

Put this in your Javascript file:

::

    
    //double list functions
    function dl_unassign(id_ua, id_a) {
        var elem_ua = $(id_ua);
        var elem_a = $(id_a);
        var a_options = elem_a.options;
        var ua_options = new Array();
    
        for(var i=0; i<a_options.length; i++) {
            if(a_options[i].selected == true) {
                ua_options.push(a_options[i]);
            }
        }
        for(i=0; i<ua_options.length; i++) {
            elem_ua.insert(ua_options[i]);
        }
    }
    
    function dl_assign(id_ua, id_a) {
        var elem_ua = $(id_ua);
        var elem_a = $(id_a);
        var ua_options = elem_ua.options;
        var a_options = new Array();
        
        for(var i=0; i<ua_options.length; i++) {
            if(ua_options[i].selected == true) {
                a_options.push(ua_options[i]);
            }
        }
        for(i=0; i<a_options.length; i++) {
            elem_a.insert(a_options[i]);
        }
    }
    
    function dl_onSubmit(id_a) {
        var elem_a = $(id_a);
        var a_options = elem_a.options;
        for(var i=0; i<a_options.length; i++) {
            a_options[i].selected = true;
        }
    }
    //doublelist functions end


.. meta::
    :title: Doublelist Helper
    :description: CakePHP Article related to javascript,select,linked list,double list,doublelist,Helpers
    :keywords: javascript,select,linked list,double list,doublelist,Helpers
    :copyright: Copyright 2009 
    :category: helpers

