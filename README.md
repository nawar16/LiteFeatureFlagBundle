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
* **Smart Memory Saving:** It strictly enforces a dynamic callback architecture. The real service is passed as a lazy-loading closure (`callable`) . the heavy underlying class is completely ignored and never created in memory unless the feature flag is turned ON and someone actually uses it.

## How to use

Simply mark any service class with the `#[Feature]` attribute. The bundle handles all proxy generation automatically.

### 1. Default Exception Strategy
If the feature is disabled, calling any method on the service throws a `FeatureDisabledException`.

```php
use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;

#[Feature('new_payment_gateway')]
class ModernPaymentGateway 
{
    public function charge(): string 
    {
        return 'Charged successfully via Stripe';
    }
}
```
### 2. Fallback Strategy
Instead of throwing an exception, you can tell the proxy to execute a fallback method in the same class if the flag is turned off.

```php
use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;

#[Feature(
    name: 'beta_checkout', 
    disabled: Feature::STRATEGY_FALLBACK, 
    fallback: 'fallbackMethod'
)]
class CheckoutService 
{
    public function process(): string 
    {
        ...........
    }

    public function fallbackMethod(): string 
    {
        ..........
    }
}
```


### Creating a Custom Resolver
You can easily extend the bundle by writing custom domain logic (for example matching a runtime context) by implementing `FeatureResolverInterface` and tagging it:

```php
use Nawar16\LiteFeatureFlagBundle\Resolver\FeatureResolverInterface;
use Nawar16\LiteFeatureFlagBundle\Context\FeatureContext;

final class CustomTenantResolver implements FeatureResolverInterface
{
    public function resolve(string feature, FeatureContext context): ?bool 
    {
        //logic
        return null; //true/false to decide, or null to yield to lower priority resolvers
    }
    public function priority(): int 
    {
        return 100; // Ordered descending via Compiler Pass mapping
    }
}
```


## Testing Integration
The bundle includes a native test state store allowing you to force feature behaviors inside your test suites without container configuration manipulation

```php
use Nawar16\LiteFeatureFlagBundle\Testing\FeatureFlagTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CheckoutTest extends KernelTestCase
{
    use FeatureFlagTestTrait; //automatic clean up after each test method

    public function testNewCheckoutFlow(): void
    {
        \(this->enableFeature('beta_checkout');\)this->assertFeatureEnabled('beta_checkout');
        
        //execution behavior
    }
}
```

## CLI Tooling
Monitor the application flag states directly via the terminal:
```bash
# List all configured flags
php bin/console feature:list

# Inspect the state of a targeted flag
php bin/console feature:status beta_checkout
```
