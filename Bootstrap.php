<?php

namespace Twig;

use Rad\Config;
use Rad\Core\Bundle;
use Rad\Routing\Router;

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
                    if (is_dir(SRC_DIR . "/$bundleName/Resource/template/")) {
                        $loader->addPath(SRC_DIR . "/$bundleName/Resource/template/", $bundleName);
                    }
                }

                $twig = new \Twig_Environment($loader);

                // add route generator function {
                $function = new \Twig_SimpleFunction(
                    'generateUrl',
                    function ($url = null, $withParams = true, $withLanguage = true, $incDomain = true) {
                        $router = $this->getRouter();

                        return $router->generateUrl($url, [
                            Router::GEN_OPT_LANGUAGE => $withLanguage,
                            Router::GEN_OPT_WITH_PARAMS => $withParams,
                            Router::GEN_OPT_INC_DOMAIN => $incDomain,
                        ]);
                    }
                );
                $twig->addFunction($function);

                return $twig;
            }
        );
    }
}
