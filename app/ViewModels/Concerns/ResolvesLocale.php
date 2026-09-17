<?php

namespace App\ViewModels\Concerns;

/**
 * FIXTURE — awaiting replacement once the data layer resolves translatable
 * fields itself (see "Localisation" in docs/data-contract.md: every string
 * in the contract arrives already resolved for app()->getLocale()). Shared
 * by every ViewModel in this namespace so each one only states the two
 * strings, not the branching.
 */
trait ResolvesLocale
{
    protected static function pick(string $id, string $en): string
    {
        return app()->getLocale() === 'en' ? $en : $id;
    }
}
