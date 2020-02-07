<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Repositories\FileRepository;
use App\Http\Requests\BaseRequest as Request;
use Illuminate\Support\Facades\File as IlluminateFile;

class FileController extends RestController
{
    protected $fileableId;

    protected $repo;

    protected $types = ['user'];

    public function __construct(FileRepository $file) {
        $this->repo = $file;
    }

    public function create(Request $request) {
        $field = $request->input('field');
        $file = $request->file($field);

        if (is_array($file)) {
            $file = array_pop($file);
        }

        if (!$file || !$file->isValid()) {
            abort(400);
        }

        $fileData = $this->upload($file, $field);

        if (!$fileData) {
            abort(400);
        }

        return $this->repo->create($fileData);
    }

    public function update(Request $request, $id) {
        abort(405);
    }

    public function delete(Request $request, $id) {
        $file = $this->repo->delete($id);
        $path = $file->path;
        $filename = $file->filename;

        IlluminateFile::delete(storage_path() . $path . DIRECTORY_SEPARATOR . $filename);

        return $file;
    }

    protected function upload($file, $field) {
        $uniq = uniqid();
        $uri = "/tmp/$uniq";

        $originalFilename = $file->getClientOriginalName();
        $filename = $this->cleanupFilename($originalFilename);

        $location = File::store($uri . DIRECTORY_SEPARATOR . $filename, $file);

        $request = new Request();
        $request->merge([
            'path' => '/' . dirname($location),
            'original_filename' => $originalFilename,
            'filename' => basename($location),
            'field' => $field,
            'filesize' => $file->getSize(),
        ]);

        return $request->input();
    }

    protected function cleanupFilename($filename) {
        $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
        $filename = mb_ereg_replace("([\.]{2,})", '', $filename);
        return $filename;
    }
}
