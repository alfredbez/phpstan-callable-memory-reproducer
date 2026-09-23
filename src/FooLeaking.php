<?php

declare(strict_types=1);

namespace App;

final class FooLeaking
{
    /**
     * Eager callable check in ConstantArrayType:
     * When PHPStan sees a 2-element array whose 1st element matches a declared class name
     * (here: 'BigContainer'), it reflects the full class AST via BetterReflection to check
     * if 'arbitrary_ident' is a method name.
     *
     * In real-world projects with compiled Symfony containers or large generated classes,
     * this causes hundreds of MBs (or 1+ GB) of memory consumption for a simple string list.
     */
    private const array ALLOWED_MODALITIES = [
        'BigContainer',
        'arbitrary_ident',
    ];

    public function isAllowed(string $item): bool
    {
        return in_array($item, self::ALLOWED_MODALITIES, true);
    }
}
