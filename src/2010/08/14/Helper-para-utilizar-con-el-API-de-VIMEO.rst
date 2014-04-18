Helper para utilizar con el API de VIMEO
========================================

Este es el cÃ³digo correspondiente a un helper para poder obtener los
valores del xml que vimeo permite obtener atravez de su API.


Tutorial
~~~~~~~~

Vimeo tiene la opciÃ³n de general un xml con los detalles del video
atravez de una direcciÃ³n estÃ¡ndar, por ejemplo:

`http://vimeo.com/api/v2/video/8230536.xml`_
Cambiando 8230536 por el id del video de vimeo deseado obtendremos un
xml como este:

::

    
    <?xml version="1.0" encoding="utf-8"?>
    <videos>
      <video>
        <id>8230536</id>
        <title>tulip</title>
        <description>This is my school project using Flash.</description>
        <url>http://vimeo.com/8230536</url>
        <upload_date>2009-12-16 20:51:00</upload_date>
        <thumbnail_small>http://ats.vimeo.com/378/185/37818536_100.jpg</thumbnail_small>
        <thumbnail_medium>http://ats.vimeo.com/378/185/37818536_200.jpg</thumbnail_medium>
        <thumbnail_large>http://ats.vimeo.com/378/185/37818536_640.jpg</thumbnail_large>
        <user_name>noriko</user_name>
        <user_url>http://vimeo.com/noriko</user_url>
        <user_portrait_small>http://ps.vimeo.com.s3.amazonaws.com/285/285786_30.jpg</user_portrait_small>
        <user_portrait_medium>http://ps.vimeo.com.s3.amazonaws.com/285/285786_75.jpg</user_portrait_medium>
        <user_portrait_large>http://ps.vimeo.com.s3.amazonaws.com/285/285786_100.jpg</user_portrait_large>
        <user_portrait_huge>http://ps.vimeo.com.s3.amazonaws.com/285/285786_300.jpg</user_portrait_huge>
        <stats_number_of_likes>0</stats_number_of_likes>
        <stats_number_of_plays>72</stats_number_of_plays>
        <stats_number_of_comments>0</stats_number_of_comments>
        <duration>25</duration>
        <width>640</width>
        <height>400</height>
        <tags>flash, animation, flash animation, school project, interactive multimedia, motion graphic, flower, tulip</tags>
      </video>
    </videos>



Helper
~~~~~~

Simplemente, vamos a hacer un helper como este:

Helper Class:
`````````````

::

    <?php 
    
    class XmlvimeoHelper extends AppHelper
    {
     
        function xml($id)
        {
             // clase XML de cakephp
    	  App::import('Xml');
      
    	  // url del api de vimeo
    	  $file = 'http://vimeo.com/api/v2/video/'.$id.'.xml';
      
    	  // Crear el arreglo apartir del XML
    	  $Mixml =& new XML($file);
    	  $Mixml _xml = Set::reverse($Mixml ); 
      
    	  // devuelver el arreglo
    	 return $Mixml ;
        }
    }
    
    ?>



Controlador
~~~~~~~~~~~

En el appcontroller o en el contralador deseado se debe incorporar el
helper:

Controller Class:
`````````````````

::

    <?php 
    class AppController extends Controller {
    	var $helpers = array('Xmlvimeo');
    }
    ?>



Vista
~~~~~

Para utilizar la variable en la vista:

View Template:
``````````````

::

    
    <?php $Mixml = $xmlvimeo->xml('8230536'); ?>



Resultado
~~~~~~~~~

DespuÃ©s de lo anterior, la variable $Mixml serÃ¡ un arreglo de la
siguiente forma:

View Template:
``````````````

::

    
    [Mixml] => Array
    (
        [Videos] => Array
            (
                [Video] => Array
                    (
                        [id] => 8230536
                        [title] => tulip
                        [description] => This is my school project using Flash.
                        [upload_date] => 2009-12-16 20:51:00
                        [thumbnail_small] => http://ats.vimeo.com/378/185/37818536_100.jpg
                        [thumbnail_medium] => http://ats.vimeo.com/378/185/37818536_200.jpg
                        [thumbnail_large] => http://ats.vimeo.com/378/185/37818536_640.jpg
                        [user_name] => noriko
                        [user_url] => http://vimeo.com/noriko
                        [user_portrait_small] => http://ps.vimeo.com.s3.amazonaws.com/285/285786_30.jpg
                        [user_portrait_medium] => http://ps.vimeo.com.s3.amazonaws.com/285/285786_75.jpg
                        [user_portrait_large] => http://ps.vimeo.com.s3.amazonaws.com/285/285786_100.jpg
                        [user_portrait_huge] => http://ps.vimeo.com.s3.amazonaws.com/285/285786_300.jpg
                        [stats_number_of_likes] => 0
                        [stats_number_of_plays] => 72
                        [stats_number_of_comments] => 0
                        [duration] => 25
                        [width] => 640
                        [height] => 400
                        [tags] => flash, animation, flash animation, school project, interactive multimedia, motion graphic, flower, tulip
                    )
    
            )
    
    )



Ejemplo
~~~~~~~

View Template:
``````````````

::

    <?php echo $Mixml['Videos']['Video']['thumbnail_small']; ?>

Con ello se obtiene:
`http://ps.vimeo.com.s3.amazonaws.com/285/285786_30.jpg`_


Muchas Gracias!
~~~~~~~~~~~~~~~


.. _http://vimeo.com/api/v2/video/8230536.xml: http://vimeo.com/api/v2/video/8230536.xml
.. _http://ps.vimeo.com.s3.amazonaws.com/285/285786_30.jpg: http://ps.vimeo.com.s3.amazonaws.com/285/285786_30.jpg

.. author:: cledyulate
.. categories:: articles, helpers
.. tags:: helper xml,cledy,cledyulate,api helper,vimeo xml,vimeo
api,Helpers

