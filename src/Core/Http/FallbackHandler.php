<?php
declare(strict_types=1);

namespace Tenanted\Core\Http;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Fallback Handler
 */
final class FallbackHandler
{
    /**
     * Handle the fallback
     *
     * @return never
     */
    public function __invoke(): never
    {
        throw new NotFoundHttpException();
    }
}