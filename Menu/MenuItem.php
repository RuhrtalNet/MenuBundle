<?php
/**
 * This file is part of the RuhrtalNet\MenuBundle.
 *
 * @version $Revision$
 */

namespace RuhrtalNet\MenuBundle\Menu;

/**
 *
 */
class MenuItem
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $route;

    /**
     * @var string[]
     */
    public $routeParameters = array();

    /**
     * Internal menu path with the following format:
     *
     * ${menu}:${submenu}:${item}
     *
     * or
     *
     * ${menu}:${item}
     *
     * This property is used to build the menu tree.
     *
     * @var string
     */
    public $path;

    /**
     * @var integer
     */
    public $order;

    /**
     * Is this menu item marked as disabled?
     *
     * @var boolean
     */
    public $disabled = false;

    /**
     * Is this menu item for the current user visible?
     *
     * @var boolean
     */
    public $visible = true;

    /**
     * Is this an exclusive menu item?
     *
     * If a menu item is marked as exclusive all other items within the same
     * menu will will be flagged as disabled.
     *
     * @var boolean
     */
    public $exclusive = false;

    /**
     * @var boolean
     */
    public $displayChildren = false;

    public function __construct(array $values = array())
    {
        foreach ($values as $name => $value) {
            $this->$name = $value;
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \OutOfBoundsException("No property \${$name} exists.");
    }

    public function __set($name, $value)
    {
        throw new \OutOfBoundsException("No property \${$name} exists.");
    }
}
