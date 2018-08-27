<?php
/**
 * Created by PhpStorm.
 * User: mprow
 * Date: 8/27/2018
 * Time: 4:27 PM
 */

class CommandHelp
{
    private static $actions = [
        [
            'params' => ['h', 'help'],
            'text' => 'Show help'
        ],[
            'params' => ['a', 'add'],
            'text' => 'Add new item'
        ],[
            'params' => ['d', 'dupe'],
            'text' => 'Remove duplicate items'
        ],[
            'params' => ['s', 'search'],
            'text' => 'Search for item',
            'argument' => 'phone'
        ],[
            'params' => ['x', 'remove'],
            'text' => 'Remove item',
            'argument' => 'phone'
        ],[
            'params' => ['m', 'merge'],
            'text' => 'Merge lists'
        ],[
            'params' => ['u', 'update'],
            'text' => 'Update item'
        ],[
            'params' => ['r', 'count-rows'],
            'text' => 'Count rows in list'
        ],[
            'params' => ['c', 'count-cols'],
            'text' => 'Count columns in list'
        ],[
            'params' => ['l', 'list-cols'],
            'text' => 'Show list of columns'
        ]
    ];

    private static $options = [
        [
            'params' => 'use-big-list',
            'text' => 'Use big test list'
        ],[
            'params' => 'phone',
            'text' => 'Phone number',
            'value' => true
        ],[
            'params' => 'first-name',
            'text' => 'First name',
            'value' => true
        ],[
            'params' => 'last-name',
            'text' => 'Last name',
            'value' => true
        ],[
            'params' => 'title',
            'text' => 'Title',
            'value' => true
        ],[
            'params' => 'address',
            'text' => 'Address',
            'value' => true
        ],[
            'params' => 'address-2',
            'text' => 'Address 2',
            'value' => true
        ],[
            'params' => 'city',
            'text' => 'City',
            'value' => true
        ],[
            'params' => 'state',
            'text' => 'State',
            'value' => true
        ],[
            'params' => 'zip-code',
            'text' => 'Zip code',
            'value' => true
        ],[
            'params' => 'job-title',
            'text' => 'Job title',
            'value' => true
        ],[
            'params' => 'email',
            'text' => 'Email',
            'value' => true
        ],[
            'params' => 'voted',
            'text' => 'Voted',
            'value' => true
        ],[
            'params' => 'district',
            'text' => 'District',
            'value' => true
        ],[
            'params' => 'special-id',
            'text' => 'Special ID',
            'value' => true
        ],[
            'params' => 'party',
            'text' => 'Party',
            'value' => true
        ]
    ];

    private static $examples = [
        "Search by phone: -s 2225551212",
        "Add record: --add --phone 2225551212 --first-name Test --last-name Human --title test --address here --city Denver --state CO --zip-code 80222 --job-title Tester --email te@st.com --voted 1 --district 2 --special-id testid1 --party party1",
        "Delete record: -x 2225551212 --use-big-list",
        "Update record: -u --phone 2225551212 --first-name T",
    ];

    /**
     * Get help text
     * @return string
     */
    public static function getHelpText()
    {
        global $argv;
        $result = '';
        $result .= $argv[0] . " <action> [options]" . PHP_EOL;
        $result .= "\nActions:\n";
        $result .= self::getHelpTextItems(static::$actions);

        $result .= "\nOptions:\n";
        $result .= self::getHelpTextItems(static::$options);

        $result .= "\nExamples:\n";
        $result .= self::getExamples();

        return $result;
    }

    private static function getExamples()
    {
        $result = '';
        foreach (static::$examples as $example) {
            $result .= "\t" . $example . "\n";
        }
        return $result;
    }

    /**
     * @param $nodes
     * @return string
     */
    private static function getHelpTextItems($nodes)
    {
        $result = '';

        foreach ($nodes as $node) {
            $params = [];
            if (!is_array($node['params'])) {
                $node['params'] = [$node['params']];
            }
            foreach ($node['params'] as $param) {
                $paramPrefix = strlen($param) === 1 ? '-' : '--';
                $params[] = $paramPrefix . $param;
            }

            $result .= "\t[" . implode('|', $params) . "";

            if (!empty($node['argument'])) {
                $result .= "] <" . $node['argument'] . ">";
            } elseif (isset($node['value']) && $node['value']) {
                $result .= "=<value>]";
            } else {
                $result .= "]";
            }

            $result .= "\n\t\t" . $node['text'] . "\n";
        }

        return $result;
    }

}