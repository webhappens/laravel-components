<?php

namespace WebHappens\Components;

use InvalidArgumentException;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Arrayable;

class Attributes implements Htmlable, Arrayable
{
    use HasClasses;

    protected $attributes = [];

    public function setAttributes($attributes)
    {
        foreach ($attributes as $attribute => $value) {
            if ($attribute === 'class') {
                $this->addClass($value);

                continue;
            }

            if (is_int($attribute)) {
                $attribute = $value;
                $value = '';
            }

            $this->setAttribute($attribute, (string) $value);
        }

        return $this;
    }

    public function setAttribute($attribute, $value = null)
    {
        if ($attribute === 'class') {
            $this->addClass($value);

            return $this;
        }

        $this->attributes[$attribute] = $value;

        return $this;
    }

    public function forgetAttribute($attribute)
    {
        if ($attribute === 'class') {
            $this->classes = [];

            return $this;
        }

        if (isset($this->attributes[$attribute])) {
            unset($this->attributes[$attribute]);
        }

        return $this;
    }

    public function getAttribute($attribute, $fallback = null)
    {
        if ($attribute === 'class') {
            return implode(' ', $this->classes);
        }

        return $this->attributes[$attribute] ?? $fallback;
    }

    public function hasAttribute(string $attribute): bool
    {
        return array_key_exists($attribute, $this->attributes);
    }

    public function id($id)
    {
        return $this->setAttribute('id', $id);
    }

    public function ariaLabel($label)
    {
        return $this->setAttribute('aria-label', $label);
    }

    public function ariaLabelledBy($id)
    {
        return $this->setAttribute('aria-labelledby', $id);
    }

    public function ariaHidden(bool $bool)
    {
        return $this->setAttribute('aria-hidden', $bool ? 'true' : 'false');
    }

    public function ariaCurrent($value = true)
    {
        $validValues = ['page', 'step', 'location', 'date', 'time'];

        if ( ! (in_array($value, $validValues) || is_bool($value))) {
            throw new InvalidArgumentException("`$value` is invalid for aria-current, please use: a boolean or " . implode(", ", $validValues));
        }

        if ($value === false) {
            return $this->forgetAttribute('aria-current');
        }

        if ($value === true) {
            return $this->setAttribute('aria-current', 'true');
        }

        return $this->setAttribute('aria-current', $value);
    }

    public function isEmpty()
    {
        return empty($this->attributes) && empty($this->classes);
    }

    public function toArray()
    {
        if (empty($this->classes)) {
            return $this->attributes;
        }

        return array_merge(['class' => $this->getClasses()], $this->attributes);
    }

    public function render()
    {
        if ($this->isEmpty()) {
            return '';
        }

        $attributeStrings = [];

        foreach ($this->toArray() as $attribute => $value) {
            if ($value === '') {
                $attributeStrings[] = $attribute;

                continue;
            }

            $value = htmlentities($value, ENT_QUOTES, 'UTF-8', false);

            $attributeStrings[] = "{$attribute}=\"{$value}\"";
        }

        return implode(' ', $attributeStrings);
    }

    public function toHtml()
    {
        return $this->render();
    }

    public function __toString()
    {
        return $this->render();
    }
}
