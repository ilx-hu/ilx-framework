<?php


namespace Ilx\Module\Menu;


use Basil\DataSource\ArraySource;
use Basil\DataSource\NestedSetSource;
use Basil\Tree;
use Ilx\Module\Database\DatabaseModule;
use Ilx\Module\IlxModule;
use Ilx\Module\Menu\Model\MenuItem;
use Ilx\Module\ModuleManager;
use Ilx\Module\Twig\TwigModule;
use PandaBase\Connection\ConnectionManager;
use PandaBase\Connection\Scheme\Table;

/**
 * Class MenuModule
 *
 * @package Ilx\Module\Menu
 */
class MenuModule extends IlxModule
{

    function defaultParameters()
    {
        return [
            "structure" => [],
            "table" => "menu"
        ];
    }

    function environmentalVariables()
    {
        return [];
    }

    function routes()
    {
        return [];
    }

    function serviceProviders()
    {
        return [[
            "class_name" => MenuServiceProvider::class,
            "parameters" => [
                "table" => $this->parameters["table"]
            ]
        ]];
    }

    function hooks()
    {
        return [];
    }

    function bootstrap(ModuleManager $moduleManager)
    {
        /** @var TwigModule $twig_module */
        $twig_module = $moduleManager::get("Twig");
        $twig_module->addContentProvider([
            "class_name" => MenuContentProvider::class,
            "parameters" => [
                "name" => "menu"
            ]
        ]);


        /** @var DatabaseModule $database_module */
        $database_module = $moduleManager::get("Database");

        $database_module->addTables([
            MenuItem::class  => [
                Table::TABLE_NAME => $this->parameters["table"],
                Table::TABLE_ID   => "node_id",
                Table::FIELDS     => [
                    "node_id"               => "int(10) unsigned NOT NULL AUTO_INCREMENT",
                    "node_lft"              => "int(10) DEFAULT NULL",
                    "node_rgt"              => "int(10) DEFAULT NULL",
                    "name"              => "varchar(255) DEFAULT NULL",
                    "title"             => "varchar(255) DEFAULT NULL",
                    "route"             => "varchar(255) DEFAULT NULL",
                ],
                Table::PRIMARY_KEY => ["node_id"]
            ]
        ]);

    }

    function initScript($include_templates)
    {
        print("Creating menu structure...\n");

        $roots = [
            "name"  => "root",
            "title" => "Root",
            "route" => null,
            "children" => $this->parameters["structure"]
        ];


        Tree::convert(new ArraySource([
            ArraySource::NODE_ID => "role_id",
            ArraySource::CHILDREN=> "children",
            ArraySource::ROOT_ID => 1
        ], $roots), new NestedSetSource([
            NestedSetSource::DB         => ConnectionManager::getInstance()->getConnection()->getDatabase(),
            NestedSetSource::TABLE_NAME => $this->parameters["table"],
            NestedSetSource::NODE_ID    => "node_id",
            NestedSetSource::ROOT_ID    => 1,
            NestedSetSource::LEFT       => "node_lft",
            NestedSetSource::RIGHT      => "node_rgt"
        ]));
        print("Menu structure has been created.\n");
    }
}