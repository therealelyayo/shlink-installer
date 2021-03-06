<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Installer\Config\Option;

use Shlinkio\Shlink\Config\Collection\PathCollection;
use Symfony\Component\Console\Style\StyleInterface;

class DatabaseHostConfigOption extends AbstractNonSqliteDependentConfigOption
{
    public function getConfigPath(): array
    {
        return ['entity_manager', 'connection', 'host'];
    }

    public function ask(StyleInterface $io, PathCollection $currentOptions): string
    {
        return $io->ask('Database host', 'localhost');
    }
}
