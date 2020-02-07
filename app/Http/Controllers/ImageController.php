<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Repositories\ImageRepository;
use App\Http\Requests\BaseRequest as Request;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager as ImageManager;

class ImageController extends FileController
{
    protected $imageableId;

    protected $types = ['user'];

    public function __construct(ImageRepository $image) {
        $this->repo = $image;
    }

    protected function upload($file, $field) {
        $uniq = uniqid();
        $uri = "/tmp/$uniq";

        $originalFilename = $file->getClientOriginalName();
        $filename = $this->cleanupFilename($originalFilename);

        $manager = new ImageManager(array('driver' => 'imagick'));
        try {
            $image = $manager->make($file);
        } catch (NotReadableException $e) {
            return null;
        }
        Image::store($uri . DIRECTORY_SEPARATOR . $filename, $image);

        $request = new Request();
        $request->merge([
            'path' => $uri,
            'original_filename' => $originalFilename,
            'filename' => $filename,
            'width' => $image->width(),
            'height' => $image->height(),
            'field' => $field,
            'filesize' => $image->filesize(),
        ]);

        return $request->input();
    }
}
