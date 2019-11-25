<?php


namespace Ilx\Module\Frame;


use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Ilx\Module\Twig\TwigModule;

class FrameModule extends IlxModule
{
    const COPY_ON_INSTALL = "copy_on_install";
    const FRAME_NAMES = "frame_names";
    const DEFAULT_FRAME = "default_frame";

    const PAGE_TITLE = "page_title";
    const STYLESHEETS = "stylesheets";
    const JAVASCRIPTS = "javascripts";

    function defaultParameters()
    {
        return [
            # kell-e telepítés esetén másolni a mintát
            FrameModule::COPY_ON_INSTALL => false,
            FrameModule::FRAME_NAMES => ["basic"],
            FrameModule::DEFAULT_FRAME => "basic",

            FrameModule::PAGE_TITLE  => "my page",
            FrameModule::STYLESHEETS => [],
            FrameModule::JAVASCRIPTS => []
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
            "class_name" => FrameServiceProvider::class,
            "parameters" => [
                "title" => $this->parameters[FrameModule::PAGE_TITLE],
                "stylesheets" => $this->parameters[FrameModule::STYLESHEETS],
                "javascripts" => $this->parameters[FrameModule::JAVASCRIPTS]
            ]
        ]];
    }

    function hooks()
    {
        return [];
    }

    function bootstrap(ModuleManager $moduleManager)
    {
        print("Bootstraping FrameModule...\n");

        print("\tRegistering FrameContentProvider...\n");
        /** @var TwigModule $twig_module */
        $twig_module = $moduleManager::get("Twig");
        $twig_module->addContentProvider([
            "class_name" => FrameContentProvider::class,
            "parameters" => [
                "name" => "frame"
            ]
        ]);

        # csak akkor csinálunk bármit is, ha a copy_on_install be van billentve.
        if($this->parameters[FrameModule::COPY_ON_INSTALL]) {
            foreach ($this->parameters[FrameModule::FRAME_NAMES] as $frame_name) {
                # kiválasztjuk a megelelő template útvonalt
                $template_path = dirname(__FILE__).DIRECTORY_SEPARATOR."Templates".DIRECTORY_SEPARATOR.$frame_name;
                # hozzáadjuk, mint template útvonal. Ezeket mindig másoljuk és nem készül róluk szimbolikus link
                $twig_module->addTemplatePath($template_path, $frame_name, false);
                # a frame-eket még regisztrálni kell, mint új frame.
                $twig_module->setFrame($frame_name, DIRECTORY_SEPARATOR.$frame_name.DIRECTORY_SEPARATOR."frame.twig");
                print("\t- Added '$frame_name' as frame template\n");
            }

            # default frame beállítása
            $default = $this->parameters[FrameModule::DEFAULT_FRAME];
            $twig_module->setFrame("default", DIRECTORY_SEPARATOR.$default.DIRECTORY_SEPARATOR."frame.twig");
            print("\t- '$default' has been set as default frame\n");
        }
        else {
            print("\t Bootstraping has been skipped, because copy_on_install parameter is false.\n");
        }
    }

    function initScript($include_templates)
    {
        // A fájlok másolását a Twig modulon keresztül a resource modul végzi, így itt nincs tennivaló.
    }
}