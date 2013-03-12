

Fulltext search and i18n
========================

by %s on February 20, 2011

Fulltext MySQL search using `MATCH () AGAINST ()` and the i18n tables
requires a more complex search query.

First, add a `FULLTEXT` index to your i18n table: `ALTER TABLE `i18n`
ADD FULLTEXT `content` ( `content` );`

For your query, you need to create an "OR" condition with an entry for
every field you want to add to your search. In my example, I search
the 'title' and 'description' fields. The "I18n__" prefix for your
fieldname is the automagic CakePHP naming for your i18n content
relations.

` $conditions = array('OR' => array()); foreach(
array('name','description') as $field ) {
$conditions['OR']['MATCH(I18n__'.$field.'.content) AGAINST(? IN
BOOLEAN MODE)'] = $keywords; } $this->$model->find('all',
array('conditions' => $conditions)); `


.. meta::
    :title: Fulltext search and i18n
    :description: CakePHP Article related to MySQL fulltext match against i,Articles
    :keywords: MySQL fulltext match against i,Articles
    :copyright: Copyright 2011 
    :category: articles

