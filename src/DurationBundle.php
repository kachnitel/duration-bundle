<?php

namespace Kachnitel\DurationBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DurationBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
