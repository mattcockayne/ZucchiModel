ZucchiModel
==============

Model management and lightweight ORM overlaying Zend\Db

Build Status
* Master: [![Build Status](https://secure.travis-ci.org/zucchi/ZucchiModel.png?branch=master)](http://travis-ci.org/zucchi/ZucchiModel)

Installation
------------

From the root of your ZF2 Skeleton Application run

    ./composer.phar require zucchi/model


To run tests with PHPStorm

	XDEBUG_CONFIG="idekey=phpstorm1" PHP_IDE_CONFIG="serverName=sandbox.creatingit.co.uk" vendor/bin/codecept run --coverage --html