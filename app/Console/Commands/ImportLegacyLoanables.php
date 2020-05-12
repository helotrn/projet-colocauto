<?php

namespace App\Console\Commands;

use App\Models\Bike;
use App\Models\Car;
use App\Models\File;
use App\Models\Image;
use App\Models\Owner;
use App\Models\Trailer;
use App\Models\User;
use Illuminate\Console\Command;
use Intervention\Image\ImageManager as ImageManager;

class ImportLegacyLoanables extends Command
{
    protected $signature = 'import:legacy:loanables '
        . '{filename : The TSV filename} '
        . '{usersFilename : The users TSV filename}'
        . '{folder : The folder with the images}';

    protected $description = 'Import legacy loanables from a provided TSV file';

    protected $filename;
    protected $usersFilename;
    protected $folder;

    private $file;
    private $usersFile;
    private $userEmailsById = [];

    public function handle() {
        $this->filename = $this->argument('filename');
        $this->usersFilename = $this->argument('usersFilename');
        $this->folder = $this->argument('folder');

        $this->info('Reading files...');
        $this->file = fopen(base_path() . "/{$this->filename}", 'r');
        $this->usersFile = fopen(base_path() . "/{$this->usersFilename}", 'r');

        if (!$this->file) {
            $this->error('File not found');
            return;
        }

        if (!$this->usersFile) {
            $this->error('Users file not found');
            return;
        }

        $this->info('Fetching users...');
        fgetcsv($this->usersFile, null, "\t"); // Headers
        while ($line = fgetcsv($this->usersFile, null, "\t")) {
            $this->userEmailsById[intval($line[0])] = trim($line[5]);
        }

        $this->info('Syncing loanables...');
        $headers = fgetcsv($this->file, null, "\t");
        while ($line = fgetcsv($this->file, null, "\t")) {
            if (strtolower($line[0]) !== 'voiture') {
                continue;
            }
            $line = array_map('trim', $line);
            //$this->echoLine($headers, $line);

            switch (strtolower($line[0])) {
                case 'voiture':
                    $itemType = 'car';

                    $this->warn("Working on car {$line[2]}...");

                    $data = [
                        'name' => $line[2],
                        'position' => $line[5],
                        'location_description' => $line[6],
                        'instructions' => $line[18],
                        'comments' => $line[14],
                        'brand' => $line[7],
                        'model' => $line[8],
                        'plate_number' => $line[9],
                        'year_of_circulation' => $line[10],
                        'transmission_mode' => preg_match('/automati/i', $line[12])
                            ? 'automatic'
                            : 'manual',
                        'engine' => 'fuel',
                        'papers_location' => preg_match('/voiture/i', $line[15])
                            ? 'in_the_car'
                            : 'to_request_with_car',
                        'insurer' => $line[16],
                    ];

                    $car = Car::where('name', $line[2])->first();
                    if (!$car) {
                        $car = new Car;
                    }
                    $car->fill($data);
                    $car->save();

                    $item = $car;
                    break;
                case 'vélo':
                    $itemType = 'bike';

                    $this->warn("Working on bike {$line[2]}...");

                    $data = [
                        'name' => $line[2],
                        'position' => $line[5],
                        'location_description' => $line[6],
                        'instructions' => '',
                        'comments' => $line[18],
                        'model' => $line[7],
                        'size' => 'medium',
                    ];

                    $bike = Bike::where('name', $line[2])->first();
                    if (!$bike) {
                        $bike = new Bike;
                    }
                    $bike->fill($data);
                    $bike->save();

                    $item = $bike;
                    break;
                case 'remorque':
                    $itemType = 'trailer';

                    $this->warn("Working on trailer {$line[2]}...");

                    $data = [
                        'name' => $line[2],
                        'position' => $line[5],
                        'location_description' => $line[6],
                        'comments' => '',
                        'instructions' => '',
                        'maximum_charge' => $line[18],
                    ];

                    $trailer = Trailer::where('name', $line[2])->first();
                    if (!$trailer) {
                        $trailer = new trailer;
                    }
                    $trailer->fill($data);
                    $trailer->save();

                    $item = $trailer;
                    break;
                default:
                    die('not supported');
            }

            if (intval($line[3]) > 1) {
                $userEmail = $this->userEmailsById[$line[3]];
                $user = User::where('email', $userEmail)->first();

                if (!$user) {
                    $this->error("Could not find user with email $userEmail.");
                    die();
                }

                $owner = $user->owner;
                if (!$owner) {
                    $this->warn("Creating empty owner for user ID {$user->id}...");
                    $owner = new Owner;
                    $owner->user_id = $user->id;
                    $owner->save();
                }

                $item->owner_id = $owner->id;
            } else {
                // No owner, assuming always available
                $item->availability_mode = 'always';
            }
            $item->save();

            $this->warn("Working on $itemType {$line[1]} images...");

            $fixedFolder = str_replace('Voiture', 'Vehicule', $line[19]);
            $files = glob(base_path() . "/{$this->folder}/{$fixedFolder}_dossier_photo/*");

            if (empty($files)) {
                $this->error('No pictures found.');
            }

            foreach ($files as $path) {
                $name = basename($path);

                if (preg_match('/photo_vehicule/i', $name)) {
                    $uniq = uniqid();
                    $uri = "/$itemType/$uniq";

                    $filename = $this->cleanupFilename($name);

                    $manager = new ImageManager(array('driver' => 'imagick'));
                    $imageFile = $manager->make($path)->orientate();

                    $imageData = [
                        'path' => $uri,
                        'original_filename' => $name,
                        'filename' => $filename,
                        'width' => $imageFile->width(),
                        'height' => $imageFile->height(),
                        'field' => 'image',
                        'filesize' => $imageFile->filesize(),
                    ];

                    if (Image::where('filesize', $imageFile->filesize())->first()) {
                        $this->warn("{$name} appears to exist already: skippping...");
                        continue;
                    }

                    Image::store($uri . DIRECTORY_SEPARATOR . $filename, $imageFile);

                    $image = new Image;
                    $image->fill($imageData);
                    $image->imageable()->associate($item);
                    $image->save();
                } elseif (preg_match('/rapport_insp/i', $name)) {
                    $uniq = uniqid();
                    $uri = "/$itemType/$uniq";

                    $filename = $this->cleanupFilename($name);
                    $filesize = filesize($path);

                    $fileData = [
                        'path' => $uri,
                        'original_filename' => $name,
                        'filename' => $filename,
                        'field' => 'report',
                        'filesize' => $filesize,
                    ];

                    if (File::where('filesize', $filesize)->first()) {
                        $this->warn("{$name} appears to exist already: skippping...");
                        continue;
                    }

                    mkdir(storage_path('app') . $uri);
                    copy($path, storage_path('app') . $uri . DIRECTORY_SEPARATOR . $filename);

                    $file = new File;
                    $file->fill($fileData);
                    $file->fileable()->associate($item);
                    $file->save();
                } else {
                    $this->error("unknown file $name");
                    die();
                }
            }
        }

        $this->info('Done.');
    }

    private function echoLine($headers, $line) {
        foreach ($headers as $index => $title) {
            echo sprintf("%2d %-s: %s", $index, trim($title), $line[$index] ? $line[$index] : '×');
            echo "\n";
        }
        echo "===\n";
    }

    private function cleanupFilename($filename) {
        $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
        $filename = mb_ereg_replace("([\.]{2,})", '', $filename);
        return $filename;
    }
}
