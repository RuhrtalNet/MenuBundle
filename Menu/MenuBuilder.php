<?php
/**
 * This file is part of the RuhrtalNet\MenuBundle.
 *
 * @version $Revision$
 */

namespace RuhrtalNet\MenuBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use phpDocumentor\Reflection\DocBlock\Tag\ReturnTag;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class MenuBuilder
{
    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * Optional list of available menu slots. If this property is <b>NULL</b>
     * the builder will create requested menu slots on demand.
     *
     * @var string[]
     */
    private $availableMenus;

    /**
     * @var \RuhrtalNet\MenuBundle\Menu\MenuItem[][]
     */
    private $menuItems = array();

    /**
     * Mapping between paths and the corresponding routes.
     *
     * @var array
     */
    private $pathMapping = array();

    /**
     * Internal state with all active menu paths.
     *
     * @var array
     */
    private $activePaths;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param string[] $availableMenus
     */
    public function __construct(ContainerInterface $container, array $availableMenus = null)
    {
        $this->factory = $container->get('knp_menu.factory');
        $this->request = $container->get('request');

        $this->availableMenus = $availableMenus;
    }

    public function __call($method, $args)
    {
        if (preg_match('(^createBreadcrumb$)i', $method)) {
            return $this->createBreadcrumbMenu();
        }
        if (preg_match('(^create([a-z0-9]+)Menu$)i', $method, $match)) {
            $target = trim(preg_replace_callback(
                '(([A-Z]))',
                function ($m) {
                    return ':' . $m[1];
                },
                $match[1]
            ), ':');
            return $this->createMenuFromPath($target);
        }
        throw new \BadMethodCallException("Unexpected method {$method}() called.");
    }

    /**
     * @param string $path
     * @return \Knp\Menu\ItemInterface
     */
    protected function createMenuFromPath($path)
    {
        if (false === isset($this->menuItems[$path])) {
            throw new \OutOfBoundsException("Undefined menu {$path} requested.");
        }
        return $this->createMenuFromItems($this->menuItems[$path]);
    }

    /**
     * @param \RuhrtalNet\MenuBundle\Menu\MenuItem[] $items
     * @return \Knp\Menu\ItemInterface[]
     */
    protected function createMenuFromItems(array $items)
    {
        $this->activateRoutes();

        $menu = $this->factory->createItem('root');

        return $this->prepareMenuFromItems($menu, $items);
    }

    /**
     * @return \Knp\Menu\ItemInterface[]
     */
    protected function createBreadcrumbMenu()
    {
        $this->activateRoutes();

        $items = array();
        $paths = array_keys($this->activePaths);
        rsort($paths);

        for ($i = 1; $i < count($paths); ++$i) {
            $path   = $paths[$i - 1];
            $target = $paths[$i];

            if (isset($this->menuItems[$target][$path])) {
                $items[$path] = $this->menuItems[$target][$path];
            }
        }

        return $this->createMenuFromItems($items);
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     * @param \RuhrtalNet\MenuBundle\Menu\MenuItem[] $items
     * @return \Knp\Menu\ItemInterface
     */
    protected function prepareMenuFromItems(ItemInterface $menu, array $items)
    {
        usort(
            $items,
            function (MenuItem $item0, MenuItem $item1) {
                return $item0->order - $item1->order;
            }
        );

        $hasActiveExclusive = false;

        foreach ($items as $menuItem) {

            if (false === (boolean) $menuItem->visible) {
                continue;
            }

            $routeParameters = array();
            foreach ($menuItem->routeParameters as $parameter => $default) {
                $routeParameters[$parameter] = $this->request->get($parameter) ?: $default;
            }

            $child = $menu->addChild(
                $menuItem->label,
                array(
                    'route' => $menuItem->route,
                    'routeParameters' => $routeParameters,
                )
            );

            $child->setExtras(
                array(
                    'id'        => $menuItem->id,
                    'icon'      => $menuItem->icon,
                    'route'     => $menuItem->route,
                    'active'    => isset($this->activePaths[$menuItem->path]),
                    'disabled'  => (boolean) $menuItem->disabled,
                    'exclusive' => (boolean) $menuItem->exclusive,
                )
            );

            if ($menuItem->displayChildren && isset($this->menuItems[$menuItem->path])) {
                $child = $this->prepareMenuFromItems(
                    $child,
                    $this->menuItems[$menuItem->path]
                );
            }

            if ($menuItem->exclusive && isset($this->activePaths[$menuItem->path])) {
                $hasActiveExclusive = true;
            }
        }

        if ($hasActiveExclusive) {
            foreach ($menu->getChildren() as $child) {
                $child->setExtra('disabled', !$child->getExtra('active'));
            }
        }

        return $menu;
    }

    public function addMenuItem(MenuItem $menuItem)
    {
        $target = substr($menuItem->path, 0, strrpos($menuItem->path, ':'));

        $this->createMenuSlot($target);

        if (false === isset($this->menuItems[$target])) {
            throw new \InvalidArgumentException("No menu with name '{$target}' exists.");
        }
        $this->menuItems[$target][$menuItem->path] = $menuItem;

        $this->pathMapping[$menuItem->path] = $menuItem->route;
    }

    private function createMenuSlot($target)
    {
        if (isset($this->menuItems[$target])) {
            return;
        }

        if (null === $this->availableMenus || in_array($target, $this->availableMenus)) {
            $this->menuItems[$target] = array();
        }
    }

    /**
     *
     */
    private function activateRoutes()
    {
        if (is_array($this->activePaths)) {
            return;
        }
        $this->activePaths = array();

        $route = $this->request->get('_route');
        foreach ($this->pathMapping as $path => $itemRoute) {
            if ($itemRoute != $route) {
                continue;
            }
            $this->activePaths[$path] = true;

            $parts = explode(':', $path);
            while (array_pop($parts)) {
                $this->activePaths[join(':', $parts)] = true;
            }
        }
    }
}

