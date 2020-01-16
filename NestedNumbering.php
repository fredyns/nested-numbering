<?php

namespace fredyns\nestednumbering;

/**
 * Create Nested Numbering
 * supporting to 5 level
 * 
 * Usage:
 *  NestedNumbering::start(['A', '1', 'a', '1)', 'a>', 'indentation' => '&nbsp;&nbsp;&nbsp;&nbsp;' OR FALSE, 'space' => '&nbsp;', 'encode' => true, 'full' => false]);  // start numbering
 *  NestedNumbering::newItem($level);
 * 
 *
 * @author Fredy Nurman Saleh <email@fredyns.net>
 */
class NestedNumbering
{
    const TYPE_NUMERIC = '1';
    const TYPE_UPPERCHAR = 'A';
    const TYPE_LOWERCHAR = 'a';
    const TYPE_UPPERROMAN = 'I';
    const TYPE_LOWERROMAN = 'i';

    public static $config = [
        'A.', // numbering type level 1
        '1.', // numbering type level 2
        'a.', // numbering type level 3
        'i.', // numbering type level 4
        '1)', // numbering type level 5
        'prefix' => '', // prepend output
        'indentation' => '&nbsp;&nbsp;&nbsp;&nbsp;', // indent for sub numbering
        'full' => FALSE, // produce full nested number (eg: A.1.a.i.1)
        'suffix' => '', // append output
    ];
    public static $counter = []; // storing current numbering counter

    /**
     * starting new nested numbering
     * 
     * @param array $config
     */

    public static function start($config = [])
    {
        static::$counter = []; // reset counter

        foreach (array_keys(static::$config) as $key) {
            // only overwrite necessary config
            if (isset($config[$key])) {
                static::$config[$key] = $config[$key];
            }
        }
    }

    /**
     * create new numbering item
     * 
     * @param int $level
     * @return string
     */
    public static function newItem($level = 1)
    {
        // ensure counter
        static::ensureCounter($level);

        // increase counter
        $sequence = static::newSequence($level);
        $number = static::formatNumber($sequence, $level);

        if (static::$config['full']) { // prepend full numbering
            $number .= '.'; // as tail

            if ($level > 1) { // full number works for level 2 & more
                for ($iterate_level = $level - 1; $iterate_level >= 1; $iterate_level--) { // iterate from above level to 1
                    $upper_number = static::getNumber($iterate_level).'.'; // upper level numbering with dot tail
                    $number = $upper_number.$number; // prepend to parsed numbering
                }
            }
        } else { // format regular numbering
            $number .= static::getTail($level);
            // add indentation
            if (static::$config['indentation'] !== FALSE && $level > 1) {
                $tab_added = $level - 1;
                $number = str_repeat(static::$config['indentation'], $tab_added).$number;
            }
        }

        return $number;
    }

    /**
     * ensure target numbering level & upper level properly initiated
     * 
     * @param int $level
     */
    private static function ensureCounter($level)
    {
        for ($iterator = 1; $iterator <= $level; $iterator++) { // iterate from 1 to target level
            if (!isset(static::$counter[$iterator])) { // when level not exist
                static::$counter[$iterator] = 0; // initiate counter
            }
        }
    }

    /**
     * get new sequence number on defined level
     * @param int $level
     * @return int
     */
    private static function newSequence($level)
    {
        $new_number = null;

        foreach (static::$counter as $counter_level => $counter_value) {
            if ($counter_level < $level) { // upper counter
                if ($counter_value == 0) { // not properly initiated (bug prevention)
                    static::$counter = 1; // ensure upper numbering
                }
            } elseif ($counter_level == $level) { // target counter
                $new_number = ++static::$counter[$counter_level]; // this is new number generated
            } elseif ($counter_level > $level) { // sub-numbering
                static::$counter[$counter_level] = 0; // reset counter
            }
        }

        return $new_number;
    }

    /**
     * format numbering as type spesified
     * @param int $sequence
     * @param int $level
     * @return string
     */
    private static function formatNumber($sequence, $level)
    {
        $type = static::getType($level);

        switch ($type) {
            case static::TYPE_UPPERCHAR:
                return static::int2Char($sequence, TRUE);
            case static::TYPE_UPPERROMAN:
                return static::int2Roman($sequence);
            case static::TYPE_LOWERCHAR:
                return static::int2Char($sequence, FALSE);
            case static::TYPE_LOWERROMAN:
                return strtolower(static::int2Roman($sequence));
            case static::TYPE_NUMERIC:
            default : // in case of invalid type spesified
                return $sequence;
        }
    }

    /**
     * get current numbering on particular level
     * @param int $level
     * @return string
     */
    private static function getNumber($level)
    {
        $sequence = static::$counter[$level];

        return static::formatNumber($sequence, $level);
    }

    /**
     * get numbering type on particular level
     * @param int $level
     * @return string
     */
    private static function getType($level)
    {
        $index = $level - 1; // index on array

        if (!isset(static::$config[$index])) {
            return static::TYPE_NUMERIC;
        }

        $type = (string) static::$config[$index];

        if (strlen($type) > 1) {
            $type = substr($type, 0, 1);
        }

        return $type;
    }

    /**
     * get numbering tail on particular level
     * @param int $level
     * @return string
     */
    private static function getTail($level)
    {
        $index = $level - 1; // index on array

        if (!isset(static::$config[$index])) {
            return static::TYPE_NUMERIC;
        }

        $type = (string) static::$config[$index];

        if (strlen($type) > 1) {
            return substr($type, 1);
        }

        return '.';
    }

    /**
     * convert numeric sequence to alphabetical (A, B, ..., AA, ...)
     * @param int $integer
     * @param bool $uppercase
     * @return string
     */
    public static function int2Char($integer, $uppercase = true)
    {
        $higher_number = '';
        $current_number = '';

        if ($integer > 26) {
            /**
             * if integer exceeding 26, do nesting convert
             */
            $division = floor($integer / 26);
            $higher_number = static::int2Char($division, $uppercase);

            $integer %= 26;
        }

        /**
         * see ascii table
         * 'A' start from 65, offset is 64
         * 'a' start from 97, offset is 96
         * number 1 will return A (capital letter)
         */
        $offset = $uppercase ? 64 : 96;
        $ascii = $integer + $offset;
        $current_number = chr($ascii);

        // result
        return $higher_number.$current_number;
    }

    /**
     * convert arabic to roman numbering
     * 
     * got from https://www.hashbangcode.com/article/php-function-turn-integer-roman-numerals
     * 
     * @param int $integer
     * @return string
     */
    public static function int2Roman($integer)
    {
        // Convert the integer into an integer (just to make sure)
        $integer = intval($integer);

        // Create a lookup array that contains all of the Roman numerals.
        $lookup = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        ];

        $result = '';
        foreach ($lookup as $roman => $value) {
            // Determine the number of matches
            $matches = intval($integer / $value);

            // Add the same number of characters to the string
            $result .= str_repeat($roman, $matches);

            // Set the integer to be the remainder of the integer and the value
            $integer = $integer % $value;
        }

        // The Roman numeral should be built, return it
        return $result;
    }

}
