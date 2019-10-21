<?php
/**
 * Interface to Config classes
 */

declare(strict_types = 1);

namespace Vgip\Gip\Config\Storage;

interface InterfaceStorage
{
    public function getObject(array $data = [], string $keyNamingType = '') : object;
}
