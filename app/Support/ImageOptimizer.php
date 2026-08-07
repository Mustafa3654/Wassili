<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Converts uploaded images to WebP, downscaled and stripped of metadata.
 *
 * Menus here are photo-heavy and customers browse on phones over Lebanese
 * mobile data, so payload size matters more than pixel-perfect fidelity. A
 * 4 MB phone photo typically lands under 60 KB.
 *
 * Uses GD directly rather than pulling in an image library — GD ships with
 * PHP and is enabled on shared hosting, so there's nothing extra to install.
 * If WebP encoding is unavailable the original file is stored unchanged, so
 * uploads never fail because of this.
 */
class ImageOptimizer
{
    /** Longest edge, in pixels, for a product or vendor photo. */
    public const MAX_PHOTO = 900;

    /** Longest edge for a logo — they render at ~48px, so this is generous. */
    public const MAX_LOGO = 400;

    /** WebP quality. 70–80 is visually lossless for photography at these sizes. */
    public const QUALITY = 72;

    /**
     * Store an upload as WebP and return its path relative to the disk.
     */
    public static function storeWebp(
        UploadedFile $file,
        string $directory,
        int $maxEdge = self::MAX_PHOTO,
        string $disk = 'public'
    ): string {
        $name = Str::uuid()->toString();

        $image = self::open($file);

        // Anything GD can't decode (SVG, HEIC, a corrupt file) is stored as-is
        // rather than rejected.
        if ($image === null || ! function_exists('imagewebp')) {
            return $file->store($directory, $disk);
        }

        $image = self::fixOrientation($image, $file);
        $image = self::downscale($image, $maxEdge);

        $tmp = tempnam(sys_get_temp_dir(), 'webp');
        imagewebp($image, $tmp, self::QUALITY);
        imagedestroy($image);

        $path = trim($directory, '/').'/'.$name.'.webp';
        Storage::disk($disk)->put($path, file_get_contents($tmp));
        @unlink($tmp);

        return $path;
    }

    /** Decode the upload into a GD image, or null if unsupported. */
    protected static function open(UploadedFile $file): ?\GdImage
    {
        $path = $file->getRealPath();
        $type = @exif_imagetype($path) ?: null;

        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            IMAGETYPE_BMP  => @imagecreatefrombmp($path),
            default        => null,
        };

        return $image ?: null;
    }

    /**
     * Phone cameras record rotation in EXIF rather than rotating the pixels,
     * so without this, portrait photos upload sideways.
     */
    protected static function fixOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = $exif['Orientation'] ?? 1;

        $rotated = match ($orientation) {
            3       => imagerotate($image, 180, 0),
            6       => imagerotate($image, -90, 0),
            8       => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    /** Shrink so the longest edge fits $maxEdge. Never enlarges. */
    protected static function downscale(\GdImage $image, int $maxEdge): \GdImage
    {
        $width  = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= $maxEdge) {
            return $image;
        }

        $ratio = $maxEdge / $longest;
        $newWidth  = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Keep PNG transparency intact through the resize.
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }
}
