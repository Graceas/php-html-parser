<?php
/**
 * Created by PhpStorm.
 * User: gorelov
 * Date: 13.10.19
 * Time: 18:19
 */


namespace HtmlParser\Exception;

/**
 * Class PropertyNotFoundXPatchMapperException
 * @package HtmlParser\Exception
 */
class PropertyNotFoundXPatchMapperException extends XPathMapperException
{
    /**
     * @param string $property
     */
    public function __construct($property)
    {
        parent::__construct(sprintf('Property not found for call "%s"', $property));
    }
}
