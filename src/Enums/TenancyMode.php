<?php

namespace Wezlo\FilamentLookups\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TenancyMode: string implements HasColor, HasLabel
{
    case Shared = 'shared';
    case Tenant = 'tenant';
    case Both = 'both';

    public function getLabel(): string
    {
        return match ($this) {
            self::Shared => __('filament-lookups::lookups.tenancy_mode.shared'),
            self::Tenant => __('filament-lookups::lookups.tenancy_mode.tenant'),
            self::Both => __('filament-lookups::lookups.tenancy_mode.both'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Shared => 'info',
            self::Tenant => 'warning',
            self::Both => 'success',
        };
    }
}
