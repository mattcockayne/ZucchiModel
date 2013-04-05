<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'zucchimodel.modelmanager' => function ($sm) {
                $zendDb = $sm->get('Zend\Db\Adapter\Adapter');
                $adapter = new \ZucchiModel\Adapter\ZendDb($zendDb);
                $manager = new \ZucchiModel\ModelManager($adapter);

                return $manager;
            },
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
        'aliases' => array(
            'modelmanager' => 'zucchimodel.modelmanager',
        )
    ),
);
