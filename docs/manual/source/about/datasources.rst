Data-Sources
============

When we say data-source what we are referring to is some form of data storage solution be it SQL, NoSQL, CSV or
something else. Ideally we want to be able to allow you to interface with any form of data-source and be able to map
its contents to objects. Wherever possible we have tried to do this by using pre-existing database abstraction tools but
ZucchiModel is not restricted and provided an appropriate adapter exists or can be written then it should be able to
interface with anything

Available Adapters
------------------

Zend\Db
~~~~~~~

Our first adapter for ZucchiModel allows you to interface with all of the SQL databases supported by Zends Database component.

Adapters in Development
-----------------------

Mongo
~~~~~

We tend to use Mongo quite a lot recently in our projects so have an adapter for Mongo scheduled.