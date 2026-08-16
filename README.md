# Lite Feature Flag Bundle


![CI](https://github.com/nawar16/LiteFeatureFlagBundle/actions/workflows/ci.yml/badge.svg)


A lightweight, self-hosted, Symfony Feature Flag bundle focused on developer workflow, not enterprise flag management

## Performance & Cache Lifecycle

The bundle uses Symfony's native Dependency Injection container tracking:
* **Development (dev):** Changes to `lite_feature_flag_bundle.yaml` are caught instantly via container monitoring. No manual cache required.
* **Production (prod):** Configurations compile statically into memory during `php bin/console cache:warmup`,ensuring maximum performance with zero runtime disk I/O overhead.

Note: Environment variables bypass this completely and take immediate effect at runtime without requiring a cache clear.


## How it works

The bundle uses a smart background script (a Compiler Pass) to automatically protect the services without having to write any extra setup code:
* **Automatic Scanning:** When Symfony starts up, the bundle automatically scans the classes looking for the `#[Feature]` label.
* **The Magic Switch:** If it finds the label, it hides your real service behind a protective **Proxy** guard class using the exact same service id.
* **Smart Memory Saving:** It uses a Symfony closure trick to pass the real service as a recipe (`callable`). the heavy service is completely ignored and never created in memory unless the feature flag is turned ON and someone actually uses it.