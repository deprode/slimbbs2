<?php

namespace App\Model;

class Model
{
    public function __get($key)
    {
        if (property_exists(static::class, $key)) {
            return $this->$key;
        }
        return null;
    }

    public function __set($key, $value)
    {
        if (property_exists(static::class, $key)) {
            $this->$key = $value;
        }
    }

    public function __isset($name)
    {
        return property_exists(static::class, $name);
    }
}