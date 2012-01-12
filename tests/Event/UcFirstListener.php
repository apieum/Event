<?php
/**
 * File UcFirstListener.php
 *
 * PHP version 5.2
 *
 * @category tests/fixtures
 * @package  Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     UcFirstListener.php
 *
 */
/**
 * Extends listener with upper cases events
 * 
 * @category tests/fixtures
 * @package  Event
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     UcFirstListener
 *
 */
class UcFirstListener extends Event_Listener
{
    /**
     * Return ucfirst of $string
     * 
     * @param string $word the string to apply ucfirst
     * 
     * @return string
     */
    public function onUcFirst($word)
    {
        return ucfirst($word);
    }
    /**
     * return all the words to upper case
     * 
     * @param string $sentence the sentence to upper case
     * 
     * @return string
     */
    public function onUppercase($sentence)
    {
        return ucwords($sentence);
    }
}