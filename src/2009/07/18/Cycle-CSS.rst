Cycle CSS
=========

by markgandolfo on July 18, 2009

Cycle CSS is a helper that will output alternating text, its very
useful for doing alternating row colors in a table.
Find it
`http://github.com/markgandolfo/cakephp_cycle_css/tree/master`_
So To use it,


View Template:
``````````````

::

    
    <table>
     <? foreach(array(1,2,3) as $item) : ?>
        <tr class="<?= $cycle->css() ?>">
          <td><?= $item ?></td>
        </tr>
     <? endforeach ?>
    </table>

Will give us the following output


View Template:
``````````````

::

    
    <table>
      <tr class="odd">
       <td>1</td>
      </tr>
      <tr class="even">
        <td>2</td>
      </tr>
      <tr class="odd">
        <td>3</td>
      </tr>
     </table>

If you wanted to show the class tags also


View Template:
``````````````

::

    
    <table>
     <? foreach(array(1,2,3) as $item) : ?>
        <tr <?= $cycle->css(array('odd','even'), true) ?>>
          <td><?= $item ?></td>
        </tr>
     <? endforeach ?>
    </table>

Output is as follow


View Template:
``````````````

::

    
    <table>
      <tr class="odd">
       <td>1</td>
      </tr>
      <tr class="even">
        <td>2</td>
      </tr>
      <tr class="odd">
        <td>3</td>
      </tr>
    </table>



.. _http://github.com/markgandolfo/cakephp_cycle_css/tree/master: http://github.com/markgandolfo/cakephp_cycle_css/tree/master

.. author:: markgandolfo
.. categories:: articles, helpers
.. tags:: CSS,Color,colors,colours,alternating,cycle,alternate,Helpers

