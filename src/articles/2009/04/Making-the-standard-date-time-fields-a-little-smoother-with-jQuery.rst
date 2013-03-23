Making the standard date/time fields a little smoother with jQuery
==================================================================

by %s on April 22, 2009

While working with the date/time input fields in Cake I got tired of
having to select 3/6 drop down boxes to choose all of the date/time
information and specifically of having to select 3/6 drop down boxes
if I decided to clear the date. A little bit of jQuery will clear this
right up though.
Just add this this piece of jQuery code and all of your drop down
boxes will gain the following behavior:

- If all of the values are blank and you choose a value for any of
them, all the others will be set to the first value in the list -
except for the year which will become the current year.
- If you set any of the values back to the empty cell, all of them
will select the empty cell.

Now just drop in this jQuery code and you should be good to go.

::

    
       $('div.input.datetime select').change(function() {
          if($(this).val() == '') {
             $(this).siblings('select').val('');
          }
          else {
             $(this).siblings('select').each(function(sel) {
                if($(this).val() == '') {
                   if($(this).attr('id').indexOf('Year') != -1) 
                      $(this).val((new Date()).getFullYear());
                   else this.selectedIndex = 1;               
                }
             });
          }
       });


.. meta::
    :title: Making the standard date/time fields a little smoother with jQuery
    :description: CakePHP Article related to jquery,form,datetime,date,time,brightball,Snippets
    :keywords: jquery,form,datetime,date,time,brightball,Snippets
    :copyright: Copyright 2009 
    :category: snippets

