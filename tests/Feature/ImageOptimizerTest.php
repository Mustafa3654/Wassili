<?php

namespace Tests\Feature;

use App\Support\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    /** Build a real JPEG on disk so GD has actual pixels to work with. */
    private function jpeg(int $width, int $height): UploadedFile
    {
        $img = imagecreatetruecolor($width, $height);

        // Some colour variation, otherwise a flat image compresses so well the
        // size assertions prove nothing.
        for ($i = 0; $i < 400; $i++) {
            $colour = imagecolorallocate($img, random_int(0, 255), random_int(0, 255), random_int(0, 255));
            imagefilledellipse($img, random_int(0, $width), random_int(0, $height), 120, 120, $colour);
        }

        $path = tempnam(sys_get_temp_dir(), 'src').'.jpg';
        imagejpeg($img, $path, 95);
        imagedestroy($img);

        return new UploadedFile($path, 'photo.jpg', 'image/jpeg', null, true);
    }

    public function test_upload_is_converted_to_webp_and_shrunk(): void
    {
        Storage::fake('public');

        $source = $this->jpeg(3000, 2000);
        $originalBytes = $source->getSize();

        $path = ImageOptimizer::storeWebp($source, 'products');

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        $bytes = strlen(Storage::disk('public')->get($path));
        $this->assertLessThan($originalBytes, $bytes, 'WebP should be smaller than the source JPEG');

        // Longest edge capped.
        $info = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame(ImageOptimizer::MAX_PHOTO, max($info[0], $info[1]));
        $this->assertSame(IMAGETYPE_WEBP, $info[2]);
    }

    public function test_logos_are_capped_smaller_than_photos(): void
    {
        Storage::fake('public');

        $path = ImageOptimizer::storeWebp($this->jpeg(1200, 1200), 'vendors', ImageOptimizer::MAX_LOGO);
        $info = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame(ImageOptimizer::MAX_LOGO, max($info[0], $info[1]));
    }

    /** An image already smaller than the cap must not be blown up. */
    public function test_small_images_are_not_enlarged(): void
    {
        Storage::fake('public');

        $path = ImageOptimizer::storeWebp($this->jpeg(200, 150), 'products');
        $info = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame([200, 150], [$info[0], $info[1]]);
    }
}
