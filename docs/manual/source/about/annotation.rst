Annotations
===========

Having been using Annotations for quite some time and with Zends adoption of the Doctrine Annotation reader it made sense to re-use this.

As such we have a hard dependency on the Zend\Code & Doctrine\Common\Annotation components for all our annotation reading.

In the same way that Doctrine & Zend Use annotations you need to make sure to import the Annotations you are going to use into the file with an appropriate use statement. We tend to use:

    use ZucchiModel\Annotation as Model


Available Annotations
---------------------

@Model\Field(string)
~~~~~~~~~~~~~~~~~~~~
The field annotation should only be used for defining a properties type. This will then be used in casting a value to/from when mapping data. This Annotation should only be populated with a string.

Available datatypes are

* string - this will cast the data to a string
* integer - this will cast to an integer
* binary - currently no casting will take place for binary data
* boolean - this will cast to a boolean
* float - this will cast to a float
* date - this will cast to a DateTime object in the model and a date on the datasource
* time - this will cast to a DateTime object in the model and a time on the datasource
* datetime  - this will cast to a DateTime object in the model and a datetime on the datasource
* json_array - this will cast to an array in the model and a json string on the datasource
* json_object - this will cast to an object in the model and a json string on the datasource

@Model\Relationship(array)
~~~~~~~~~~~~~~~~~~~~~~~~~~
This allows you to define a relationship to another model and requires you to define very specific properties for mapping the relationship. Relationships are always defined in a single direction. This relationship should be defined using a json data structure

Required properties for defining a relationship are

* name - A common name to refer to the relationship as
* model - The model that the relationship maps to
* type - The type of relation ship, Can be on of 'ToOne', 'ToMany' and 'ManyToMany'
* mappedKey - the name of the field/column that contains the local reference/id
* mappedBy - the name of the field/column that contains the remote reference/id

ToOne
^^^^^
Assuming we are defining a relationship from a User model to a UserAddress model we would write a relationship to look something like::

    @Model\Relationship({
        "name" : "Address",
        "model" : "UserAddress",
        "type" : "toOne",
        "mappedKey" : "id",
        "mappedBy" : "User_id"
    })

This mapping assumes that the User data in the data-source has a field/column called id and the UserAddress data in the data-source has a field/column called User_id.

.. N.B. ZucchiModel expects the data-source to be properly formed. in SQL this means that you MUST have the appropriate foreign keys that match the defined relationship

ToMany
^^^^^^
'name','model','type','mappedKey','mappedBy'

ManyToMany
^^^^^^^^^^
'name','model','type','mappedKey','mappedBy','foreignKey','foreignBy','referencedBy'


@Model\Target
~~~~~~~~~~~~~
A target can be a number of things i.e a table in SQL, a Collection in NoSQL.

