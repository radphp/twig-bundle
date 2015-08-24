<?php

namespace Twig\Library;

use Rad\DependencyInjection\ContainerAwareTrait;
use Rad\Network\Http\Response;

/**
 * Twig Response
 *
 * @package Twig\Library
 */
class TwigResponse extends Response
{
    use ContainerAwareTrait;

    /**
     * Twig\Library\TwigResponse constructor
     *
     * @param string $templateName
     * @param array  $context
     * @param int    $status
     * @param string $reason
     * @param array  $headers
     *
     * @throws \Rad\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function __construct($templateName, array $context = [], $status = 200, $reason = '', array $headers = [])
    {
        /** @var \Twig_Environment $twig */
        $twig = $this->getContainer()->get('twig');

        parent::__construct($twig->render($templateName, $context), $status, $reason, $headers);
    }

    /**
     * Factory method for chain ability.
     *
     * @param string $templateName
     * @param array  $context
     * @param int    $status
     * @param string $reason
     * @param array  $headers
     *
     * @return Response
     */
    public static function create($templateName, array $context = [], $status = 200, $reason = '', array $headers = [])
    {
        return new static($templateName, $context, $status, $reason, $headers);
    }
}
