<?php


namespace Ilx;


use Ilx\Module\ModuleManager;
use Kodiak\Application;
use Kodiak\Core\KodiConf;
use Kodiak\Exception\ConfigurationException;

class Ilx
{
    const CONFIG_FILENAME = "conf.json";

    private static $rootPath = null;

    private static $configuration = null;

    /**
     * Ilx Framework-t telepíti a paraméterben megadott modulokkal. A modul konfiguráció útvonal tetszőleges helyen
     * lehet, viszont az alkalmazást a working directoryba fogja feltelepíteni (getcwd()).
     *
     * @param string $path Útvonal a modul konfigurációs fájlhoz.
     */
    public static function install($path) {
        Ilx::update($path, true, true, true);
    }

    /**
     * Ilx Framework konfigurációt frissíti a paraméterben megadott modulokkal és beállításokkal.
     *
     * @param string $path Útvonal a modul konfigurációs fájlhoz.
     * @param bool $run_scripts Ha igaz lefuttatja a telepítő szkripteket
     * @param bool $include_templates Ha igaz, másolja a template-eket
     * @param bool $install
     */
    public static function update($path, $run_scripts, $include_templates, $install=false) {
        if($install) print("Installing Ilx-framework application...\n");
        else print("Updating Ilx-framework application...\n");
        print("Loading modules.json ...\n");
        $modules_config = json_decode(file_get_contents($path), true);

        //ModuleManager inicializálása
        print("Loading modules:\n");
        $moduleManager = ModuleManager::init($modules_config);

        // Module-ok inicializálása
        print("Bootstrapping modules:\n");
        $moduleManager->bootstrapModules();

        // Kodiak konfiguráció generálása
        print("Generating Kodiak configuration...\n");
        @mkdir(Ilx::configPath());
        $configuration = $moduleManager->collectConfiguration();
        $configuration->addEnvironmentalVariable("mode", $modules_config["mode"]);
        $configuration->addEnvironmentalVariable("timezone", date_default_timezone_get());
        $configuration->to_json(Ilx::configPath().DIRECTORY_SEPARATOR.Ilx::CONFIG_FILENAME);

        // Module-ok install szkriptjeinek futtatása
        if($run_scripts) {
            print("Executing modules' init scripts:\n");
            $moduleManager->runInstallScripts($include_templates);
        }

        print("Application ".$modules_config["name"]." has been successfully installed. The entry point of the application is web/index.php \n");
    }

    /**
     * Elindítja az ILX alkalmazást.
     */
    public static function run() {

        // Config beolvasása
        $config_array = Ilx::getConfiguration();

        // Környezeti változók beállítása
        date_default_timezone_set($config_array->get()[KodiConf::ENVIRONMENT]["timezone"]);
        error_reporting(E_ERROR | E_WARNING | E_PARSE);

        // Kodiak futtatása
        try {
            $application = Application::getInstance();
            $application->run($config_array->get());
        } catch (ConfigurationException $e) {
            print("ERR 500: configuration_error");
        }
    }


    /**
     * Konfigurációs könyvtár elérési útvonala. Alapméretezetten ez a working directory/config lesz.
     *
     * @return string
     */
    public static function configPath() {
        return self::rootPath().DIRECTORY_SEPARATOR."config";
    }

    /**
     * Konfigurációs könyvtár elérési útvonalának beállítása.
     *
     * @param string $path
     */
    public static function setRootPath($path) {
        self::$rootPath = $path;
    }


    /**
     * A paraméterben kapott konfigurációs fájlt updateli az akutális modul beállítások alapján.
     *
     * @return Configuration
     */
    public static function getConfiguration() {

        if(self::$configuration == null) {
            self::$configuration = Configuration::from_json(self::configPath().DIRECTORY_SEPARATOR
                .self::CONFIG_FILENAME);
        }

        return self::$configuration;
    }

    /**
     * View könyvtár elérési útvonala.
     *
     * @return string
     */
    public static function viewPath() {
        return self::rootPath().DIRECTORY_SEPARATOR."view";
    }

    /**
     * Web könyvtár elérési útvonala.
     * @return string
     */
    public static function webPath() {
        return self::rootPath().DIRECTORY_SEPARATOR."web";
    }

    /**
     * Css fájlokat tartalmazó könyvtár elérési útvonala.
     *
     * @param bool $relative Ha igaz, akkor a webPath-hoz képesti relatív útvonalat adja vissza.
     * @return string
     */
    public static function cssPath($relative = false) {
        if($relative) {
            return DIRECTORY_SEPARATOR."css";
        }
        return self::webPath().DIRECTORY_SEPARATOR."css";
    }

    /**
     * Js fájlokat tartalmazó könyvtár elérési útvonala.
     *
     * @param bool $relative Ha igaz, akkor a webPath-hoz képesti relatív útvonalat adja vissza.
     * @return string
     */
    public static function jsPath($relative = false) {
        if($relative) {
            return DIRECTORY_SEPARATOR."css";
        }
        return self::webPath().DIRECTORY_SEPARATOR."js";
    }

    /**
     * Kép fájlokat tartalmazó könyvtár elérési útvonala.
     *
     * @return string
     */
    public static function imagesPath() {
        return self::webPath().DIRECTORY_SEPARATOR."images";
    }

    /**
     * Logfájlokat tartalmazó mappa neve elérési útvonala.
     *
     * @return string
     */
    public static function logPath() {
        return self::rootPath().DIRECTORY_SEPARATOR."log";
    }

    /**
     * Visszaadja a projekt gyökér mappáját
     */
    public static function rootPath() {
        if(self::$rootPath == null) {
            self::$rootPath = getcwd();
        }
        return self::$rootPath;
    }
}