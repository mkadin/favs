Location Favorites
==================

About
=====
A simple RESTful API for working with favorite locations along with a single
page application to manipulate the data. The back end is written in PHP using
the Silex microframework.  The front end utilizes backbone.js for structure.
Also integrates with the Google Maps API (v3).

Reviewing this Code?
====================
If you're reviewing this application, the majority of the code written is...
  -In the app/ directory. These PHP files represent the Silex-based RESTful API.
  -The js/app.js file.  This file contains all of the application's custom JS.

Installation
============
1) Copy sample.config.php to config.php.

2) Edit the new config.php with your database credentials and Google Maps API
key.

3) Import the database.sql to set up the table structure.

4) Install Silex.  The easiest way to set this up is with composer.
(http://silex.sensiolabs.org/download)