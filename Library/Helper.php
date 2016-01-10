<?php

namespace Twig\Library;

use Rad\DependencyInjection\Container;
use Rad\DependencyInjection\Registry;

class Helper
{
    const GLOBAL_CSS = 'global_css';
    const GLOBAL_JS = 'global_js';

    const MASTER_TWIG = 'master_twig';
    const TWIG_REGISTRY_SCOPE = 'twig';

    public static function addJs($js, $priority = 0)
    {
        $js = [
            'js' => $js,
            'priority' => $priority
        ];

        /** @var Registry $registry */
        $registry = Container::get('registry');

        $result = (array) $registry->get(self::GLOBAL_JS, self::TWIG_REGISTRY_SCOPE);

        // skip if it's duplicate
        foreach ($result as $node) {
            if ($js['js'] == $node['js']) {
                return;
            }
        }

        $result[] = $js;
        $registry->set(self::GLOBAL_JS, $result, self::TWIG_REGISTRY_SCOPE);
    }

    public static function addCss($css, $priority = 0)
    {
        $css = [
            'css' => $css,
            'priority' => $priority
        ];

        /** @var Registry $registry */
        $registry = Container::get('registry');

        $result = (array) $registry->get(self::GLOBAL_CSS, self::TWIG_REGISTRY_SCOPE);

        // skip if it's duplicate
        foreach ($result as $node) {
            if ($css['css'] == $node['css']) {
                return;
            }
        }

        $result[] = $css;
        $registry->set(self::GLOBAL_CSS, $result, self::TWIG_REGISTRY_SCOPE);
    }

    public static function addMasterTwig($masterTwig)
    {
        /** @var Registry $registry */
        $registry = Container::get('registry');

        $result = $registry->get(self::MASTER_TWIG, self::TWIG_REGISTRY_SCOPE);
        $result[] = $masterTwig;
        $registry->set(self::MASTER_TWIG, $result, self::TWIG_REGISTRY_SCOPE);
    }
}
