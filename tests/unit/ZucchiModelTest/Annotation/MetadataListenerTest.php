<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModelTest\Annotation;

use Codeception\Util\Stub;

use Zend\Code\Reflection\ClassReflection;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;
use Zend\EventManager\Event;

use ZucchiModel\ModelManager;
use ZucchiModel\Adapter\ZendDb;
use ZucchiModel\Annotation\MetadataListener;
use ZucchiModel\Metadata;

/**
 * MetadataListenerTest
 *
 * Tests on the MetadataListener Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage Annotation
 * @category
 */
class MetadataListenerTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    /**
     * Actually connection via Zend Db
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $zendDbAdapter;

    /**
     * ZucchiModel wrapper for Zend Db adapter
     *
     * @var \ZucchiModel\Adapter\ZendDb
     */
    protected $adapter;

    /**
     * ModelManager
     *
     * @var \ZucchiModel\ModelManager
     */
    protected $modelManager;

    /**
     * Setup Test
     */
    protected function _before()
    {
        $this->zendDbAdapter = new ZendDbAdapter(array(
            'driver' => 'Mysqli',
            'database' => 'moduledev',
            'username' => 'root',
            'password' => 'password',
            'port' => '3306',
            'host' => '127.0.0.1',
            'charset' => 'UTF8',
            'profiler' => true
        ));
        $this->adapter = new ZendDb($this->zendDbAdapter);
        $this->modelManager = new ModelManager($this->adapter);
    }

    /**
     * Tear down Test
     */
    protected function _after()
    {
    }

    /**
     * Check MetadataListener Events can be attached and detached from Event
     * Manager.
     */
    public function testDetachMetadataListenerWithValidEventManager()
    {
        $em = $this->modelManager->getEventManager();
        $metadataListener = new MetadataListener();
        $metadataListener->attach($em);
        $metadataListener->detach($em);
    }

    /**
     * Check attach throws an \ErrorException when supplied with invalid
     * param.
     *
     * @expectedException \ErrorException
     */
    public function testAttachWithInvalidEventManagerParam()
    {
        $metadataListener = new MetadataListener();
        $metadataListener->attach('string monkey');
    }

    /**
     * Check detach throws an \ErrorException when supplied with invalid
     * param.
     *
     * @expectedException \ErrorException
     */
    public function testDetachWithInvalidEventManagerParam()
    {
        $metadataListener = new MetadataListener();
        $metadataListener->detach('string monkey');
    }

    /**
     * Check prepareModelMetadata with valid Event.
     */
    public function testPrepareModelMetadataWithValidEventParam()
    {
        $md = new Metadata\MetaDataContainer();

        // Get the Model's Annotations
        $reflection  = new ClassReflection('\ZucchiModelTest\Annotation\TestAsset\User');
        $am = $this->modelManager->getAnnotationManager();
        $em = $this->modelManager->getEventManager();

        // Find all the Model Metadata
        if ($annotations = $reflection->getAnnotations($am)) {
            $event = new Event();
            $event->setName('prepareModelMetadata');
            $event->setTarget($annotations);
            $event->setParam('model', $md->getModel());
            $event->setParam('relationships', $md->getRelationships());
            $em->trigger($event);
        }
    }

    /**
     * Check prepareModelMetadata throws \UnexpectedValueException when
     * supplied with invalid Annotations in Event.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testPrepareModelMetadataWithInvalidAnnotations()
    {
        $md = new Metadata\MetaDataContainer();

        // Get the Model's Annotations
        $reflection  = new ClassReflection('\ZucchiModelTest\Annotation\TestAsset\User');
        $am = $this->modelManager->getAnnotationManager();
        $em = $this->modelManager->getEventManager();

        // Find all the Model Metadata
        if ($annotations = $reflection->getAnnotations($am)) {
            $event = new Event();
            $event->setName('prepareModelMetadata');
            $event->setTarget('string monkey');
            $event->setParam('model', $md->getModel());
            $event->setParam('relationships', $md->getRelationships());
            $em->trigger($event);
        }
    }

    /**
     * Check prepareModelMetadata throws \UnexpectedValueException when
     * supplied with invalid Model in Event.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testPrepareModelMetadataWithInvalidModel()
    {
        $md = new Metadata\MetaDataContainer();

        // Get the Model's Annotations
        $reflection  = new ClassReflection('\ZucchiModelTest\Annotation\TestAsset\User');
        $am = $this->modelManager->getAnnotationManager();
        $em = $this->modelManager->getEventManager();

        // Find all the Model Metadata
        if ($annotations = $reflection->getAnnotations($am)) {
            $event = new Event();
            $event->setName('prepareModelMetadata');
            $event->setTarget($annotations);
            $event->setParam('model', 'string monkey');
            $event->setParam('relationships', $md->getRelationships());
            $em->trigger($event);
        }
    }

    /**
     * Check prepareModelMetadata throws \UnexpectedValueException when
     * supplied with invalid Relationships in Event.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testPrepareModelMetadataWithInvalidRelationships()
    {
        $md = new Metadata\MetaDataContainer();

        // Get the Model's Annotations
        $reflection  = new ClassReflection('\ZucchiModelTest\Annotation\TestAsset\User');
        $am = $this->modelManager->getAnnotationManager();
        $em = $this->modelManager->getEventManager();

        // Find all the Model Metadata
        if ($annotations = $reflection->getAnnotations($am)) {
            $event = new Event();
            $event->setName('prepareModelMetadata');
            $event->setTarget($annotations);
            $event->setParam('model', $md->getModel());
            $event->setParam('relationships', 'string monkey');
            $em->trigger($event);
        }
    }

    /**
     * Check prepareModelMetadata throws an \ErrorException when supplied with invalid
     * param.
     *
     * @expectedException \ErrorException
     */
    public function testPrepareModelMetadataWithInvalidEventParam()
    {
        $metadataListener = new MetadataListener();
        $metadataListener->prepareModelMetadata('string monkey');
    }

    /**
     * Check prepareFieldMetadata with valid Event.
     */
    public function testPrepareFieldMetadataWithValidEventParam()
    {
        $md = new Metadata\MetaDataContainer();

        // Get the Model's Annotations
        $reflection  = new ClassReflection('\ZucchiModelTest\Annotation\TestAsset\User');
        $am = $this->modelManager->getAnnotationManager();
        $em = $this->modelManager->getEventManager();

        // Find all the Fields Metadata
        if ($properties = $reflection->getProperties()) {
            $event = new Event();
            $event->setName('prepareFieldMetadata');
            $event->setTarget($md->getFields());
            foreach ($properties as $property) {
                if ($annotation = $property->getAnnotations($am)) {
                    $event->setParam('property', $property->getName());
                    $event->setParam('annotation', $annotation);
                    $em->trigger($event);
                }
            }
        }
    }

    /**
     * Check prepareFieldMetadata throws \UnexpectedValueException when
     * supplied with invalid Fields in Event.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testPrepareFieldMetadataWithInvalidFields()
    {
        $md = new Metadata\MetaDataContainer();

        // Get the Model's Annotations
        $reflection  = new ClassReflection('\ZucchiModelTest\Annotation\TestAsset\User');
        $am = $this->modelManager->getAnnotationManager();
        $em = $this->modelManager->getEventManager();

        // Find all the Fields Metadata
        if ($properties = $reflection->getProperties()) {
            $event = new Event();
            $event->setName('prepareFieldMetadata');
            $event->setTarget('');
            foreach ($properties as $property) {
                if ($annotation = $property->getAnnotations($am)) {
                    $event->setParam('property', $property->getName());
                    $event->setParam('annotation', $annotation);
                    $em->trigger($event);
                }
            }
        }
    }

    /**
     * Check prepareFieldMetadata throws \UnexpectedValueException when
     * supplied with invalid Property in Event.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testPrepareFieldMetadataWithInvalidProperty()
    {
        $md = new Metadata\MetaDataContainer();

        // Get the Model's Annotations
        $reflection  = new ClassReflection('\ZucchiModelTest\Annotation\TestAsset\User');
        $am = $this->modelManager->getAnnotationManager();
        $em = $this->modelManager->getEventManager();

        // Find all the Fields Metadata
        if ($properties = $reflection->getProperties()) {
            $event = new Event();
            $event->setName('prepareFieldMetadata');
            $event->setTarget($md->getFields());
            foreach ($properties as $property) {
                if ($annotation = $property->getAnnotations($am)) {
                    $event->setParam('property', array('string', 'monkey'));
                    $event->setParam('annotation', $annotation);
                    $em->trigger($event);
                }
            }
        }
    }

    /**
     * Check prepareFieldMetadata throws \UnexpectedValueException when
     * supplied with empty Property in Event.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testPrepareFieldMetadataWithEmptyProperty()
    {
        $md = new Metadata\MetaDataContainer();

        // Get the Model's Annotations
        $reflection  = new ClassReflection('\ZucchiModelTest\Annotation\TestAsset\User');
        $am = $this->modelManager->getAnnotationManager();
        $em = $this->modelManager->getEventManager();

        // Find all the Fields Metadata
        if ($properties = $reflection->getProperties()) {
            $event = new Event();
            $event->setName('prepareFieldMetadata');
            $event->setTarget($md->getFields());
            foreach ($properties as $property) {
                if ($annotation = $property->getAnnotations($am)) {
                    $event->setParam('property', '');
                    $event->setParam('annotation', $annotation);
                    $em->trigger($event);
                }
            }
        }
    }

    /**
     * Check prepareFieldMetadata throws \UnexpectedValueException when
     * supplied with invalid Annotation in Event.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testPrepareFieldMetadataWithInvalidAnnotation()
    {
        $md = new Metadata\MetaDataContainer();

        // Get the Model's Annotations
        $reflection  = new ClassReflection('\ZucchiModelTest\Annotation\TestAsset\User');
        $am = $this->modelManager->getAnnotationManager();
        $em = $this->modelManager->getEventManager();

        // Find all the Fields Metadata
        if ($properties = $reflection->getProperties()) {
            $event = new Event();
            $event->setName('prepareFieldMetadata');
            $event->setTarget($md->getFields());
            foreach ($properties as $property) {
                if ($annotation = $property->getAnnotations($am)) {
                    $event->setParam('property', $property->getName());
                    $event->setParam('annotation', 'string monkey');
                    $em->trigger($event);
                }
            }
        }
    }

    /**
     * Check prepareFieldMetadata throws an \ErrorException when supplied with invalid
     * param.
     *
     * @expectedException \ErrorException
     */
    public function testPrepareFieldMetadataWithInvalidEventParam()
    {
        $metadataListener = new MetadataListener();
        $metadataListener->prepareFieldMetadata('string monkey');
    }
}