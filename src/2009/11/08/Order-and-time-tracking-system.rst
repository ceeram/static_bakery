Order and time tracking system
==============================

A short introduction to the features of our order and time tracking
system, build with CakePHP.
We have a lot of orders coming in and out of the office, and many
people are working on the same jobs, so it is essential to have a
functioning order and time tracking system. Therefor I developed a
system in raw PHP for about 2 years ago, and this is the base for the
newly fully rewritten system we are currently using today, made with
CakePHP.

I will try to make a short tour of the system here, starting by
explaining it in a few sentences.

With the order system we can ...

+ create an address book of clients, and our contacts at the clients
+ create new orders, attached to a client and client contact person,
  with order numbers generated automatically.
+ track time used on the order.
+ set an hour rate per employee to help writing invoices, and generate
  estimate income for ongoing orders in a given period.
+ generate lists of orders, for given time periods, with detailed
  information about income, hours spent etc.
+ add purchases and sales to orders to see how much has been earned
  for a single order.
+ write an invoice which is saved as XML ready to import to an
  InDesign template, with all data pre-written, VAT calculated etc.



Order system walkthrough
````````````````````````
To be able to add invoices, you will first need to create a client and
add a client employee, for you order contact person.

When adding the client you also need to select which of VAT zone the
client is in, to be able to automatically calculate VAT on invoices.
Adding an employee looks about the same. Adding either of these will
create an entry in our LDAP database, making sure that all phone
numbers are to be found on and recognized by our desktop phones,
e-mails in e-mail applications etc.

When a client and client employee is added, you can add an order. On
the order you have the option to describe the order in details and
adding different deadline dates. Adding these dates will add an entry
in the system users calendars, since the system publishes an .ics
calendar everybody subscribes to. Further, the system also creates a
folder on a shared IMAP account, making everyone able to move e-mails
related to the order to a shared destination, where everybody has
access.


When the order is added, it is possible to track the time on the
order. Selecting an order is easy, as drop boxes with orders are
updated depending on which client is selected in the client drop box.
With a date selected, there is only room for a descriptive text and
how many hours is used that given date.

To track time even easier, I am currently developing a Dashboard
Widget with a stopwatch, where it is possible to select a specific
order, start the timer, pausing, or stopping and submitting it
automatically.

The order list is most important, in my opinion. Here we are able to
output a list of all orders, for many different purposes. Historical,
overview of open/active orders, to make an estimate of ongoing work
etc.

For the single order it is of course possible to get an overview of
spend hours and their value. It is also possible to send an
automatically generated order confirmation to the client contact
person, with sales- and delivery terms etc. attached.

Furthermore you can add sales, purchases and invoices to the order.
Adding an invoice creates an XML ready to import to an InDesign
template, leaving almost no manual work.

Besides this, there are several extra smaller details in the system,
but this should give a clear picture of it.

This is my first Cake project, and I really feel like doing more!

(originally posted `here`_)

.. _here: http://indesigning.net/orders-time-tracking-what-is-your-approach

.. author:: Silkjaer
.. categories:: articles, case_studies
.. tags:: order system,Case Studies

