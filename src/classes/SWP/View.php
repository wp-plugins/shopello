<?php
namespace SWP;

use \Twig_Loader_Filesystem;
use \Twig_Environment;
use \Twig_Extension_Debug;
use \Twig_SimpleFilter;
use \Twig_SimpleFunction;

class View
{
    /** @var Twig_Loader_Filesystem */
    private $twigLoader;
    /** @var Twig_Environment */
    private $twigEnvironment;

    private static $instance;

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->twigLoader = new Twig_Loader_Filesystem(SHOPELLO_PLUGIN_DIR.'src/twig/');
        $this->twigEnvironment = new Twig_Environment($this->twigLoader, array( 'debug' => true ));

        $this->twigEnvironment->addExtension(new Twig_Extension_Debug());

        // Add cast_to_array to cast objects to arrays
        $this->twigEnvironment->addFilter(new Twig_SimpleFilter('cast_to_array', function ($stdClassObject) {
            return (array) $stdClassObject;
        }));

        $this->twigEnvironment->addFunction(new Twig_SimpleFunction('__', function ($string, $namespace) {
            return __($string, $namespace);
        }));
    }

    public function render($template, $data = array())
    {
        return $this->twigEnvironment->render($template.'.twig', $data);
    }
}
