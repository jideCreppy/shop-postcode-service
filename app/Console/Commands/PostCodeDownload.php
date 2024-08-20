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
     * Execute console command to download, process and store postcodes.
     */
    public function handle(): void
    {
        $progress = progress(label: 'Downloading postcodes...', steps: 2);
        $progress->start();

        $archiveFileName = Str(config('services.postcode.data.url'))->afterLast('/');
        $archiveFinalPath = storage_path().'/app/public/postcode-data-'.$archiveFileName;

        $progress->advance();

        $this->beginDownload($archiveFinalPath);

        // Using a smaller downloaded file sample to parse and store postcodes in the database for the purpose of demonstration.
        $csvPath = storage_path().'/app/public/postcodes/Data/multi_csv/ONSPD_NOV_2022_UK_AB.csv';

        $progress->advance();

        // Account for memory usage and performance. Hence, use Lazy collections and data chunks to assist with DB inserts.
        SimpleExcelReader::create($csvPath)
            ->getRows()
            ->skip(1)
            ->values()
            ->chunk(200)
            ->each(function ($rows) {
                $postcodes = [];
                $createdDate = now();

                foreach ($rows as $key => $row) {
                    $postcodes[$key]['postcode'] = htmlspecialchars($row['pcd'], ENT_QUOTES, 'UTF-8', false);
                    $postcodes[$key]['geo_coordinates'] = DB::raw("ST_SRID(POINT({$row['lat']}, {$row['long']}), 4326)");
                    $postcodes[$key]['created_at'] = $createdDate;
                }

                Postcode::insertOrIgnore($postcodes);
            });

        $progress->finish();

        outro('Operation Complete! 🚀');
    }

    /**
     * Copy the target postcode archive to the storage directory.
     */
    public function beginDownload(string $archiveFinalPath): void
    {
        try {
            copy(config('services.postcode.data.url'), $archiveFinalPath);

            $archiveHandler = new ZipArchive;

            $zippedFile = $archiveHandler->open($archiveFinalPath);

            if ($zippedFile === true) {
                $archiveHandler->extractTo(storage_path().'/app/public/postcodes/');
                $archiveHandler->close();
                info('Unzip operation successful!');
            } else {
                warning('Unzip operation failed.');
                exit;
            }
        } catch (Exception $e) {
            warning('Failed downloading postcode data. Error:'.$e->getMessage());
            exit;
        }
    }
}
