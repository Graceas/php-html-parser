<?php
/**
 * Created by PhpStorm.
 * User: gorelov
 * Date: 13.10.19
 * Time: 18:19
 */


namespace HtmlParser\Exception;

/**
 * Class EmptyInstructionsXPatchMapperException
 * @package HtmlParser\Exception
 */
class EmptyInstructionsXPatchMapperException extends XPathMapperException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('Instructions is empty');
    }
}
