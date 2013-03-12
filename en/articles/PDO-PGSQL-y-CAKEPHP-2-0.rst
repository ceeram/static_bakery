

PDO_PGSQL y CAKEPHP 2.0
=======================

by %s on March 13, 2012

Solución a los problemas de conexión a la base de datos con objetos
PDO con cakephp 2.x

Comencé recientemente a utilizar el cakephp 2.x, y con la nueva
versión vinieron los nuevos problemas. cree el proyecto sin
inconvenientes, cocine los modelos, controladores y vistas en un
soplido, y cuando eche a correr la aplicación me salto este error:

Cake is NOT able to connect to the database. Database connection
"SQLSTATE[08006] [7] could not connect to server: Permission denied Is
the server running on host "127.0.0.1" and accepting TCP/IP
connections on port 5432?" is missing, or could not be created.

alli comenzo una travesia de ensayo, error y busqueda sin fin que
después de día y medio leí que este error es provocado por SElinux,
que interpreta la petición del httpd como un ataque y lo bloquea, y lo
resolví con el siguiente comando (linux):


setsebool -P httpd_can_network_connect 1.
=========================================

aquí le dejo el link donde conseguí la solución:`http://www.cristea.me
/linux-permission-denied-on-apache-while-connecting-to-postgresql/`_

espero que le sea de utilidad y le ahorre tiempo e inconvenientes.
saludos


.. _http://www.cristea.me/linux-permission-denied-on-apache-while-connecting-to-postgresql/: http://www.cristea.me/linux-permission-denied-on-apache-while-connecting-to-postgresql/
.. meta::
    :title: PDO_PGSQL y CAKEPHP 2.0
    :description: CakePHP Article related to PDO SQLSTATE[08006] [7],Articles
    :keywords: PDO SQLSTATE[08006] [7],Articles
    :copyright: Copyright 2012 
    :category: articles

