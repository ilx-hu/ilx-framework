<?php


namespace Ilx\Module\Menu;


use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Ilx\Module\Twig\TwigModule;

/**
 * Class MenuModule
 *
 * TODO: amit tisztázni kell, hogy a route-k hozzárendelése hogyan történjen.
 * azt biztos hogy a route-ra a neve alapján kell hivatkozni
 * @package Ilx\Module\Menu
 */
class MenuModule extends IlxModule
{

    function defaultParameters()
    {
        return [
            "structure" => [
                [
                    "name" => "home",
                    "title" => "Kezdőlap",
                    "children" => []
                ],
                [
                    "name" => "contacts",
                    "title" => "Kapcsolat",
                    "children" => [
                        [
                            "name" => "colleagues",
                            "title" => "Munkatársak",
                            "children" => []
                        ],
                        [
                            "name" => "address",
                            "title" => "Címek",
                            "children" => []
                        ],
                    ]
                ],
            ],
            "url_prefix" => "/page"
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
                "url_prefix" => $this->parameters["url_prefix"]
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
    }

    function initScript($include_templates)
    {
        // TODO: adatbázis feltöltése
    }
}