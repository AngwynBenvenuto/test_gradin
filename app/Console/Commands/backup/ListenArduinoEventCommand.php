<?php

// namespace App\Console\Commands;

// use App\Models\HarvestOrder;
// use App\Models\NfcCard;
// use Illuminate\Console\Command;
// use Symfony\Component\Process\Process;

// class ListenArduinoEventCommand extends Command
// {
//     protected $signature = 'arduino:reader
//         {--python=py : Python executable, contoh py atau C:\Python313\python.exe}
//         {--script= : Lokasi file arduino_serial_scan26_logger.py}
//         {--port=COM17 : Port Arduino}
//         {--baud=9600 : Baud rate Arduino}
//         {--restart-delay=3 : Jeda restart Python jika berhenti}';

//     protected $description =
//         'Menjalankan Python Serial Arduino dan lookup barcode/NFC ke database';

//     private ?Process $pythonProcess = null;

//     /** Sisa output Python yang belum membentuk satu baris utuh. */
//     private string $lineBuffer = '';

//     public function handle(): int
//     {
//         $script = $this->resolveScriptPath();

//         if (!is_file($script)) {
//             $this->error("File Python tidak ditemukan: {$script}");
//             return self::FAILURE;
//         }

//         /*
//          * Mencegah dua command membuka COM yang sama.
//          */
//         $lockPath = storage_path('app/arduino_reader.lock');
//         $lockHandle = fopen($lockPath, 'c');

//         if ($lockHandle === false) {
//             $this->error("Gagal membuat lock file: {$lockPath}");
//             return self::FAILURE;
//         }

//         if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
//             fclose($lockHandle);

//             $this->error(
//                 'Arduino reader sudah berjalan pada proses Laravel lain.'
//             );

//             return self::FAILURE;
//         }

//         register_shutdown_function(function (): void {
//             $this->stopPython();
//         });

//         $this->info('Arduino reader service dimulai.');
//         $this->line('Script : ' . $script);
//         $this->line('Port   : ' . $this->option('port'));
//         $this->line('Baud   : ' . $this->option('baud'));
//         $this->newLine();

//         try {
//             /*
//              * Jika Python berhenti karena error fatal, Laravel akan
//              * menjalankannya kembali.
//              */
//             while (true) {
//                 $exitCode = $this->runPython($script);

//                 $this->warn(
//                     "Python berhenti dengan exit code {$exitCode}."
//                 );

//                 $delay = max(
//                     1,
//                     (int) $this->option('restart-delay')
//                 );

//                 $this->line(
//                     "Menjalankan ulang dalam {$delay} detik..."
//                 );

//                 sleep($delay);
//             }
//         } finally {
//             $this->stopPython();

//             flock($lockHandle, LOCK_UN);
//             fclose($lockHandle);
//         }
//     }

//     private function resolveScriptPath(): string
//     {
//         $option = $this->option('script');

//         if (is_string($option) && trim($option) !== '') {
//             return $option;
//         }

//         /*
//          * Lokasi default:
//          * project-laravel/scripts/arduino_serial_scan26_logger.py
//          */
//         return base_path(
//             'scripts/arduino_logger.py'
//         );
//     }

//     private function buildPythonCommand(string $script): array
//     {
//         $python = (string) $this->option('python');

//         $command = [$python];

//         /*
//          * Windows Python Launcher memakai:
//          * py -3 script.py
//          *
//          * Jika --python menunjuk langsung ke python.exe,
//          * argumen -3 tidak digunakan.
//          */
//         $pythonName = strtolower(
//             basename(str_replace('\\', '/', $python))
//         );

//         if ($pythonName === 'py' || $pythonName === 'py.exe') {
//             $command[] = '-3';
//         }

//         $command[] = $script;
//         $command[] = '--port';
//         $command[] = (string) $this->option('port');
//         $command[] = '--baud';
//         $command[] = (string) $this->option('baud');

//         return $command;
//     }

//     private function runPython(string $script): int
//     {
//         $command = $this->buildPythonCommand($script);

