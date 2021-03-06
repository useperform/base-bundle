<?php

namespace Perform\BaseBundle\Util;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class StringUtil
{
    /**
     * Create a sensible, human readable default for $string,
     * e.g. creating a label for the name of form inputs.
     *
     * @param string $string
     *
     * @return string
     */
    public static function sensible($string)
    {
        $string = preg_replace('`([A-Z])`', '-\1', $string);
        $string = str_replace(['-', '_'], ' ', $string);

        return ucfirst(trim(strtolower($string)));
    }

    /**
     * Show the beginning of a piece of non-formatted text.
     *
     * @param string $text
     *
     * @return string
     */
    public static function preview($text)
    {
        if (strlen($text) < 50) {
            return $text;
        }

        return substr($text, 0, 50).'…';
    }

    /**
     * Create a suitable name for an entity managed by a crud class.
     *
     * @param string $class The crud classname
     */
    public static function crudClassToEntityName($class)
    {
        //EntityNameCrud -> Entity Name
        return trim(preg_replace('/([A-Z][a-z])/', ' \1', self::classBasename($class, 'Crud')));
    }

    /**
     * Get the basename of a class, optionally removing a suffix if it exists.
     *
     * @param string $class
     * @param string $suffix
     *
     * @return string
     */
    public static function classBasename($class, $suffix = '')
    {
        $pieces = explode('\\', $class);
        $end = end($pieces);

        if (!$suffix) {
            return $end;
        }

        return substr($end, 0 - strlen($suffix)) === $suffix ? substr($end, 0, 0 - strlen($suffix))  : $end;
    }

    /**
     * Suggest the class name of an entity for a crud class.
     *
     * @param string $crudClass
     */
    public static function entityClassForCrud($crudClass)
    {
        $basename = static::classBasename($crudClass, 'Crud');
        $pieces = explode('\\', $crudClass);
        // Namespace\AppBundle\Crud\SomethingCrud -> Namespace\AppBundle\Entity\Something
        array_pop($pieces);
        array_pop($pieces);
        $pieces[] = 'Entity';
        $pieces[] = $basename;

        return implode('\\', $pieces);
    }

    /**
     * Suggest a twig template location for a crud class.
     *
     * @param string $crudClass
     * @param string $context    The crud context
     */
    public static function templateForCrud($crudClass, $context)
    {
        $basename = strtolower(preg_replace('/([a-z\d])([A-Z])/', '\\1_\\2', self::classBasename($crudClass, 'Crud')));

        $pos = strpos($crudClass, 'Bundle');
        if ($pos !== false) {
            // Vendor/FooBundle -> VendorFoo
            $namespace = str_replace('\\', '', substr($crudClass, 0, $pos));

            return sprintf('@%s/crud/%s/%s.html.twig', $namespace, $basename, $context);
        }

        return sprintf('crud/%s/%s.html.twig', $basename, $context);
    }
}
