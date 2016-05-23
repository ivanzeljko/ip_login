CONTENTS
--------

 * Introduction
 * Dependencies
 * Configuration
 * How the module works

INTRODUCTION
------------

The IP login module allows user to be automatically logged in based on their IP address.

DEPENDENCIES
------------

The module depends on field_ipaddress. This module contains a special IP address field that can handle IP spans in a way
that allows fast querying.

CONFIGURATION
-------------

 * Enable module
 * Create a user field of type field_ipaddress and with the field name "field_ipaddress".

HOW THE MODULE WORKS
--------------------

Doing login by IP can be tricky and may cause unwanted effects. To avoid slowing down the site by repeated login requests
and logings when access from robots, the module takes a slightly difference approach:

Login is attempted once by a javascript ajax request. Whether succesfull or not, the script will only do this once pr.
browser session. This way we take away the heavy lifting from the site. If login is succesful, the script will just reload the
current page and user is logged in.