<?php

namespace Twig;

use Rad\Core\AbstractBundle;
use Rad\Core\Bundles;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Translation\IdentityTranslator;
use Twig\Library\Helper as TwigHelper;
use Rad\Configure\Config;
use Rad\DependencyInjection\Registry;
use Rad\Routing\Router;
use Twig_Extension_Debug;

/**
 * Twig Bundle
 *
 * @package Twig
 */
class TwigBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    public function loadService()
    {
        $this->getContainer()->setShared('twig',
            function () {
                $loader = new \Twig_Loader_Filesystem([]);
                $loader->addPath(SRC_DIR . "/App/Resource/template");

                foreach (Bundles::getLoaded() as $bundleName) {
                    if (is_dir(SRC_DIR . "/$bundleName/Resource/template/")) {
                        $loader->addPath(SRC_DIR . "/$bundleName/Resource/template", $bundleName);
                    }
                }
                $loader->addPath(ROOT_DIR . '/vendor/symfony/twig-bridge/Resources', 'TwigBridgeTemplates');

                if (Config::get('debug', false)) {
                    $options = [
                        'debug' => true,
                        'cache' => false,
                    ];
                } else {
                    $options = [
                        'debug' => false,
                        'cache' => CACHE_DIR . '/twig/',
                    ];
                }

                $twig = new \Twig_Environment($loader, $options);
                if (Config::get('debug', false)) {
                    $twig->addExtension(new Twig_Extension_Debug());
                }

                // add route generator function
                $twig->addFunction(new \Twig_SimpleFunction(
                        'generateUrl',
                        function ($url = null, $withParams = true, $withLanguage = true, $incDomain = true) {
                            $router = $this->getRouter();

                            return $router->generateUrl($url, [
                                Router::GEN_OPT_LANGUAGE => $withLanguage,
                                Router::GEN_OPT_WITH_PARAMS => $withParams,
                                Router::GEN_OPT_INC_DOMAIN => $incDomain,
                            ]);
                        }
                    )
                );

                $twig->addFunction(new \Twig_SimpleFunction(
                        'getService',
                        function ($name, array $args = []) {
                            return $this->getContainer()->get($name, $args);
                        }
                    )
                );

                /** @var Registry $registry */
                $registry = $this->getContainer()->get('registry');

                // return css tags
                $twig->addFunction(
                    new \Twig_SimpleFunction(
                        'getCss',
                        function () use ($registry) {
                            $result = $registry->get(TwigHelper::GLOBAL_CSS, TwigHelper::TWIG_REGISTRY_SCOPE);

                            if (is_array($result)) {
                                foreach ($result as $row) {
                                    $order[] = $row['priority'];
                                }

                                array_multisort($order, $result);

                                array_walk($result, function (&$item) {
                                    $item = $item['css'];

                                    // if it is direct link to file
                                    if (strpos($item, 'file://') === 0) {
                                        $item = substr($item, 7);
                                        $item = "<link rel=\"stylesheet\" href=\"$item\">";
                                    } else {
                                        $item = "<style>\n$item\n</style>";
                                    }
                                });

                                $result = implode("\n", $result);
                            }

                            return strval($result);
                        },
                        [
                            'is_safe' => ['html']
                        ]
                    )
                );

                // return js tags
                $twig->addFunction(new \Twig_SimpleFunction(
                        'getJs',
                        function () use ($registry) {
                            $result = $registry->get(TwigHelper::GLOBAL_JS, TwigHelper::TWIG_REGISTRY_SCOPE);

                            if (is_array($result)) {
                                foreach ($result as $row) {
                                    $order[] = $row['priority'];
                                }

                                array_multisort($order, $result);

                                array_walk($result, function (&$item) {
                                    $item = $item['js'];

                                    // if it is direct link to file
                                    if (strpos($item, 'file://') === 0) {
                                        $item = substr($item, 7);
                                        $item = "<script src=\"$item\"></script>";
                                    } else {
                                        $item = "<script>\n$item\n</script>";
                                    }
                                });

                                $result = implode("\n", $result);
                            }

                            return strval($result);
                        },
                        [
                            'is_safe' => ['html']
                        ]
                    )
                );

                // return master twig
                $twig->addFunction(new \Twig_SimpleFunction(
                        'getMasterTwig',
                        function () use ($registry) {
                            $result = $registry->get(TwigHelper::MASTER_TWIG, TwigHelper::TWIG_REGISTRY_SCOPE);
                            $item = array_shift($result);
                            $registry->set(TwigHelper::MASTER_TWIG, $result, TwigHelper::TWIG_REGISTRY_SCOPE);

                            return strval($item);
                        },
                        [
                            'is_safe' => ['html']
                        ]
                    )
                );

                Library\Helper::addMasterTwig('@App/master.twig');

                $renderer = new TwigRendererEngine(['@TwigBridgeTemplates/views/Form/form_div_layout.html.twig']);
                $renderer->setEnvironment($twig);
                $twig->addExtension(new FormExtension(new TwigRenderer($renderer)));
                $twig->addExtension(new TranslationExtension(new IdentityTranslator()));

                return $twig;
            }
        );
    }
}
