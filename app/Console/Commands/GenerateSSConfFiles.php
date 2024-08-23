<?php

namespace App\Console\Commands;

use App\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSSConfFiles extends Command
{
    const SS_CHECK_DIR = 'ss-tocheck/';

    protected $signature = 'ss-config:generate-files';

    protected $description = 'Generate everyday SS Conf Files in format ip.ip.ip.ip.ss';

    public function handle()
    {
        $servers = Server::where('ss_config','!=' ,'')->whereNotNull('ss_config')->get();

        if (Storage::disk('storage')->exists(self::SS_CHECK_DIR)) {
            Storage::disk('storage')->deleteDirectory(self::SS_CHECK_DIR);
        }
        Storage::disk('storage')->makeDirectory(self::SS_CHECK_DIR);

        foreach ($servers as $server) {
            $filename = self::SS_CHECK_DIR . $server->ip . '.ss';
            Storage::disk('storage')->put($filename,$server->ss_config);
        }

    }
}
