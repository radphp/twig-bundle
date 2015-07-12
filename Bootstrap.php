<?php

namespace Twig;

use Rad\Config;
use Rad\Core\Bundle;

/**
 * Twig Bootstrap
 *
 * @package Twig
 */
class Bootstrap extends Bundle
{
    public function startup()
    {
        $this->initTwig();
    }

    /**
     * Initialize twig object
     *
     * @return void
     */
    protected function initTwig()
    {
        $this->getContainer()->setShared('twig',
            function () {
                $loader = new \Twig_Loader_Filesystem([]);
                foreach (Config::get('bundles', []) as $bundleName => $options) {
                    $loader->addPath(SRC_DIR . "/$bundleName/Resource/template/", $bundleName);
                }

                $twig = new \Twig_Environment($loader);

                // add route generator function {
                $function = new \Twig_SimpleFunction(
                    'generateUrl',
                    function ($url = null, $options = null) {
                        $router = $this->getRouter();

                        return $router->generateUrl($url, $options);
                    }
                );
                $twig->addFunction($function);

                return $twig;
            }
        );
    }
}
