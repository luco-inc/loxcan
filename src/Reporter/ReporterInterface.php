<?php

declare(strict_types=1);

namespace Siketyan\Loxcan\Reporter;

use Siketyan\Loxcan\Model\DependencyCollectionDiff;

interface ReporterInterface
{
    public function report(DependencyCollectionDiff $diff): void;
    public function supports(): bool;
}
