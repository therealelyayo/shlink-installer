<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Installer\Config\Option;

use Shlinkio\Shlink\Config\Collection\PathCollection;
use Symfony\Component\Console\Style\StyleInterface;

class DatabaseMySqlOptionsConfigOption extends AbstractDriverDependentConfigOption
{
    public function getConfigPath(): array
    {
        return ['entity_manager', 'connection', 'driverOptions'];
    }

    public function ask(StyleInterface $io, PathCollection $currentOptions): array
    {
        return [
            // PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            1002 => 'SET NAMES utf8',
        ];
    }

    protected function shouldBeAskedForDbDriver(string $dbDriver): bool
    {
        return $dbDriver === DatabaseDriverConfigOption::MYSQL_DRIVER;
    }
}
