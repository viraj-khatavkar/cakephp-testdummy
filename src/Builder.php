<?php

namespace TestDummy;

use Cake\ORM\TableRegistry;
use Closure;
use Faker\Generator as Faker;
use InvalidArgumentException;

class Builder
{
    /**
     * The model definitions in the container.
     *
     * @var array
     */
    protected $definitions;

    /**
     * The model being built.
     *
     * @var string
     */
    protected $class;

    /**
     * The name of the model being built.
     *
     * @var string
     */
    protected $name = 'default';

    /**
     * The model states.
     *
     * @var array
     */
    protected $states;

    /**
     * The states to apply.
     *
     * @var array
     */
    protected $activeStates = [];

    /**
     * The Faker instance for the builder.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * The number of models to build.
     *
     * @var int|null
     */
    protected $amount = null;

    /**
     * Create an new builder instance.
     *
     * @param  string $class
     * @param  string $name
     * @param  array $definitions
     * @param  array $states
     * @param  \Faker\Generator $faker
     *
     */
    public function __construct($class, $name, array $definitions, array $states, Faker $faker)
    {
        $this->name = $name;
        $this->class = $class;
        $this->faker = $faker;
        $this->states = $states;
        $this->definitions = $definitions;
    }

    /**
     * Set the amount of models you wish to create / make.
     *
     * @param  int $amount
     *
     * @return $this
     */
    public function times($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Set the states to be applied to the model.
     *
     * @param  array|string $states
     *
     * @return $this
     */
    public function states($states)
    {
        $this->activeStates = is_array($states) ? $states : func_get_args();

        return $this;
    }

    /**
     * Build and persist a named entity.
     *
     * @param  array $overrides
     *
     * @return mixed
     */
    public function create(array $overrides = [])
    {
        if ($this->amount === null) {
            return $this->persist($overrides);
        }

        for ($i = 0; $i < $this->amount; $i++) {
            $entities[] = $this->persist($overrides);
        }

        return collection($entities);
    }

    /**
     * Persist the entity and any relationships.
     *
     * @param  array $attributes
     *
     * @return mixed
     */
    protected function persist(array $attributes = [])
    {
        $attributes = $this->getAttributes($attributes);

        $model = TableRegistry::get($this->class);
        $entity = $model->newEntity($attributes, [
            'validate'         => false,
            'accessibleFields' => ['*' => true],
        ]);

        $model->save($entity, ['tableFactory' => true]);

        return $entity;
    }

    /**
     * Get a raw attributes array for the model.
     *
     * @param  array  $attributes
     * @return mixed
     */
    protected function getRawAttributes(array $attributes = [])
    {
        $definition = call_user_func(
            $this->definitions[$this->class][$this->name],
            $this->faker, $attributes
        );

        return $this->callClosureAttributes(
            array_merge($this->applyStates($definition, $attributes), $attributes)
        );
    }

    /**
     * Make an instance of the model with the given attributes.
     *
     * @param  array $attributes
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getAttributes(array $attributes = [])
    {
        if (!isset($this->definitions[$this->class][$this->name])) {
            throw new InvalidArgumentException("Unable to locate factory with name [{$this->name}] [{$this->class}].");
        }

        return $this->getRawAttributes($attributes);
    }

    /**
     * Apply the active states to the model definition array.
     *
     * @param  array $definition
     * @param  array $attributes
     *
     * @return array
     */
    protected function applyStates(array $definition, array $attributes = [])
    {
        foreach ($this->activeStates as $state) {
            if (!isset($this->states[$this->class][$state])) {
                throw new InvalidArgumentException("Unable to locate [{$state}] state for [{$this->class}].");
            }

            $definition = array_merge($definition, call_user_func(
                $this->states[$this->class][$state],
                $this->faker, $attributes
            ));
        }

        return $definition;
    }

    /**
     * Evaluate any Closure attributes on the attribute array.
     *
     * @param  array $attributes
     *
     * @return array
     */
    protected function callClosureAttributes(array $attributes)
    {
        foreach ($attributes as &$attribute) {
            $attribute = $attribute instanceof Closure
                ? $attribute($attributes) : $attribute;
        }

        return $attributes;
    }
}
