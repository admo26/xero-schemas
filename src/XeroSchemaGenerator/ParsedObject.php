<?php

namespace Calcinai\XeroSchemaGenerator;

use ICanBoogie\Inflector;

abstract class ParsedObject
{

    /**
     * @var string
     */
    protected $singular_name;
    protected $collective_name;
    protected $raw_name;

    protected $aliases = [];

    public function __construct($raw_name)
    {
        $this->raw_name = $raw_name;

        $parsed = self::parseRawName($raw_name);

        $this->name = array_shift($parsed);
        $this->collective_name = Inflector::get()->pluralize($this->name);
        $this->singular_name = Inflector::get()->singularize($this->name);

        $this->aliases += $parsed;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSingularName()
    {
        return $this->singular_name;
    }

    public function getCollectiveName()
    {
        return $this->collective_name;
    }

    /**
     * @param string $singular_name
     * @return static
     */
    public function setSingularName($singular_name)
    {
        $this->singular_name = $singular_name;
        return $this;
    }

    /**
     * Parse a raw name into all possible names
     *
     * @param $raw_name
     * @return array
     */
    public static function parseRawName($raw_name)
    {
        $names = [];

        $exploded_names = explode(' and ', $raw_name);
        foreach ($exploded_names as $name) {
            //Name sure it's singular and title case
            $name = ucwords($name);
            //Remove spaces and non-a-z
            $names[] = preg_replace('/[^a-z]+/i', '', $name);
        }

        //If there are two, see if they're common
        if (count($names) === 2) {
            $a = strrev($names[0]);
            $b = strrev($names[1]);
            $shortest = min(strlen($a), strlen($b));

            for ($i = 0; $i < $shortest; $i++) {
                if ($a[$i] !== $b[$i]) {
                    break;
                }
            }

            //More than 80% of the word the same, add it to the start
            if ($i / $shortest > 0.8) {
                array_unshift($names, substr($names[0], -$i));
            }
        }

        return $names;
    }
}