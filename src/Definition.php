<?php

namespace TestDummy;

use Faker\Factory;
use Symfony\Component\Finder\Finder;

class Definition
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * The model definitions in the container.
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * The registered model states.
     *
     * @var array
     */
    protected $states = [];

    /**
     * The Faker instance for the builder.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * Define a class with a given set of attributes.
     *
     * @param  string $model
     * @param  callable $attributes
     * @param  string $name
     *
     * @return $this
     */
    public function define($model, callable $attributes, $name = 'default')
    {
        $this->definitions[$model][$name] = $attributes;

        return $this;
    }

    /**
     * Define a state with a given set of attributes.
     *
     * @param  string $class
     * @param  string $state
     * @param  callable $attributes
     *
     * @return $this
     */
    public function state($class, $state, callable $attributes)
    {
        $this->states[$class][$state] = $attributes;

        return $this;
    }

    /**
     * Load factories from path.
     *
     * @param  string $path
     *
     * @return $this
     */
    public function load($path)
    {
        $factory = $this;

        if (is_dir($path)) {
            foreach (Finder::create()->files()->name('*.php')->in($path) as $file) {
                require $file->getRealPath();
            }
        }

        return $factory;
    }

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Create a builder for the given model.
     *
     * @param  string $class
     * @param  string $name
     *
     * @return \TestDummy\Builder
     */
    public function of($class, $name = 'default')
    {
        return new Builder($class, $name, $this->definitions, $this->states, $this->faker);
    }
}