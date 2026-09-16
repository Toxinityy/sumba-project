<?php

namespace App\Console\Commands;

use App\Services\Images\ImageCapabilities;
use Illuminate\Console\Command;

class ImageCapabilitiesReport extends Command
{
    protected $signature = 'images:capabilities';
    protected $description = 'Report which image formats this host can encode';

    public function handle(ImageCapabilities $capabilities): int
    {
        $this->table(
            ['Format', 'Encodable'],
            collect($capabilities->report())
                ->map(fn (bool $ok, string $format) => [$format, $ok ? 'yes' : 'no'])
                ->values()
                ->all()
        );

        $this->line('Chain: ' . implode(' -> ', $capabilities->bestChain()));

        return self::SUCCESS;
    }
}
