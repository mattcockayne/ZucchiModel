<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser;
use Zend\Code\Reflection\ClassReflection;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use ZucchiModel\Metadata;
use ZucchiModel\Annotation\MetadataListener;

/**
 * Object Manager for
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage ModelManager
 * @category
 */
class ModelManager implements EventManagerAwareInterface
{
    /**
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var AnnotationManager;
     */
    protected $annotationManager;

    /**
     * Mapping data for loaded models
     *
     * @var array
     */
    protected $modelMetadata = array();

    /**
     * Collection of know Annotations related to ModelManager
     *
     * @var array
     */
    protected $registeredAnnotations = array(
        'ZucchiModel\Annotation\Field',
        'ZucchiModel\Annotation\Relationship',
    );

    public function __construct(AdapterInterface $adapter = null)
    {
        if ($adapter) $this->setAdapter($adapter);
    }

    /**
     * Get Zend Db Adapter
     *
     * @return \Zend\Db\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set Zend Db Adapter
     *
     * @param \Zend\Db\Adapter\AdapterInterface $adapter
     * @return ModelManager
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Get event manager
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * Set event manager instance
     *
     * @param  EventManagerInterface $events
     * @return AnnotationBuilder
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_class($this),
        ));
        $metadataListener = new MetadataListener();
        $metadataListener->attach($events);
        $this->eventManager = $events;
        return $this;
    }

    /**
     * Get Annotation Manager
     *
     * @return \Zend\Code\Annotation\AnnotationManager
     */
    public function getAnnotationManager()
    {
        if (!$this->annotationManager) {
            $this->annotationManager = new AnnotationManager();
            $parser = new Parser\DoctrineAnnotationParser();
            foreach ($this->registeredAnnotations as $annotation) {
                $parser->registerAnnotation($annotation);
            }
            $this->annotationManager->attach($parser);
        }
        return $this->annotationManager;
    }

    /**
     * Set Annotation Manager
     *
     * @param \Zend\Code\Annotation\AnnotationManager $annotationManager
     */
    public function setAnnotationManager(AnnotationManager $annotationManager)
    {
        $this->annotationManager = $annotationManager;
    }

    /**
     * Get Metadata
     *
     * @param $class
     * @return mixed
     */
    public function getMetadata($class)
    {
        if (!array_key_exists($class, $this->modelMetadata)) {
            $reflection  = new ClassReflection($class);
            $am = $this->getAnnotationManager();
            $em = $this->getEventManager();

            $model = new Metadata\Model();
            $fields = new Metadata\Fields();

            if ($annotation = $reflection->getAnnotations($am)) {
                $event = new Event();
                $event->setName('configureModel');
                $event->setTarget($model);
                $event->setParam('annotation', $annotation);
                $em->trigger($event);
            }

            if ($properties = $reflection->getProperties()) {
                $event = new Event();
                $event->setName('configureModelFields');
                $event->setTarget($fields);

                foreach ($properties as $property) {
                    if ($annotation = $property->getAnnotations($am)) {
                        $event->setParam('property',$property->getName());
                        $event->setParam('annotation',$annotation);
                        $em->trigger($event);
                    }
                }
            }



            $this->modelMetadata[$class] = array(
                'model' => $model,
                'fields' => $fields,
            );
            var_dump($this->modelMetadata);
        }

        return $this->modelMetadata[$class];
    }

    /**
     * helper to find objects by criteria
     * @param $criteria
     * @return ObjectManager
     */
    public function find($criteria)
    {
        return $this;
    }

    /**
     * Get Relationships
     *
     * @param $model
     * @param $nameOfRelationship
     */
    public function getRelationship($model, $nameOfRelationship)
    {
        if (isset($this->modelMetadata[get_class($model)]['relationships'][$nameOfRelationship])) {
            // lookup and return relationship;

        }
    }

    /**
     * Release stored model
     *
     * @param $model
     * @param bool $releaseRelationships
     */
    public function release($model, $releaseRelationships = true)
    {

    }
}

