Translate behavior - bindTranslation problem
============================================

One problem I noticed is that when i used the bindTranslation, the
results came mixed up. I meen the index of the array was 0 or 1 and
the languages didn't follow the same rule.
Hello,

I had to develop a multilanguage website.
I used the Translate behavior. The admin had to fill the content in 2
languages. He also had to see both contents and edit them.
One problem I noticed is that when i used the bindTranslation, the
results came mixed up. I meen the index of the array was 0 or 1 and
the languages didn't follow the same rule.

I opened translate.php in cake/lib/model/behaviors and found the
bindTranslation method inside the file.

There is a line:

::

    
    $default = array('className' => $RuntimeModel->alias, 'foreignKey' => 'foreign_key');

You should replace it with:

::

    
    $default = array('className' => $RuntimeModel->alias, 'foreignKey' => 'foreign_key', 'order'=>'locale');

Now both translation were where i expected.

Hope it helps!

Alex


.. author:: alexdeefuse
.. categories:: articles, tutorials
.. tags:: ,Tutorials

