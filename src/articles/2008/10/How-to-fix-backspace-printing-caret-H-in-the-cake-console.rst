How to fix backspace printing caret-H in the cake console
=========================================================

by perceptes on October 22, 2008

If keys such as backspace and the arrow keys are printing control
characters instead of doing what you expect, the solution is quite
simple.
I recently got started with CakePHP, and one little annoyance I'd come
across was some keys printing literal control characters inside the
Cake console. The critical key here was backspace printing ^H instead
of actually backspacing. So if I made a mistake while typing something
in while baking files, I'd often have to quit out of the whole console
and start over.

As it turns out, this problem is not really specific to Cake - it's an
issue of shell's terminal type. I'm working on Mac OS X Leopard from
the standard bash shell. In Terminal's preferences, under Settings >
Advanced, changing "Declare terminal as" from xterm-color to vt100
made the keys behave correctly within the Cake console.

.. meta::
    :title: How to fix backspace printing caret-H in the cake console
    :description: CakePHP Article related to Console,backspace,control h,terminal,terminal type,General Interest
    :keywords: Console,backspace,control h,terminal,terminal type,General Interest
    :copyright: Copyright 2008 perceptes
    :category: general_interest

