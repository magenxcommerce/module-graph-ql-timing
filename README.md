# Magenx_GraphQlTiming

Adds a `Server-Timing` response header to every GraphQL response carrying the
GraphQL controller's dispatch wall-clock time:

```
Server-Timing: magento-app;desc="magento dispatch";dur=42.0
```

## Why

The headless storefront couldn't tell whether slow GraphQL requests were the
network or Magento itself. This module surfaces Magento's own processing time so
the storefront's `/api/graphql` proxy can merge it into the `Server-Timing`
header the browser sees (DevTools → Network → Timing). The difference between the
proxy's `proxy-upstream` figure and this `magento-app` figure is the network hop.

It is a single header on an already-built response — cheap, side-effect free, and
safe to leave enabled. It's only *surfaced* when the storefront's
`GRAPHQL_PROFILE` flag is on.

## Install

```
bin/magento module:enable Magenx_GraphQlTiming
bin/magento setup:upgrade
```

## Deeper drill-down

For per-resolver / per-DB-query timing (not just the total), enable Magento's
built-in profiler:

```
bin/magento dev:profiler:enable html      # or set MAGE_PROFILER in app/etc/env.php
```
