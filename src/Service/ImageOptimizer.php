<?php

namespace App\Service;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageOptimizer
{
    private const MAX_WIDTH = 200;
    private const MAX_HEIGHT = 150;

    /**
     * @var Imagine
     */
    private Imagine $imagine;

    public function __construct ()
    {
        $this->imagine = new Imagine();
    }

    public function resize (string $fileName): void
    {
        list($iWidth, $iHeight) = getimagesize($fileName);
        $ratio = $iWidth / $iHeight;
        $width = self::MAX_WIDTH;
        $height = self::MAX_HEIGHT;

        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width * $ratio;
        }

        $photo = $this->imagine->open($fileName);
        $photo->resize(new Box($width, $height))->save($fileName);
    }
}
