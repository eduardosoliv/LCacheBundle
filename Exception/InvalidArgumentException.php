<?php

/*
 * This file is a part of the PHP Local Cache Bundle.
 *
 * (c) 2013 Eduardo Oliveira
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ESO\LCacheBundle\Exception;

/**
 * InvalidArgumentException.
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * Require value is scalar.
     *
     * @param mixed          $value     Value.
     * @param string         $callee    Method name of the caller.
     * @param integer|string $parameter Parameter position/name.
     *
     * @throws InvalidArgumentException
     */
    public static function requireScalar($value, $callee, $parameter)
    {
        if (!is_scalar($value)) {
            throw new static(
                sprintf(
                    '%s() expects parameter "%s" to be scalar, %s given.',
                    $callee,
                    $parameter,
                    gettype($value)
                )
            );
        }
    }

    /**
     * If value is not null will be verified if is a positive integer.
     *
     * @param mixed          $value     Value.
     * @param string         $callee    Method name of the caller.
     * @param integer|string $parameter Parameter position/name.
     *
     * @throws InvalidArgumentException
     */
    public static function optionalPositiveInteger($value, $callee, $parameter)
    {
        if ($value !== null && (!is_int($value) || $value < 1)) {

            $valueType = gettype($value);
            $type = ($valueType === 'integer') ? 'zero or negative integer' : $valueType;

            throw new static(
                sprintf(
                    '%s() expects parameter "%s" to be null or positive integer, %s given.',
                    $callee,
                    $parameter,
                    $type
                )
            );
        }
    }
}
