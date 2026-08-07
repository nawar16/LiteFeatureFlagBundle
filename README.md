# Lite Feature Flag Bundle


![CI](https://github.com/nawar16/LiteFeatureFlagBundle/actions/workflows/ci.yml/badge.svg)


A lightweight, self-hosted, Symfony Feature Flag bundle focused on developer workflow, not enterprise flag management

## Performance & Cache Lifecycle

The bundle uses Symfony's native Dependency Injection container tracking:
* **Development (dev):** Changes to `lite_feature_flag_bundle.yaml` are caught instantly via container monitoring. No manual cache required.
* **Production (prod):** Configurations compile statically into memory during `php bin/console cache:warmup`,ensuring maximum performance with zero runtime disk I/O overhead.


Note: Environment variables bypass this completely and take immediate effect at runtime without requiring a cache clear.