//         $this->line(
//             '[PYTHON] Menjalankan: ' .
//             implode(' ', array_map(
//                 static fn (string $item): string =>
//                     str_contains($item, ' ')
//                         ? '"' . $item . '"'
//                         : $item,
//                 $command
//             ))
//         );

//         $this->lineBuffer = '';

//         $this->pythonProcess = new Process(
//             $command,
//             base_path()
//         );

//         /*
//          * Python harus berjalan terus.
//          */
//         $this->pythonProcess->setTimeout(null);
//         $this->pythonProcess->setIdleTimeout(null);

//         $exitCode = $this->pythonProcess->run(
//             function (string $type, string $output): void {
//                 if ($type === Process::ERR) {
//                     $this->processPythonErrorOutput($output);
//                     return;
//                 }

//                 $this->processPythonOutput($output);
//             }
//         );

//         /*
//          * Proses sisa karakter terakhir bila Python berhenti
//          * tanpa newline.
//          */
//         $remaining = trim($this->lineBuffer);

//         if ($remaining !== '') {
//             $this->handlePythonLine($remaining);
//         }

//         $this->lineBuffer = '';
//         $this->pythonProcess = null;

//         return $exitCode;
//     }

//     private function processPythonOutput(string $output): void
//     {
//         $this->lineBuffer .= $output;

//         while (
//             ($newlinePosition = strpos(
//                 $this->lineBuffer,
//                 "\n"
//             )) !== false
//         ) {
//             $line = substr(
//                 $this->lineBuffer,
//                 0,
//                 $newlinePosition
//             );

//             $this->lineBuffer = substr(
//                 $this->lineBuffer,
//                 $newlinePosition + 1
//             );

//             $line = trim($line);

//             if ($line !== '') {
//                 $this->handlePythonLine($line);
//             }
//         }
//     }

//     private function processPythonErrorOutput(string $output): void
//     {
//         foreach (preg_split('/\r\n|\r|\n/', $output) as $line) {
//             $line = trim($line);

//             if ($line !== '') {
//                 $this->warn('[PYTHON ERROR] ' . $line);
//             }
//         }
//     }

//     private function handlePythonLine(string $line): void
//     {
//         /*
//          * Pesan status Python.
//          */
//         if (
//             str_starts_with($line, '[INFO]') ||
//             str_starts_with($line, '[CONNECTED]')
//         ) {
//             $this->line($line);
//             return;
//         }

//         /*
//          * Data Wiegand 26 yang belum diputuskan jenisnya.
//          */
//         if (str_starts_with($line, 'SCAN26|')) {
//             $this->processScan26($line);
//             return;
//         }

//         /*
//          * Wiegand 34 atau barcode yang sudah pasti.
//          */
//         if (str_starts_with($line, 'BARCODE|')) {
//             $fields = $this->parseFields($line);
//             $barcode = $fields['value'] ?? '';

//             if ($barcode !== '') {
//                 $this->processBarcode($barcode);
//             }

//             return;
//         }

//         if (str_starts_with($line, 'PIN|')) {
//             $fields = $this->parseFields($line);
//             $pin = $fields['value'] ?? '';

//             if ($pin !== '') {
//                 $this->info("PIN: {$pin}");
//             }
//         }
//     }

//     private function processScan26(string $line): void
//     {
//         $fields = $this->parseFields($line);

//         $barcodeCandidate =
//             trim($fields['barcode_candidate'] ?? '');

//         $readerCardIdRaw =
//             $fields['reader_card_id'] ?? '';

//         $readerCardId =
//             $this->normalizeReaderCardId(
//                 $readerCardIdRaw
//             );

//         $readerCardIdDisplay =
//             $this->formatReaderCardIdDisplay(
//                 $readerCardId
//             );

//         /*
//          * Cari kandidat barcode terlebih dahulu.
//          * Semua tiket QR valid harus sudah ada di database.
//          */
//         $harvestOrder = null;

//         if ($barcodeCandidate !== '') {
//             $harvestOrder = HarvestOrder::query()
//                 ->where('no_tiket', $barcodeCandidate)
//                 ->first();
//         }

//         /*
//          * Cari reader_card_id NFC.
//          */
//         $nfcCard = null;

//         if ($readerCardId !== '') {
//             $nfcCard = NfcCard::query()
//                 ->where('reader_card_id', $readerCardId)
//                 ->first();
//         }

