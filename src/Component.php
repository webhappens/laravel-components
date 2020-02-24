<?php

namespace WebHappens\Components;

use ReflectionClass;
use JsonSerializable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use WebHappens\MagicProperties\MagicProperties;
use WebHappens\ConditionalMethods\ConditionalMethods;
use WebHappens\ConditionalMethods\NotConditionalMethod;

abstract class Component implements Htmlable, Arrayable, Jsonable, JsonSerializable
{
    use MagicProperties,
        ConditionalMethods {
            MagicProperties::__call as __call_magic_properties;
            ConditionalMethods::__call as __call_conditional;
    }

    protected $isVisible = true;

    public static function make(...$args)
    {
        return new static(...$args);
    }

    public function __construct($data = [])
    {
        $this->with($data);

        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    public function with($data) {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $this->setPropertyValues($data);
    }

    public function show()
    {
        $this->isVisible = true;

        return $this;
    }

    public function hide()
    {
        $this->isVisible = false;

        return $this;
    }

    protected function getViewData()
    {
        return $this->getPropertyValues();
    }

    public function toArray()
    {
        return collect($this->getViewData())->toArray();
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function render()
    {
        return view()->first($this->getViewNames(), $this->getViewData())->render();
    }

    public function toHtml()
    {
        if ( ! $this->isVisible()) {
            return '';
        }

        return $this->render();
    }

    public function dump()
    {
        return dump($this);
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    protected function setAttributes($attributes)
    {
        return $this->attributes->setAttributes($attributes);
    }

    protected function getViewNames()
    {
        $classes = collect();
        $class = new ReflectionClass($this);
        do {
            $classes->push($class->getName());
        } while ($class = $class->getParentClass());

        return $classes
            ->map(function($class) {
                return 'components::' . ltrim(str_replace(['App\\Components', '\\'], ['', '.'], $class), '.');
            })
            ->toArray();
    }

    public function __call($method, $arguments)
    {
        try {
            return $this->__call_conditional($method, $arguments);
        } catch (NotConditionalMethod $e) {}

        try {
            return $this->__call_magic_properties($method, $arguments);
        } catch (\BadMethodCallException $e) {}

        throw new \BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }
}
