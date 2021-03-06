<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Installer\Config;

use Shlinkio\Shlink\Config\Collection\PathCollection;
use Symfony\Component\Console\Style\StyleInterface;

interface ConfigGeneratorInterface
{
    public function generateConfigInteractively(StyleInterface $io, array $previousConfig): PathCollection;
}
