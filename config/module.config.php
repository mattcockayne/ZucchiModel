<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'zucchimodel.modelmanager' => function ($sm) {
                $manager = new ZucchiModel\ModelManager();
                // set adapter

                // add additional listeners

                //etc


                return $manager;
            },
        ),
    ),
);