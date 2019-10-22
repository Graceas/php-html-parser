<?php
/**
 * Created by PhpStorm.
 * User: gorelov
 * Date: 13.10.19
 * Time: 18:19
 */


namespace HtmlParser\Exception;

/**
 * Class MethodNotFoundXPatchMapperException
 * @package HtmlParser\Exception
 */
class MethodNotFoundXPatchMapperException extends XPathMapperException
{
    /**
     * @param array  $instruction
     * @param string $method
     * @param string $class
     */
    public function __construct($instruction, $method, $class)
    {
        parent::__construct(sprintf('Method "%s" not found for call "%s" in the class "%s"', $method, $instruction['instruction'], $class));
    }
}
