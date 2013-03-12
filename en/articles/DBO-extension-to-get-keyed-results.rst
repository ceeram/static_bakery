

DBO extension to get keyed results
==================================

by %s on November 30, 2010

I have often found the need to have the result of a find( 'all' )
keyed to the tables id. I had been doing this in the code with an,
often expensive, foreach loop: [code] $keyResults = array(); foreach(
$results as $result ) { $keyResults[ $result[ 'Model' ][ 'id' ] ] =
$result; } [/code] I didn't like this so I have created an extension
to the MySqli DBO object (as I am using MySqli) that recreates the
find methods to return a result keyed to the tables id
App Model
I have been using Matt Currys excellent custom find ( described in
this e-book `http://www.pseudocoder.com/free-cakephp-book`_ ) to
create my own 'cake like' finds e.g. $this->Model->find( 'custom' );
So I used this method to create a new custom find in my app_model.php



.. _http://www.pseudocoder.com/free-cakephp-book: http://www.pseudocoder.com/free-cakephp-book
.. meta::
    :title: DBO extension to get keyed results
    :description: CakePHP Article related to dbo extension,Articles
    :keywords: dbo extension,Articles
    :copyright: Copyright 2010 
    :category: articles

