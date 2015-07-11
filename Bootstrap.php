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
                    $loader->addPath(SRC_DIR . "/$bundleName/Resource/templates/", $bundleName);
                }

                //var_dump($loader->getPaths());die;
                $twig = new \Twig_Environment($loader);

                return $twig;
            }
        );
    }
}
