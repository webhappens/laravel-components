<?php

namespace WebHappens\Components;

trait HasClasses
{
    protected $classes = [];

    /**
     * @param string|iterable $class
     */
    public function addClass($class)
    {
        if (is_null($class)) {
            return $this;
        }

        if (is_string($class)) {
            $class = explode(' ', $class);
        }

        $this->classes = array_unique(
            array_merge($this->classes, $class)
        );

        return $this;
    }

    /**
     * @param string|iterable $class
     */
    public function removeClass($class)
    {
        if (is_string($class)) {
            $class = explode(' ', $class);
        }

        foreach ($class as $classToRemove) {
            if (($key = array_search($classToRemove, $this->classes)) !== false) {
                unset($this->classes[$key]);
            }
        }

        return $this;
    }

    public function getClasses()
    {
        return $this->getClassesProperty($this->classes);
    }

    protected function getClassesProperty($classes): string
    {
        return implode(' ', $classes);
    }
}
