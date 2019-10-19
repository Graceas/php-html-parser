<?php
/**
 * Created by PhpStorm.
 * User: gorelov
 * Date: 13.10.19
 * Time: 18:19
 */


namespace HtmlParser\Exception;

/**
 * Class MapNotFoundXPatchMapperException
 * @package HtmlParser\Exception
 */
class MapNotFoundXPatchMapperException extends XPathMapperException
{
    /**
     * @param string $mapPath
     */
    public function __construct($mapPath)
    {
        parent::__construct(sprintf('Map not found for path "%s"', $mapPath));
    }
}
