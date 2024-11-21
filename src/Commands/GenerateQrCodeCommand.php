<?php

namespace LaraBoy\LaraQrcode\Commands;

use Illuminate\Console\Command;
use LaraBoy\LaraQrcode\Facades\QrCode;

class GenerateQrCodeCommand extends Command
{
    protected $signature = 'qrcode:generate {data} {--path=storage/qrcodes/qrcode.png}';
    protected $description = 'Generate a QR code and save it to a file.';

    public function handle()
    {
        $data = $this->argument('data');
        $path = $this->option('path');

        $qrcode = QrCode::generate($data);
        file_put_contents($path, $qrcode);

        $this->info("QR code generated and saved to {$path}");
    }
}
