Crop and resize images in cakePHP
=================================

by edap on August 14, 2012

CakePHP helper and component to crop and resize images using
jWindowCrop
I've just created an helper and a component to make images thumbs easy
to create. The code is avaliable at this `git repo`_.
I've used `jWindowCrop`_, a jquery plugin, in order to create the crop
interface. There are a lot of plugins out there to do this particular
job, probably the mosts famous are `Jcrop`_ and `imgAreaSelect`_. I've
tried both of them, they are really well done and extremely powerfull,
neverthless, i've prefered jWindowCrop for his clearer interface.
There is only one window containing the picture that you want to crop,
you can work directly on this one, using only 2 controls: the zoom and
the drag movement.I think it's enough to do the job. I've found smart
the way to avoid the preview picture, used in the others two plugin.
But sure, this works only if you know exactly the size of the picture
that you want to obtain.
> To install it in your application and start to crop the images that
you have previously uploaded: <ul> <li>copy app
View/Helper/CropHelper.php in your app/View/Helper folder
