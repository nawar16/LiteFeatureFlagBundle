<?php

namespace Nawar16\LiteFeatureFlagBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Feature
{
    public const STRATEGY_EXCEPTION = 'exception';
    public const STRATEGY_FALLBACK = 'fallback';
    public function __construct(
        public string $name,
        public string $disabled = self::STRATEGY_EXCEPTION,
        public ?string $fallback = null
    ) {}
}
