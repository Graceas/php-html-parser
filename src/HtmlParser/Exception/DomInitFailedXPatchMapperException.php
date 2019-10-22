<?php
/**
 * Created by PhpStorm.
 * User: gorelov
 * Date: 13.10.19
 * Time: 18:19
 */

namespace HtmlParser\Exception;

/**
 * Class DomInitFailedXPatchMapperException
 * @package HtmlParser\Exception
 */
class DomInitFailedXPatchMapperException extends XPathMapperException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('DOM Init Failed!');
    }
}
