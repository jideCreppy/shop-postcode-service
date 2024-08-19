<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
     * Execute the console command.
     */
    public function handle(): BinaryFileResponse
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'postcode_data_2022-11.zip');
        copy(config('services.postcode.data.url'), $tempFile);

        return response()->download($tempFile, 'postcode_data_2022-11.zip');
    }
}
