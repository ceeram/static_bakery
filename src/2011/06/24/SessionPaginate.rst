SessionPaginate
===============

by lenadro on June 24, 2011

**Paginador por sesión, mantiene las condiciones para poder realizar
paginaciones con formularios de filtros.**
Ejemplo: controllers/personas_controller.php `
<?php

PersonasControllerextendsAppController
{
....
functionindex()
{
if(!empty($this->data))
{
$this->SessionPaginate->clean();
$conditions=Set::filter($this->postConditions($this->data,$op));
}

$this->set('personas',$this->SessionPaginate->paginate('Persona',$cond
itions));
}
...
}
` **components/sessionpaginate.php** `
<?php
/**
*@nolicense
*@date24junio2011
*/

classSessionPaginateComponentextendsObject
{
var$controller=null;
var$components=array('Session');

functioninitialize(&$controller,$settings=array())
{
$this->controller=$controller;

}

/**
*Limpiadatostemporales.
*/
functionclean()
{
@$this->Session->delete('session_paginate_vars');
@$this->Session->delete('session_paginate_data');
}

/**
*Alamacenatemporalmenteeldatadelcontrolador.
*/
functiondata($data=array())
{
if(empty($data))
$data=(array)$this->controller->data;
$this->Session->write('session_paginate_data',$data);
}

/**
*mismoquecontroller->paginate(...),perosemantiene
*lascondicionesparapodernavegaratravésdelaspáginas
*/
functionpaginate($object=NULL,$scope=array(),$whitelist=array())
{
if($this->Session->check('session_paginate_vars'))
$scope=$this->Session->read('session_paginate_vars');
else
$this->Session->write('session_paginate_vars',$scope);

if($this->Session->read('session_paginate_data'))
{
$this->controller->data=is_array($this->controller->data)?$this->contr
oller->data:array();
$this->controller->data=array_merge($this->controller->data,$this->Ses
sion->read('session_paginate_data'));
}

return$this->controller->paginate($object,$scope,$whitelist);
}
}
?>
`


.. author:: lenadro
.. categories:: articles
.. tags:: filters search buscador filtro,Articles

