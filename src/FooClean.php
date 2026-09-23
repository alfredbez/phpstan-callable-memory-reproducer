<?php

declare(strict_types=1);

namespace App;

final class FooClean
{
    /**
     * Identical values, but reversed order:
     * 1st element ('arbitrary_ident') does not match any declared class.
     * PHPStan short-circuits without reflecting 'BigContainer', consuming ~50 MB instead of ~500 MB.
     */
    private const array ALLOWED_MODALITIES = [
        'arbitrary_ident',
        'BigContainer',
    ];

    public function isAllowed(string $item): bool
    {
        return in_array($item, self::ALLOWED_MODALITIES, true);
    }
}
