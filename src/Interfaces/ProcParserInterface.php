<?php

namespace MrCrankHank\IetParser\Interfaces;

use League\Flysystem\FilesystemInterface;

interface ProcParserInterface
{
    public function __construct(FilesystemInterface $filesystem, $filePath, $target = null);

    public function getSession($target = false);

    public function getVolume($target = false);

    public function setTidIndex($tidIndex);

    public function getTidIndex();
}