//         if ($harvestOrder && !$nfcCard) {
//             $this->info(
//                 "BARCODE: {$barcodeCandidate}"
//             );

//             $this->handleHarvestOrder($harvestOrder);
//             return;
//         }

//         if (!$harvestOrder && $nfcCard) {
//             $this->handleExistingNfc($nfcCard);
//             return;
//         }

//         if ($harvestOrder && $nfcCard) {
//             /*
//              * Nilai Wiegand kebetulan cocok dengan dua data.
//              * Jangan memilih otomatis.
//              */
//             $this->warn(
//                 'AMBIGU: barcode=' .
//                 $barcodeCandidate .
//                 ', reader_card_id=' .
//                 $readerCardId
//             );

//             return;
//         }

//         /*
//          * Tidak ditemukan sebagai tiket maupun kartu.
//          * Karena QR yang valid wajib sudah ada di harvest_orders,
//          * data ini dianggap NFC baru dan langsung disimpan.
//          */
//         if ($readerCardId === '') {
//             $this->error(
//                 'NFC BARU GAGAL: reader_card_id kosong/tidak valid'
//             );

//             return;
//         }

//         $newNfcCard = new NfcCard();
//         $newNfcCard->reader_card_id = $readerCardId;
//         $newNfcCard->reader_card_id_display =
//             $readerCardIdDisplay;
//         $newNfcCard->name = null;
//         $newNfcCard->status = 'active';
//         $newNfcCard->first_seen_at = now();
//         $newNfcCard->last_seen_at = now();
//         $newNfcCard->save();

//         $this->info(
//             "NFC BARU DISIMPAN: {$readerCardIdDisplay}"
//         );
//     }

//     private function processBarcode(string $barcode): void
//     {
//         $harvestOrder = HarvestOrder::query()
//             ->where('no_tiket', $barcode)
//             ->first();

//         if (!$harvestOrder) {
//             $this->warn(
//                 "BARCODE TIDAK DITEMUKAN: {$barcode}"
//             );

//             return;
//         }

//         $this->info("BARCODE: {$barcode}");
//         $this->handleHarvestOrder($harvestOrder);
//     }

//     private function handleHarvestOrder(
//         HarvestOrder $harvestOrder
//     ): void {
//         /*
//          * Dispatch event/job atau panggil service aplikasi di sini.
//          *
//          * Contoh:
//          * event(new HarvestOrderScanned($harvestOrder));
//          */
//     }

//     private function handleExistingNfc(NfcCard $nfcCard): void
//     {
//         if ($nfcCard->status === 'blocked') {
//             $this->error(
//                 "NFC DIBLOKIR: {$nfcCard->reader_card_id}"
//             );

//             return;
//         }

//         if ($nfcCard->status !== 'active') {
//             $this->warn(
//                 "NFC TIDAK AKTIF: {$nfcCard->reader_card_id}"
//             );

//             return;
//         }

//         $nfcCard->forceFill([
//             'last_seen_at' => now(),
//         ])->save();

//         $this->info(
//             "NFC LAMA: {$nfcCard->reader_card_id}"
//         );

//         /*
//          * Dispatch event/job atau panggil service aplikasi di sini.
//          */
//     }

//     private function parseFields(string $line): array
//     {
//         $result = [];

//         foreach (explode('|', $line) as $index => $part) {
//             if (
//                 $index === 0 ||
//                 !str_contains($part, '=')
//             ) {
//                 continue;
//             }

//             [$key, $value] = explode('=', $part, 2);
//             $result[$key] = $value;
//         }

//         return $result;
//     }

//     private function normalizeReaderCardId(
//         string $value
//     ): string {
//         $value = strtoupper(trim($value));

//         return preg_replace(
//             '/[^A-F0-9]/',
//             '',
//             $value
//         ) ?: '';
//     }

//     private function stopPython(): void
//     {
//         if (
//             $this->pythonProcess !== null &&
//             $this->pythonProcess->isRunning()
//         ) {
//             $this->pythonProcess->stop(3);
//         }

//         $this->pythonProcess = null;
//     }
// }