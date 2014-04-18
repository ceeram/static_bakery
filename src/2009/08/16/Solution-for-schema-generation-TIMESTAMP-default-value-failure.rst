Solution for schema generation TIMESTAMP default value failure
==============================================================

There are several tickets (#6225, #6205, #5766) as well as relatively
significant number of posts on the Internet devoted to the improper
handling of TIMESTAMP's default value when using CakePHP schema
generation feature. I'd like to offer a quick yet handy fix for the
issue. The fix is pretty simple and you can adapt it to fit your needs
without any hassle.
The code responsible for column creation is in the
cake/libs/model/datasources/dbo_source.php file. Scan for the
â€œfunction buildColumn()â€. The particular place where default DB
fields values are handled is this:


Component Class:
````````````````

::

    <?php 
    	if (($column['type'] == 'integer' || $column['type'] == 'float' ) && isset($column['default']) && $column['default'] === '') {
    			$column['default'] = null;
    		}
    
    		if (isset($column['key']) && $column['key'] == 'primary' && $type == 'integer') {
    			$out .= ' ' . $this->columns['primary_key']['name'];
    		} elseif (isset($column['key']) && $column['key'] == 'primary') {
    			$out .= ' NOT NULL';
    		} elseif (isset($column['default']) && isset($column['null']) && $column['null'] == false) {
    			$out .= ' DEFAULT ' . $this->value($column['default'], $type) . ' NOT NULL';
    		} elseif (isset($column['default'])) {
    			$out .= ' DEFAULT ' . $this->value($column['default'], $type);
    		} elseif (isset($column['null']) && $column['null'] == true) {
    			$out .= ' DEFAULT NULL';
    		} elseif (isset($column['null']) && $column['null'] == false) {
    			$out .= ' NOT NULL';
    		}
    		return $out;
    
    ?>

Our fix will be as simple as it can be yet it will serve the purpose
of correct column generation. Let's see what we can do:


Component Class:
````````````````

::

    <?php 
    if (($column['type'] == 'integer' || $column['type'] == 'float' ) && isset($column['default'])
    && $column['default'] === '') {
    $column['default'] = null;
    } elseif ($column['type'] =='timestamp' && $column['default'] != 'CURRENT_TIMESTAMP') {
                            $column['default'] = '2000-01-01 00:00:00';
                    }
    
    if (isset($column['key']) && $column['key'] == 'primary' && $type == 'integer') {
    $out .= ' ' . $this->columns['primary_key']['name'];
    } elseif (isset($column['key']) && $column['key'] == 'primary') {
    $out .= ' NOT NULL';
    } elseif (isset($column['default']) && isset($column['null']) && $column['null'] == false) {
                if ($column['type'] == 'timestamp' && $column['default']=='CURRENT_TIMESTAMP'){
                   $out .= ' DEFAULT CURRENT_TIMESTAMP NOT NULL';
                } else {
                   $out .= ' DEFAULT ' . $this->value($column['default'], $type) . ' NOT NULL';
                }
    } elseif (isset($column['default'])) {
    $out .= ' DEFAULT ' . $this->value($column['default'], $type);
    } elseif (isset($column['null']) && $column['null'] == true) {
    $out .= ' DEFAULT NULL';
    } elseif (isset($column['null']) && $column['null'] == false) {
    $out .= ' NOT NULL';
    }
    return $out;
    }
     
    ?>

Actually, we added a trick for all the next timestamp fields (except
the one with CURRENT_TIMESTAMP default value) to have arbitrary not
NULL default values. Change the value of the $column['default'] =
'2000-01-01 00:00:00'; to fit your needs when you need certain default
values for timestamps. You can also change the logic to handle other
issues you might have with your DB schema.

Another trick is to have CURRENT_TIMESTAMP default value unquoted by
CakePHP schema code (which actually prevents the schema script from
running).

Tricks above are pretty dumb, but Iâ€™d not bother with the perfect
solution as weâ€™re going to have one from â€œthe clean IPâ€ really
soon (I hope).


.. author:: shulga
.. categories:: articles, general_interest
.. tags::
CakePHP,generate,schema,currenttimestamp,timestamp,solution,General
Interest

