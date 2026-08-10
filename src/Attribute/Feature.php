<?php

namespace Nawar16\LiteFeatureFlagBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Feature
{
    public function __construct(public string $name) 
    {}
}
