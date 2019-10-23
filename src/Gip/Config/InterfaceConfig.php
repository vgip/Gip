<?php
/**
 * Interface to Config classes
 */

declare(strict_types = 1);

namespace Vgip\Gip\Config;

use Vgip\Gip\Config\Storage\Storage;

interface InterfaceConfig
{
    public function setAll($storage);
    public function getAll();
}

