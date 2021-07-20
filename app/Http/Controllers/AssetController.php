<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Asset;
use App\Repositories\FileRepository;
use App\Repositories\ImageRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Laravel\Passport\Token;
use Storage;

class AssetController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $model;
    protected $imageRepo;
    protected $fileRepo;

    public function __construct(
        Asset $model,
        ImageRepository $imageRepo,
        FileRepository $fileRepo
    ) {
        $this->model = $model;
        $this->imageRepo = $imageRepo;
        $this->fileRepo = $fileRepo;
    }

    public function userFile(Request $request, $rest)
    {
        $token = $request->get("token");

        $asset = $this->findAsset($rest, "user");
        $newRequest = $this->buildIntermediateRequest($token, $asset);

        switch ($asset->type) {
            case "image":
                [$path, $filename] = explode("/", $rest);
                $image = $this->imageRepo->find(
                    $newRequest,
                    $asset->foreign_id
                );
                $imageFile = $image->fetch("$image->path/$filename");
                return response($imageFile->stream())->header(
                    "Content-type",
                    $imageFile->mime
                );
                break;
            default:
                return abort(404);
        }
    }

    public function communityUserFile(Request $request, $rest)
    {
        $token = $request->get("token");

        $asset = $this->findAsset($rest, "communityuser");
        $newRequest = $this->buildIntermediateRequest($token, $asset);

        switch ($asset->type) {
            case "image":
                $image = $this->imageRepo->find(
                    $newRequest,
                    $asset->foreign_id
                );
                $imageFile = $image->fetch("$image->path/$image->filename");
                return response($imageFile->stream())->header(
                    "Content-type",
                    $imageFile->mime
                );
                break;
            default:
                return abort(404);
        }
    }

    public function borrowerFile(Request $request, $rest)
    {
        $token = $request->get("token");

        $asset = $this->findAsset($rest, "borrower");
        $newRequest = $this->buildIntermediateRequest($token, $asset);

        switch ($asset->type) {
            case "file":
                $file = $this->fileRepo->find($newRequest, $asset->foreign_id);
                $fileFile = $file->fetch("$file->path/$file->filename");
                if (!$fileFile) {
                    return abort(404);
                }
                return Storage::download("$file->path/$file->filename");
                break;
            default:
                return abort(404);
        }
    }

    public function exportFile(Request $request, $rest)
    {
        return response()
            ->download(storage_path("app/exports/$rest"), $rest, [
                "Content-Type" => "text/csv",
            ])
            ->deleteFileAfterSend();
    }

    private function findAsset($rest, $slug)
    {
        [$path, $filename] = explode("/", $rest . "/");

        $fullPath = "/$slug/" . $path;

        return $this->model
            ->wherePath($fullPath)
            ->where(function ($q) use ($filename) {
                [$size, $filenameWithoutSize] = explode(
                    "_",
                    $filename . "_",
                    2
                );

                return $q
                    ->whereFilename($filename)
                    ->orWhere(
                        "filename",
                        preg_replace('/_$/', "", $filenameWithoutSize)
                    );
            })
            ->firstOrFail();
    }

    private function buildIntermediateRequest($token, $userAsset)
    {
        $tokenObject = Token::findOrFail($token);
        $newRequest = new Request();
        $newRequest->setUserResolver(function () use ($tokenObject) {
            return $tokenObject->user;
        });
        $newRequest->merge([
            "field" => $userAsset->field,
        ]);

        return $newRequest;
    }
}
