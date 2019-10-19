<?php
/**
 * Created by PhpStorm.
 * User: gorelov
 * Date: 05.10.19
 * Time: 15:33
 */

namespace HtmlParser;

/**
 * Class HtmlHelper
 * @package HtmlParser
 */
class HtmlHelper
{
    /**
     * @param string $content
     * @param string $encoding
     *
     * @return \DomXPath|null
     */
    public static function getDomByContent(&$content, $encoding = 'UTF-8')
    {
        $dom = new \DOMDocument('1.0', $encoding);
        if ($encoding === 'UTF-8') {
            $content = mb_convert_encoding($content, 'HTML-ENTITIES', $encoding);
        }

        if (empty($content)) {
            return null;
        }

        try {
            libxml_use_internal_errors(true);
            $dom->loadHTML($content);
            libxml_clear_errors();

            return new \DomXPath($dom);
        } catch (\Exception $e) {
            unset($e);
        }

        return null;
    }

    /**
     * @param \DOMNode $node
     *
     * @return array
     */
    public static function getAttributesByNode(\DOMNode $node)
    {
        $attributes = array();
        foreach ($node->attributes as $attr) {
            $attributes[$attr->nodeName] = $attr->nodeValue;
        }

        return $attributes;
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    public static function convertAttributesToString(array $attributes)
    {
        $values = array();
        foreach ($attributes as $key => $value) {
            $values[] = $key.'="'.$value.'"';
        }

        return implode(', ', $values);
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    public static function makeSelector(array $attributes)
    {
        if (isset($attributes['id'])) {
            return "@id='".$attributes['id']."'";
        } elseif (isset($attributes['class'])) {
            return "@class='".$attributes['class']."'";
        } else {
            return "unknown";
        }
    }

    /**
     * @param \DOMElement $domElement
     * @param int         $depth
     * @param string      $path
     */
    public static function getChildNodes($domElement, $depth = 1, $path = '')
    {
        foreach ($domElement->childNodes as $child) {
            /** @var \DOMElement $child */
            if ($child->nodeType == XML_TEXT_NODE) {
                $text = trim(str_replace(array("\t", "\n", "\r", "\0", "\x0B"), "", $child->nodeValue));
                if (empty($text)) {
                    continue;
                }
                echo str_repeat(' ', $depth * 4).'text['.$text.']{'.$path.'/text()'.'}'.PHP_EOL;
            } else {
                $attributes = static::getAttributesByNode($child);
                $thisNode = $child->tagName.'['.static::makeSelector($attributes).']';
                echo str_repeat(' ', $depth * 4).$child->tagName.'['.static::convertAttributesToString($attributes).']{'.$path.'/'.$thisNode.'}'.PHP_EOL;
                static::getChildNodes($child, $depth + 1, $path.'/'.$thisNode);
            }
        }
    }

    /**
     * @param string $object
     * @param array  $options
     *
     * @return string
     */
    public static function clearString($object, $options)
    {
        if (!in_array('NOT_REMOVE_BREAK', $options)) {
            $object = str_replace("\n", " ", $object);
        }

        if (!in_array('NOT_REMOVE_DOUBLE_SPACE', $options)) {
            $object = preg_replace('/\s\s+/', ' ', $object);
        }

        if (!in_array('NOT_TRIM', $options)) {
            $object = trim($object);
        }

        if (!in_array('NOT_HTML_ENTITIES', $options)) {
            $object = htmlentities($object, ENT_IGNORE, 'UTF-8');
            $object = str_replace('&nbsp;', '', $object);
        }

        return $object;
    }

    /**
     * @param string $string Price string
     *
     * @return float
     */
    public static function priceToFloat($string)
    {
        $originalString = $string;

        // convert "," to "."
        $string = str_replace(',', '.', $string);

        // remove everything except numbers and dot "."
        $string = preg_replace("/[^0-9\.]/", "", $string);

        // remove all seperators from first part and keep the end
        $string = str_replace('.', '', substr($string, 0, -3)).substr($string, -3);

        // return float
        return ((float) $string) * (strpos($originalString, '-') !== false ? -1 : 1);
    }
}
