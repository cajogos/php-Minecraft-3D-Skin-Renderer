<?php

/**
 * Class MinecraftSkinRenderer
 */
class MinecraftSkinRenderer
{
    const SECONDS_TO_CACHE = 604800; // Cache for 7 days
    const FALLBACK_IMAGE = 'notfound.png';

    private $width;
    private $height;

    /**
     * @param string $username
     * @return resource
     */
    public static function getSkinImageByUsername($username = 'cajogos')
    {
        $url = 'http://s3.amazonaws.com/MinecraftSkins/' . $username . '.png';
        if (trim($username) == '')
        {
            $img_png = imagecreatefrompng(self::FALLBACK_IMAGE);
        }
        else
        {
            $img_png = imagecreatefrompng($url);
        }

        if (!$img_png)
        {
            $img_png = imagecreatefrompng(self::FALLBACK_IMAGE);
        }
        imagealphablending($img_png, true);
        imagesavealpha($img_png, true);
        return $img_png;
    }
}