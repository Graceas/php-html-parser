<?php
/**
 * Created by PhpStorm.
 * User: gorelov
 * Date: 13.10.19
 * Time: 18:13
 */

namespace HtmlParser;

use HtmlParser\Exception\DomInitFailedXPatchMapperException;
use HtmlParser\Exception\EmptyInstructionsXPatchMapperException;
use HtmlParser\Exception\MethodNotFoundXPatchMapperException;
use HtmlParser\Exception\PropertyNotFoundXPatchMapperException;

/**
 * Class HtmlParser
 * @package HtmlParser
 */
class HtmlParser
{
    /**
     * @param string $html
     * @param string $instructions
     *
     * @return array
     * @throws DomInitFailedXPatchMapperException
     * @throws EmptyInstructionsXPatchMapperException
     * @throws MethodNotFoundXPatchMapperException
     * @throws PropertyNotFoundXPatchMapperException
     */
    public function getValues($html, $instructions)
    {
        $instructions = $this->loadMapInstructions($instructions);
        $dom = HtmlHelper::getDomByContent($html);

        if (!$dom) {
            throw new DomInitFailedXPatchMapperException();
        }

        if (!$instructions) {
            throw new EmptyInstructionsXPatchMapperException();
        }

        return $this->processMapInstructions($dom, $instructions);
    }

    /**
     * @param string $instructionsContent
     *
     * @return array
     */
    private function loadMapInstructions($instructionsContent)
    {
        $content = explode(PHP_EOL, $instructionsContent);
        $instructions = array();

        foreach ($content as $line) {
            @list($name, $instruction) = explode(' => ', $line);

            if (!isset($name) || !isset($instruction)) {
                continue;
            }

            @list($instruction, $default, $options) = explode(' || ', $instruction);
            if (!isset($default) || $default === 'null') {
                $default = null;
            }

            if ($default == '\'\'') {
                $default = '';
            }

            $default = trim($default, '\'');

            $steps = explode(' -> ', $instruction);
            $calls = array();

            foreach ($steps as $step) {
                if ($step === 'current') {
                    continue;
                }
                preg_match('/(?P<method>.*)\(\'(?P<arguments>.*)\'\)$/', $step, $matches);
                $calls[] = array(
                    'method' => $matches['method'],
                    'arguments' => explode(' ; ', $matches['arguments']),
                );
            }
            $instructions[] = array(
                'name' => $name,
                'instruction' => $instruction,
                'default' => $default,
                'calls' => $calls,
                'options' => explode(',', $options),
            );
        }

        return $instructions;
    }

    /**
     * @param \DomXPath $dom
     * @param array     $instructions
     *
     * @return array
     * @throws PropertyNotFoundXPatchMapperException
     * @throws MethodNotFoundXPatchMapperException
     */
    private function processMapInstructions(\DomXPath $dom, array $instructions)
    {
        $results = array();
        foreach ($instructions as $instruction) {
            $object = $dom;

            // if loop
            if ($instruction['name'] === ';;') {
                return $this->processMapInstructionsLoop($dom, $instructions);
            }

            foreach ($instruction['calls'] as $call) {
                if (!$object) {
                    continue;
                }
                if ($call['method'] === '__get') {
                    if (!property_exists($object, $call['arguments'][0])) {
                        throw new PropertyNotFoundXPatchMapperException($call['arguments'][0]);
                    }
                    $object = $object->{$call['arguments'][0]};
                } else {
                    if (!method_exists($object, $call['method'])) {
                        throw new MethodNotFoundXPatchMapperException($instruction, $call['method'], @get_class($object));
                    }
                    $object = call_user_func_array(array($object, $call['method']), $call['arguments']);
                }
            }

            if (is_string($object)) {
                $object = HtmlHelper::clearString($object, $instruction['options']);
            }

            $results[$instruction['name']] = (!$object) ? $instruction['default'] : $object;
        }

        return $results;
    }

    /**
     * @param \DomXPath $dom
     * @param array     $instructions
     *
     * @return array
     * @throws MethodNotFoundXPatchMapperException
     * @throws PropertyNotFoundXPatchMapperException
     */
    private function processMapInstructionsLoop(\DomXPath $dom, array $instructions)
    {
        $results = array();
        $object = $dom;

        $objects = array();
        $loopInstruction = array_shift($instructions);

        foreach ($loopInstruction['calls'] as $call) {
            if (!method_exists($object, $call['method'])) {
                throw new MethodNotFoundXPatchMapperException($loopInstruction, $call['method'], @get_class($object));
            }
            $objects = call_user_func_array(array($object, $call['method']), $call['arguments']);
        }

        foreach ($objects as $element) {
            $result = array();

            foreach ($instructions as $instruction) {
                $object = $element;

                foreach ($instruction['calls'] as $call) {
                    if (!$object) {
                        continue;
                    }
                    if ($call['method'] === '__get') {
                        if (!property_exists($object, $call['arguments'][0])) {
                            throw new PropertyNotFoundXPatchMapperException($call['arguments'][0]);
                        }
                        $object = $object->{$call['arguments'][0]};
                    } else {
                        if ($call['method'] === 'query') {
                            $call['arguments'][] = $element;
                            $object = $dom;
                        }

                        if (!method_exists($object, $call['method'])) {
                            throw new MethodNotFoundXPatchMapperException($instruction, $call['method'], @get_class($object));
                        }

                        $object = call_user_func_array(array($object, $call['method']), $call['arguments']);
                    }

                    if (is_string($object)) {
                        $object = HtmlHelper::clearString($object, $instruction['options']);
                    }
                }

                $result[$instruction['name']] = (!$object) ? $instruction['default'] : $object;
            }

            $results[] = $result;
        }

        return $results;
    }
}
