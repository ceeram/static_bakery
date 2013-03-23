Talking to multiple databases from single cakePHP application
=============================================================

by %s on September 02, 2008

This is a small tutorial on how to talk to multiple databases from a
single cakePHP application. Generally say a scenario arises when we
would have two seperate databases one for our business data and the
other to store log data, Say in one case you would like to pull the
data from both the databases, We have a simple connection setting with
which we could acheive this.
Lets put the pieces together:

In your database.php We already have an additional driver

// database connection parameters
var $backup = array('driver' => 'mysql',
'connect' => 'mysql_connect',
'host' => 'localhost',
'login' => 'root',
'password' => '',
'database' => 'backup',
'prefix' => '');

Fill in the details , We could add as many additional drivers we want
and talk to different databases.

Now say we have a table in the backup database which is used to save
the user login details say a simple table structure with UserID ,
LoginTime, LogoutTime .

Say the table name is LoginLogs and our database name is backup

Then our model would be as followed :

class LoginLog extends AppModel {
// table information
var $name = 'LoginLog';
// define which database driver the model
// needs to look upon
var $useDbConfig = 'backup';
// Table Name
var $useTable = 'LoginLogs';
var $primaryKey = '';
var $cacheQueries = false;
}
?>
Basically the line
var $useDbConfig = 'backup';

Defines which database to talk upon and the rest is the same as our
basic model definition

and in the controller just add the model as we do for normal model

uses ('sanitize');
class MyController extends AppController {
var $name='My';
var $uses = array('User','LoginLog');
function index() {
// here we could call the model as we call all the models
// and it would behave as a normal model
$logs = $this->LoginLog->findAll();
$this->set('logs', $logs);
}
}
?>

.. meta::
    :title: Talking to multiple databases from single cakePHP application
    :description: CakePHP Article related to database,Tutorials
    :keywords: database,Tutorials
    :copyright: Copyright 2008 
    :category: tutorials

