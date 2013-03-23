PhpExcel helper for generating excel files
==========================================

by %s on April 02, 2012

PhpExcel consists only from one helper class that uses PHPExcel
project (located in vendors) to generate excel files.

PHPExcel is a great library that can create XLS files. For more
information see `PHPExcel project homepage`_.

I added method for setting font and for easy table data adding (see
example).

This plugin is for CakePHP 2.x

Short example:
`
//Controller:
public$helpers=array('PhpExcel.PhpExcel');
`

`
//View:
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri',12);

//definetablecells
$table=array(
array('label'=>__('User'),'width'=>'auto','filter'=>true),
array('label'=>__('Type'),'width'=>'auto','filter'=>true),
array('label'=>__('Date'),'width'=>'auto'),
array('label'=>__('Description'),'width'=>50,'wrap'=>true),
array('label'=>__('Modified'),'width'=>'auto')
);

//heading
$this->PhpExcel->addTableHeader($table,array('name'=>'Cambria','bold'=
>true));

//data
foreach($dataas$d){
$this->PhpExcel->addTableRow(array(
$d['User']['name'],
$d['Type']['name'],
$d['User']['date'],
$d['User']['description'],
$d['User']['modified']
));
}

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output();
`
Download the code from `https://github.com/segy/PhpExcel`_


.. _https://github.com/segy/PhpExcel: https://github.com/segy/PhpExcel
.. _PHPExcel project homepage: http://phpexcel.codeplex.com/
.. meta::
    :title: PhpExcel helper for generating excel files
    :description: CakePHP Article related to helper,xls,excel,plugin,phpexcel,xlsx,Plugins
    :keywords: helper,xls,excel,plugin,phpexcel,xlsx,Plugins
    :copyright: Copyright 2012 
    :category: plugins

