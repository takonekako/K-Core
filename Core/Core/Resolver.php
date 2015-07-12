<?php
namespace Core\Core;

use Core\Container\Container;
use Core\Routing\Interfaces\ResolverInterface;
use Core\Container\Interfaces\ContainerAwareInterface;

/**
 * Class Resolver
 * @package Core\Core
 */
class Resolver implements ResolverInterface, ContainerAwareInterface
{
    /**
     * @var Container|null
     */
    private $app = null;

    /**
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @return Container
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param Container $app
     * @return self
     */
    public function setApp(Container $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Execute action
     *
     * @param string
     * @return object
     */
    function resolve($classname)
    {
        // Resolve from container if possible
        if ($this->app->has($classname)) {
            return $this->app->get($classname);
        }

        // Add namespace prefix
        $class = '\\' . $classname;

        // Create class
        $object = new $class();

        // If class needs container inject it
        if ($object instanceof ContainerAwareInterface) {
            $object->setApp($this->app);
        }
        return $object;
    }
}