Methodology
===========

ZucchiModel came about from our dissatisfaction when using a number of different off the shelf ORM solutions such as Doctrine & Propel.

We consistently found that we were hitting our head against the wall when using these tools in terms of performance. Along with that we felt that there we could create a tool that would fulfil our needs better.

Simplicity
----------

Our top priority was on trying to reduce the complexity of an ORM to just handling the mapping of data from a data-source such as a DB to objects and back again. So we came up the following requirements.

#. Must focus on both speed and memory consumption for performance without risking functionality. Only uses caching to enhance already good performance rather than rely on caching to provide performance.
#. Avoid self or cyclic referencing for models.
#. It must not manage or maintain the data-source in any way shape or form. We strongly believe that its better for the the data-source to be properly managed and maintained by someone qualified to do so.
#. Reduce the amount of magic. Some Tools appear to do things by magic. We want to avoid this where possible and be as explicit as we can without being overly verbose.
#. Utilise Annotations for defining metadata for models. Annotations have proved to be a very successful mechanism for conveying metadata.
#. No inheritance required for models. This means that ANY object can be mapped to/from provided it has been given the appropriate metadata.
#. Allow "compound" models. This means that you can populate an object from multiple targets (read as tables for SQL) provided the datasource (and adapter) provides for these relationships
#. Utilise an event manager to allow the easy injection of extra logic to the tool.
#. Make use of traits to apply specific behaviours to a model.
#. Allow the ability to create and implement adapters for different data-sources. i.e. Zend\Db,  Mongo, etc.
#. Make it easy to access the defined data-source adapters to allow for complex querying against a datasource.
#. Don't re-invent the wheel. If a tool exists that can help we need to use it. i.e. Doctrine Annotation, Zend EventManager, etc

