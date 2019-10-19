<?php

/**
 * This file is part of the Phalcon Developer Tools.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Error;

/**
 * \Phalcon\Error\AppError
 *
 * @method int type()
 * @method string message()
 * @method string file()
 * @method string line()
 * @method \Exception|null exception()
 * @method bool isException()
 * @method bool isError()
 *
 * @package Phalcon\Error
 */
class AppError
{
    protected $attributes = [];

    /**
     * AppError constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $defaults = [
            'type'        => -1,
            'message'     => 'No error message',
            'file'        => '',
            'line'        => '',
            'exception'   => null,
            'isException' => false,
            'isError'     => false,
        ];

        $this->attributes = array_merge($defaults, $options);
    }

    /**
     * Magic method to retrieve the attributes.
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed|null
     */
    public function __call($method, $args)
    {
        $attributes = array_keys($this->attributes);

        return in_array($method, $attributes, true) ? $this->attributes[$method] : null;
    }
}
