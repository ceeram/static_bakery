Table Helper
============

by scottsanders on May 08, 2009

The following code allows you to rapidly create nice tables. Complete
with pagination controls, and a host of options to allow you to
customize the resulting table. Hope it's useful!


Usage
`````

First, download the attached helper and put it in app/views/helpers/.
Here is a sample controller that uses the helper to make a nice table
of widgets in your view.

Example
+++++++

This assumes we have a widgets table with a schema that includes
columns for id, name, and type.
Example of the results: `http://jssjr.com/widgets_table.png`_

Controller Class:
`````````````````

::

    <?php 
    class WidgetController extends AppController {
      var $name = 'Widget';
      var $helpers = array('Table');
    
      function index() {
        $this->set('widgets', $this->paginate());
      }
    }
    ?>


View Template:
``````````````

::

    <h1>Widgets</h1>
    <?php
    echo $table->render($widgets,
                        array(
                          'Name' => array(
                            'path' => 'Widget.name',
                            'link' => array('controller'=>'widgets','action'=>'view','id'=>'Widget.id')),
                          'Type' => 'Widget.type')); 
    ?>



Helper Class:
`````````````

::

    <?php 
    /**
     * Table Helper for CakePHP 1.2.x.x
     * @author Scott Sanders (scott.sanders@reachsmart.com)
     * @version 1.0
     *
     * Usage:
     *
     * In the controller be sure to include the Table helper, then set the data 
     * array to be tabelized with $this->set(). The helper assumes you have the 
     * fam fam fam silk icon set installed in IMAGES/icons/, but you can override
     * the default images to use, or disable pretty pagination controls by setting 
     * pretty_paginate to false.
     *
     * If you want paginatable tables use $this->set('foo', $this->paginate());
     * otherwise just set the data and set paginate=>false in the helper options.
     *
     * In the view call the helper like so: 
     * echo $table->render($data,
     *                     array(
     *                       'Column Title' => 'Model.field',
     *                       'Column Two' => 'Model.otherField'),
     *                     array( helper options ),
     *                     array( options to pass to pagination )); 
     * Additionally you can make table fields into links like this:
     * Note that the string in the id field of an URL array will be set to the 
     * correct value when the link is created.
     * echo $table->render($data,
     *                     array(
     *                       'Column Title' => array(
     *                         'path' => 'Model.field',
     *                         'link' => array('controller'=>'my_controller','action'=>'my_action','id'=>'Model.field'),
     *                       'Column Two' => 'Model.otherField'),
     *                     array( helper options ),
     *                     array( options to pass to pagination )); 
     *
     * 
     * The available helper options and their defaults are:
     *   'paginate'           => true,  // Show pagination controls by default
     *   'pretty_paginate'    => true,  // Use images for pagination controls
     *   'pp_first'           => 'icons/control_start_blue.png',
     *   'pp_prev'            => 'icons/control_rewind_blue.png',
     *   'pp_prev_disabled'   => 'icons/control_rewind.png',
     *   'pp_next'            => 'icons/control_fastforward_blue.png',
     *   'pp_next_disabled'   => 'icons/control_fastforward.png',
     *   'pp_last'            => 'icons/control_end_blue.png',
     *   'class'              => 'ctable'  // Class name to give the wrapping div
     *
     * (A sample CSS stylesheet is included at the bottom of this file)
     * Hopefully someone finds this useful!
     */ 
    
    class TableHelper extends AppHelper {
      public $helpers = array('Html', 'Paginator');
      // Options (defaults)
      private $__options = array(
        'paginate'           => true,  // Show pagination controls by default
        'pretty_paginate'    => true, // Use images for pagination controls
        'pp_first'           => 'icons/control_start_blue.png',
        'pp_prev'            => 'icons/control_rewind_blue.png',
        'pp_prev_disabled'   => 'icons/control_rewind.png',
        'pp_next'            => 'icons/control_fastforward_blue.png',
        'pp_next_disabled'   => 'icons/control_fastforward.png',
        'pp_last'            => 'icons/control_end_blue.png',
        'class'              => 'ctable'
        );
      private $__paginationOptions = array();
    
      private function __renderPaginationControls() {
        $output = '<div class="pagination_controls">';
        if ($this->__options['pretty_paginate']) {
          $output .= $this->Paginator->first($this->Html->image('icons/control_start_blue.png', array('border'=>0, 'alt'=>'Start', 'title'=>'Start')), array('escape'=>false), null, array('class'=>'disabled')); 
        } else {
          $output .= $this->Paginator->first('<<', array('escape'=>false), null, array('class'=>'disabled')).'&nbsp'; 
        }
        if ($this->__options['pretty_paginate']) {
          if ($this->Paginator->hasPrev()) {
            $output .= $this->Paginator->prev($this->Html->image('icons/control_rewind_blue.png', array('border'=>0, 'alt'=>'Previous', 'title'=>'Previous')), array('escape'=>false), null, array('class'=>'disabled', 'escape'=>false)); 
          } else {
            $output .= $this->Paginator->prev($this->Html->image('icons/control_rewind.png', array('border'=>0, 'alt'=>'Previous', 'title'=>'Previous')), array('escape'=>false), null, array('class'=>'disabled', 'escape'=>false)); 
          } 
        } else {
          $output .= $this->Paginator->prev('<', array('escape'=>false), null, array('class'=>'disabled', 'escape'=>false)); 
        }
        $output .= $this->Paginator->counter(array('format'=>' (%start% - %end% of %count%) ')); 
        if ($this->__options['pretty_paginate']) {
          if ($this->Paginator->hasNext()) {
            $output .= $this->Paginator->next($this->Html->image('icons/control_fastforward_blue.png', array('border'=>0, 'alt'=>'Next', 'title'=>'Next')), array('escape'=>false), null, array('class'=>'disabled', 'escape'=>false)); 
          } else {
            $output .= $this->Paginator->next($this->Html->image('icons/control_fastforward.png', array('border'=>0, 'alt'=>'Next', 'title'=>'Next')), array('escape'=>false), null, array('class'=>'disabled', 'escape'=>false)); 
          }
        } else {
          $output .= $this->Paginator->next('>', array('escape'=>false), null, array('class'=>'disabled', 'escape'=>false)).'&nbsp'; 
        }
        if ($this->__options['pretty_paginate']) {
          $output .= $this->Paginator->last($this->Html->image('icons/control_end_blue.png', array('border'=>0, 'alt'=>'End', 'title'=>'End')), array('escape'=>false), null, array('class'=>'disabled')); 
        } else {
          $output .= $this->Paginator->last('>>', array('escape'=>false), null, array('class'=>'disabled')); 
        }
        $output .= "<span></span>";
        $output .= "</div>";
        return $output;
      }
    
      public function render($data, $columns, $options = array(), $pagination_options = array()) {
        // Start table div
        if (is_array($options)) {
          $this->__options = array_merge($this->__options, $options);
        }
        if (is_array($pagination_options)) {
          $this->__paginationOptions = array_merge($this->__paginationOptions, $pagination_options);
        }
        if ($this->__options['paginate']) {
          $this->Paginator->options($this->__paginationOptions);
        }
        $output  = "<div class=\"ctable\">";
        // Add pagination controls
        if ($this->__options['paginate']) {
          $output .= $this->__renderPaginationControls();
        }
        // Start data table
        $output .= "<table>";
        // Column headers
        $output .= "<thead>";
        foreach ($columns as $title => $field) {
          if (!is_array($field)) {
            if ($this->__options['paginate']) {
              $output .= "<th>".$this->Paginator->sort($title, $field, array('class'=>(($this->Paginator->sortKey() == end(explode('.', $field))) ? $this->Paginator->sortDir() : false)))."</th>";
            } else {
              $output .= "<th>$title</th>";
            }
          } else {
            if ($this->__options['paginate']) {
              $output .= "<th>".$this->Paginator->sort($title, $field['path'], array('class'=>(($this->Paginator->sortKey() == end(explode('.', $field['path']))) ? $this->Paginator->sortDir() : false)))."</th>";
            } else {
              $output .= "<th>$title</th>";
            }
          }
        }
        $output .= "</thead>";
        $output .= "<tbody>";
        // Output rows of data
        for ($i=1;$i<=count($data);$i++) { 
          if ($i % 2 == 1) { 
            $output .= '<tr>'; 
          } else { 
            $output .= '<tr class="altrow">'; 
          }
          foreach ($columns as $col => $content) {
            if (!is_array($content)) {
              $output .= '<td>'.array_shift(Set::extract('/'.preg_replace('/\./', '['.$i.']/', $content), $data)).'</td>';
            } else {
              $output .= '<td>';
              if (isset($content['link'])) {
                if(is_array($content['link'])) {
                  // Expand model keys in link (most likely just for id's)
                  foreach($content['link'] as $k => $v) {
                    if (preg_match('/\./', $v)) {
                      $content['link'][$k] = array_shift(Set::extract('/'.preg_replace('/\./', '['.$i.']/', $v), $data));
                    }
                  }
                  $output .= $this->Html->link(array_shift(Set::extract('/'.preg_replace('/\./', '['.$i.']/', $content['path']), $data)), $content['link']);
                } else {
                  if (preg_match('/^\w+\.\w+$/', $content['link'])) {
                    $content['link'] = array_shift(Set::extract('/'.preg_replace('/\./', '['.$i.']/', $content['link']), $data));
                  }
                  $output .= $this->Html->link(array_shift(Set::extract('/'.preg_replace('/\./', '['.$i.']/', $content['path']), $data)), $content['link']);
                }
              } else {
                $output .= array_shift(Set::extract('/'.preg_replace('/\./', '['.$i.']/', $content['path']), $data));
              }
              $output .= '</td>';
            }
          }
          $output .= '</tr>';
        } 
        $output .= "</tbody>";
        $output .= "</table>";
        // Repeat pagination controls
        if ($this->__options['paginate']) {
          $output .= $this->__renderPaginationControls();
        }
        // Close table div 
        $output .= "</div>";
    
        return $this->output($output);
      }
    
    }
    
    /* Sample CSS to use */
    /*
    .ctable {
      border-left: 1px solid #a6a6a6;
      border-right: 1px solid #a6a6a6;
      border-bottom: 1px solid #a6a6a6;
    }
    .ctable .pagination_controls {
      font-size: 90%;
      border-top: 1px solid #a6a6a6;
      background: #f6f6f6;
      width:100%;
      text-align:right;
      padding:1px 0 2px 0;
    }
    .ctable .pagination_controls span {
      padding-right:2px;
    }
    .ctable table {
      border-collapse:collapse;
      background: #fff;
      font-size: 100%;
      width:100%;
    }
    .ctable table td, .ctable table th {
      padding:2px;
    }
    .ctable thead {
      border-top: 1px solid #a6a6a6;
      border-bottom: 1px solid #a6a6a6;
      background: #e2e2e2;
      font-size: 105%;
    }
    .ctable thead th {
      text-align:left;
      border-left:1px solid #a6a6a6;
    }
    .ctable thead th:first-child {
      border:none;
    }
    .ctable thead a {
      text-decoration:none;
      color: #404040;
    }
    .ctable thead a:hover {
      color:#000;
    }
    .ctable thead a.asc {
      background: url('../img/elements/tables/sort_asc.gif') no-repeat center right;
      padding-right:13px;
    }
    .ctable thead a.desc {
      background: url('../img/elements/tables/sort_desc.gif') no-repeat center right;
      padding-right:13px;
    }
    .ctable tbody tr{
      border-top:1px solid #e0e0e0;
    }
    .ctable tbody tr:first-child {
      border: none;
    }
    .ctable tbody tr td:first-child {
      border:none;
    }
    .ctable tbody tr.altrow {
      background: #edf5ff;
    } 
    .ctable tbody tr td a {
      text-decoration: none;
    }
    */
    ?>



.. _http://jssjr.com/widgets_table.png: http://jssjr.com/widgets_table.png

.. author:: scottsanders
.. categories:: articles, helpers
.. tags:: helper,table,Helpers

