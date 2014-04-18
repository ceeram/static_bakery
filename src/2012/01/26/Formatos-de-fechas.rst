Formatos de fechas
==================

Hola, soy nuevo en Bakery, les dejo un comportamiento que hice para
dar vuelta la fecha, algo así como un dateformat. lo que hace es muy
simple, recorre todo un array hasta las values, luego se fija en el
modelo de la base de datos si el campo es un date o un datetime y
luego lo da vuelta al formato que querramos. espero que les sea util.
comentarios son bienvenidos.

<?php class DateformatBehavior extends ModelBehavior {

::

    //formato que queremos. (más humano)
    var $dateFormat = 'd.m.Y';
    //formato de la base de datos. 
    var $databaseFormat = 'Y-m-d';
    
    function setup(&$model) {
        $this->model = $model;
    }
    
    function _changeDateFormat($date = null,$dateFormat){
        return date($dateFormat, strtotime($date));
    }
    
    function _changeDate($queryDataConditions , $dateFormat){
        foreach($queryDataConditions as $key => $value){
            if(is_array($value)){
                $queryDataConditions[$key] = $this->_changeDate($value,$dateFormat);
            } else {
                $columns = $this->model->getColumnTypes();
                //sacamos las columnas que no queremos
                foreach($columns as $column => $type){
                    if(($type != 'date') && ($type != 'datetime')) unset($columns[$column]);
                }
                //convertimos las fecha de las columnas que si queremos, las de tipo date. 
                foreach($columns as $column => $type){
                    if(strstr($key,$column)){
                        if($type == 'datetime') $queryDataConditions[$key] = $this->_changeDateFormat($value,$dateFormat.' H:i:s ');
                        if($type == 'date') $queryDataConditions[$key] = $this->_changeDateFormat($value,$dateFormat);
                    }
                }
                
            }
        }
        return $queryDataConditions;
    }
    
    //antes de buscar modificamos la condicion, en el caso de que esta viaje en los conditions 
    function beforeFind($model, $queryData){
        $queryData['conditions'] = $this->_changeDate($queryData['conditions'] , $this->databaseFormat);
        return $queryData;
    }
    
    //despues de buscar le decimos que la queremos en el formato que configuramos. 
    function afterFind(&$model, $results){
        $results = $this->_changeDate($results, $this->dateFormat);
        return $results;
    }
    
    //antes de guardar le decimos que lo queremos en el formato de la base de datos. 
    function beforeSave($model) {
        $model->data = $this->_changeDate($model->data, $this->databaseFormat);
        return true;
    }

} ?>



.. author:: rikkin
.. categories:: articles, behaviors
.. tags:: datetime,date,fechas,Dateformat,comportamiento,base de
datos,Behaviors

