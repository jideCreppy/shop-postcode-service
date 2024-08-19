<?php

namespace App\Console\Commands;

use App\Models\Postcode;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;
use ZipArchive;

use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\warning;

class PostCodeDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postcode:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and download postcode data';

    /**
     * Execute the console command to download and store postcodes.
     */
    public function handle(): void
    {
        $progress = progress(label: 'Downloading postcodes...', steps: 2);
        $progress->start();

        /**
         * Further improvements could be to move larger file processing to jobs and/or
         * schedule this as a command.
         *
         * Also, a final clean up operation to delete processed files.
         */
        $fileName = Str(config('services.postcode.data.url'))->afterLast('/');
        $finalPath = storage_path().'/app/public/postcode-data-'.$fileName;

        // Using smaller downloaded file to parse and store in the database.
        $csvPath = storage_path().'/app/public/postcodes/Data/multi_csv/ONSPD_NOV_2022_UK_AB.csv';

        try {
            $progress->advance();
            $this->beginDownload($finalPath);
        } catch (Exception $e) {
            warning('Failed downloading postcode data'.$e->getMessage());
            exit;
        }

        $progress->advance();

        SimpleExcelReader::create($csvPath)
            ->getRows()
            ->skip(1)
            ->values()
            ->chunk(1)
            ->each(function ($rows) { // Account for memory usage performance. Hence, use Lazy collections.
                $postcodes = [];
                $today = now();

                foreach ($rows as $key => $value) {
                    $postcodes[$key]['postcode'] = $value['pcd2'];
                    $postcodes[$key]['geo_coordinates'] = DB::raw("ST_SRID(POINT({$value['lat']}, {$value['long']}), 4326)");
                    $postcodes[$key]['created_at'] = $today;
                }

                Postcode::insertOrIgnore($postcodes);
            });

        $progress->finish();

        outro('Operation Complete! 🚀');
    }

    /**
     * Copy the target postcode archive to the storage directory.
     */
    public function beginDownload(string $finalPath): void
    {
        copy(config('services.postcode.data.url'), $finalPath);

        $archiveHandler = new ZipArchive;
        $zippedFile = $archiveHandler->open($finalPath);

        if ($zippedFile === true) {
            $archiveHandler->extractTo(storage_path().'/app/public/postcodes/');
            $archiveHandler->close();
            info('Unzip operation successful!');
        } else {
            warning('Unzip operation failed.');
            exit;
        }
    }
}
