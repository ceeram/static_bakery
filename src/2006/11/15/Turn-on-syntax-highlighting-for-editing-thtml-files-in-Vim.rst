Turn on syntax highlighting for editing .thtml files in Vim
===========================================================

by dvoita on November 15, 2006

If you use Vim to edit your Views, here's how to turn on syntax
highlighting.
If you use VIm as your text editor like I do, you probably find the
syntax highlighting helpful. Unfortunately, files ending in *.thtml
aren't recognized by Vim. Here's how to fix that:

(Examples are for Unix)

1. Create a user runtime directory:

::

    
    mkdir ~/.vim

2. Create the following file:

::

    
    vi ~/.vim/filetype.vim

It should contain:

::

    
    " my filetype file
        if exists("did_load_filetypes")
          finish
        endif
        augroup filetypedetect
          au! BufRead,BufNewFile *.thtml     setfiletype html
        augroup END

3. Restart Vim, if need be.

This should make editing your Views with Vim a little easier.

Sources:

`http://www.vim.org/htmldoc/filetype.html`_



.. _http://www.vim.org/htmldoc/filetype.html: http://www.vim.org/htmldoc/filetype.html

.. author:: dvoita
.. categories:: articles, general_interest
.. tags:: views,thtml,Vim,tips,General Interest

