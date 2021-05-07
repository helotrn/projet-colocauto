<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Repositories\FileRepository;
use App\Http\Requests\BaseRequest as Request;
use Illuminate\Http\JsonResponse;
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
            switch ($file->getError()) {
                case \UPLOAD_ERR_INI_SIZE:
                case \UPLOAD_ERR_FORM_SIZE:
                    $maxUpload = (int) ini_get('upload_max_filesize');
                    $maxPost = (int) ini_get('post_max_size');
                    $maxFileSize = min($maxUpload, $maxPost);
                    return $this->respondWithMessage(
                        "La taille du fichier dépasse la limite configurée à $maxFileSize Mo",
                        422
                    );
                case \UPLOAD_ERR_PARTIAL:
                case \UPLOAD_ERR_NO_FILE:
                    return $this->respondWithMessage(
                        "Le fichier n'a pas été reçu correctement. Veuillez réessayer.",
                        422
                    );
                case \UPLOAD_ERR_NO_TMP_DIR:
                case \UPLOAD_ERR_CANT_WRITE:
                case \UPLOAD_ERR_EXTENSION:
                    return $this->respondWithMessage(
                        "Erreur serveur lors de l'enregistrement du fichier.",
                        500
                    );
                default:
                    return $this->respondWithMessage('Fichier invalide.', 422);
            }
        }

        $fileData = $this->upload($file, $field);

        if (is_a($fileData, JsonResponse::class)) {
            return $fileData;
        }

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
            'path' => $uri,
            'original_filename' => $originalFilename,
            'filename' => $filename,
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
