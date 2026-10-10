<?php
/**
 * Copyright © Magenx. All rights reserved.
 */
declare(strict_types=1);

namespace Magenx\GraphQlTiming\Plugin;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;

/**
 * Stamps the GraphQL response with a `Server-Timing: magento-app;dur=<ms>`
 * header carrying the controller's dispatch wall-clock time.
 *
 * The header is read by the storefront's `/api/graphql` proxy (which merges it
 * into its own Server-Timing) and is passed through nginx untouched, so the
 * browser's DevTools → Network → Timing tab shows Magento's processing time
 * next to the proxy→Magento round trip. The difference is the network hop.
 *
 * Cheap (a single header on an already-built response) and side-effect free, so
 * it is safe to leave always-on; it is only surfaced when the storefront's
 * GRAPHQL_PROFILE flag is set.
 *
 * An around plugin so the start time lives in a local variable: the plugin is
 * a shared instance, and under an application server (FrankenPHP worker mode)
 * a property set by one request would still be there for the next.
 */
class ServerTimingPlugin
{
    /**
     * Time the dispatch and stamp the elapsed time onto the response.
     */
    public function aroundDispatch(
        FrontControllerInterface $subject,
        callable $proceed,
        RequestInterface $request
    ): ResponseInterface {
        $startedAt = microtime(true);
        $result = $proceed($request);
        if ($result instanceof HttpResponse) {
            $ms = (microtime(true) - $startedAt) * 1000.0;
            $result->setHeader(
                'Server-Timing',
                sprintf('magento-app;desc="magento dispatch";dur=%.1f', $ms),
                true
            );
        }
        return $result;
    }
}
