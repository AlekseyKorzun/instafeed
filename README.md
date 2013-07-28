Instafeed (v1.0.0)
==========================

Instafeed package contains an extremely simple API adapter for Instagram that you
can easily extend.

The http://instagram.com/developer/endpoints/tags/ end-point support is available out of the
box as an example.

From packages I saw, you would either get a huge beast of an API client that tries to do everything
for you, or a half ass bare bone one that is poorly made and could not be extended without hacking
tons of things together.

I also did not like the fact that developers thought it was okay to simply drop random 'callback.php'
files with their package and 'index.php' that forwards you to an authorization URI.

So, another thing that this package includes is token exchange and a demo application that utilizes
tags resource to generate data.

It's build on Silex (micro-framework) and uses Bootstrap via Twig engine. Super easy to deploy and play around with.

You can view demo here: http://instafeed.webfoundation.net

If you are a fan of having a huge screen with metrics at your office, you can easily tweak and integrate
this to show data for tags that you want to monitor ;)

Installation
-----

Visit http://getcomposer.org to install composer on your system.

After installation simply run `composer install` in parent directory of this distribution to generate vendor/
directory that contains a cross system autoloader and required libraries.

You should be able to use adapter by loading \Instafeed\Tag.

Deploying Demo
-----

If you want to deploy the demo site and play around with it, I recommend (since Twig templates are using relative path(s))
setting up a sub-domain that points to www/ directory of this package.

Register your application at http://instagram.com/developer (setting up proper callback/site URI's) and open up
index.php file to setup your client identification/secret key and redirect URI.

I'm using .htaccess, so if you are not using Apache I'm sure you can figure out how to forward requests to index.php.

About
-----

See: http://instagram.com/developer/
