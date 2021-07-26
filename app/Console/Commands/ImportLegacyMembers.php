<?php

namespace App\Console\Commands;

use App\Models\Borrower;
use App\Models\Community;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class ImportLegacyMembers extends Command
{
    private static $headers = [
        "id (ignored)",
        "last_name",
        "first_name",
        "date_of_birth",
        "phone",
        "email",
        "age (not used)",
        "address",
        "postal_code",
        'is_smart_phone ("oui" === true)',
        "description",
        "communities (comma-separated names)",
        "drivers_license_number",
    ];

    protected $signature = 'import:legacy:members {filename : The TSV filename}
                            {--pretend : Do not actually import anything}
                            {--debug : Print out more information}';

    protected $description = "Import legacy members from a provided TSV file";

    protected $filename;

    private $file;
    private $debug;
    private $pretend;
    private $communitiesByName = [];

    public function handle()
    {
        $this->debug = $this->option("debug");
        $this->pretend = $this->option("pretend");

        $this->filename = $this->argument("filename");

        $this->info("Reading file...");
        $this->file = fopen(base_path() . "/{$this->filename}", "r");

        if (!$this->file) {
            $this->error("File not found");
            return;
        }

        $this->info("Fetching communities...");
        $this->fetchCommunities();

        $this->info("Syncing users...");
        $headers = fgetcsv($this->file, null, "\t");
        if ($this->debug) {
            $this->echoLine(static::$headers, $headers);
        }

        while ($line = fgetcsv($this->file, null, "\t")) {
            $line = array_map("trim", $line);
            if ($this->debug) {
                $this->echoLine($headers, $line);
            }

            if (!filter_var($line[5], FILTER_VALIDATE_EMAIL)) {
                $this->error(
                    "Invalid email {$line[5]} for user {$line[0]} {$line[1]}. Skipping..."
                );
                continue;
            }

            $data = [
                "last_name" => $line[1],
                "name" => $line[2],
                "date_of_birth" => \Carbon\Carbon::createFromFormat(
                    "d/m/Y H:m:i",
                    $line[3] . " 00:00:00"
                ),
                "phone" => $line[4],
                "email" => $line[5],
                "address" => $line[7],
                "postal_code" => $line[8],
                "is_smart_phone" => strtolower($line[9]) === "oui",
                "description" => $line[10],
                "communities" => $this->getUserCommunities($line[11]),
                "borrower" => [
                    "drivers_license_number" => $line[12],
                ],
            ];

            $this->warn("Working on {$line[2]} {$line[1]}...");

            $validator = Validator::make(
                $data,
                User::getRules("create"),
                User::$validationMessages
            );

            if ($validator->fails()) {
                $this->error("Invalid member {$line["id"]}: skipping...");
                continue;
            }

            if ($this->pretend) {
                continue;
            }

            $user = User::where("email", $data["email"])->first();

            if (!$user) {
                $user = new User();
            }
            $user->fill($data);
            $user->email = $data["email"];
            $user->password = base64_encode(random_bytes(32));
            $user->save();

            $borrower = $user->borrower;
            if (!$borrower) {
                $borrower = new Borrower();
            }

            $borrower->fill($data["borrower"]);
            $borrower->user()->associate($user);
            $borrower->save();

            $user->communities()->syncWithoutDetaching($data["communities"]);
        }

        $this->info("Done.");
    }

    private function echoLine($headers, $line)
    {
        foreach ($headers as $index => $title) {
            echo sprintf(
                "%2d %-s: %s",
                $index,
                trim($title),
                $line[$index] ? $line[$index] : "×"
            );
            echo "\n";
        }
        echo "===\n";
    }

    private function fetchCommunities()
    {
        $communities = Community::all();

        foreach ($communities as $community) {
            $this->communitiesByName[$community->name] = $community;
        }
    }

    private function getUserCommunities(string $names): array
    {
        $parts = array_map("trim", explode(",", str_replace("et", "", $names)));

        $output = [];

        foreach ($parts as $name) {
            if ($name === "") {
                continue;
            }

            if (!isset($this->communitiesByName[$name])) {
                $this->error("Community not found : $name");
                die();
            }

            $output[$this->communitiesByName[$name]->id] = [
                "approved_at" => new \DateTime(),
            ];
        }

        if (empty($output)) {
            $this->warn("Not associated to any community");
        }

        return $output;
    }
}
