<?php
/**
 * @link https://qiita.com/Hiraku/items/71e385b56dcaa37629fe
 */

namespace App\Model;

abstract class Enum
{
    private $scalar;

    public function __construct($value)
    {
        $ref = new \ReflectionObject($this);
        $consts = $ref->getConstants();
        if (!in_array($value, $consts, true)) {
            throw new \InvalidArgumentException;
        }

        $this->scalar = $value;
    }

    final public function value()
    {
        return $this->scalar;
    }

    final public function __toString()
    {
        return (string)$this->scalar;
    }
}