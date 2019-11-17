<?php


namespace Ilx\Module\Menu\Model;



use Basil\DataSource\NestedSetSource;
use Basil\Node\Node;
use Basil\Tree;
use PandaBase\Connection\ConnectionManager;

class Menu
{
    /**
     * @var Node
     */
    private $menu_tree;

    public function __construct($configuration)
    {
        $this->menu_tree = Tree::from(new NestedSetSource([
            NestedSetSource::DB         => ConnectionManager::getInstance()->getConnection()->getDatabase(),
            NestedSetSource::TABLE_NAME => $configuration["table"],
            NestedSetSource::NODE_ID    => "node_id",
            NestedSetSource::ROOT_ID    => 1,
            NestedSetSource::LEFT       => "node_lft",
            NestedSetSource::RIGHT      => "node_rgt"
        ]));
    }

    public function subMenus() {
        return $this->menu_tree->children();
    }

    public function hasSubMenus() {
        return count($this->menu_tree->children()) > 0;
    }

    public function title() {
        return $this->menu_tree->data()["title"];
    }

    public function name() {
        return $this->menu_tree->data()["name"];
    }

    public function route() {
        return $this->menu_tree->data()["route"];
    }
}