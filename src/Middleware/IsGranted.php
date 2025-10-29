<?php

namespace App\Middleware;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class IsGranted
{
    public function __construct(public ?string $role = null) {}
}
