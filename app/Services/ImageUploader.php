<?php

namespace App\Services;

class ImageUploader
{
    /**
     * Upload an array of images and return their stored paths.
     * Strings are kept as-is (e.g. for already uploaded images on update).
     *
     * @param array $images
     * @param string $directory
     * @param string $disk
     * @return array
     */
    public function uploadMultiple(array $images, string $directory = 'packages', string $disk = 'public'): array
    {
        $imagePaths = [];
        foreach ($images as $image) {
            if (is_file($image)) {
                $imagePaths[] = $image->store($directory, $disk);
            } elseif (is_string($image)) {
                $imagePaths[] = $image;
            }
        }
        return $imagePaths;
    }
}
