<?php

namespace App\Console\Commands;

use App\Models\ArduinoPort;
use App\Models\ArduinoPortLog;
use App\Models\Camera;
use App\Services\ArduinoRawDataParser;
use App\Services\TipplerConnectivityProbeService;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Long-running daemon: auto-starts/stops a serial reader per Tippler COM
 * port the moment Windows reports it present, and keeps camera reachability
 * fresh — all without the browser ever having to trigger a probe.
 *
 * Replaces the old design where the dashboard's JS called /tippler/probe
 * on an interval, which spawned a PowerShell process per request and made
 * the page heavy. This command spawns PowerShell at most once per
 * --presence-interval, regardless of how many browser tabs are open.
 *
 * Start once (Task Scheduler "At startup", or NSSM as a service) — it is
 * not meant to be re-invoked by cron; it loops forever like arduino:reader.
 */
class TipplerArduinoWatcherCommand extends Command
{
    protected $signature = 'tippler:watch-arduino
        {--python=py : Python executable, contoh py atau C:\Python313\python.exe}
        {--script= : Lokasi tippler_serial_bridge.py}
        {--presence-interval=5 : Detik antar cek port mana yang tersambung ke OS}
        {--camera-interval=10 : Detik antar cek konektivitas kamera}';

    protected $description = 'Auto start/stop serial reader per COM port Tippler begitu port aktif, dan jaga status koneksi kamera — jalan terus di background, tidak perlu di-trigger manual/cron dari browser.';

    /** @var array<int, array{process: Process, buffer: string, port: ArduinoPort}> */
    private array $running = [];

    public function handle(TipplerConnectivityProbeService $probeService): int
    {
        $script = $this->resolveScriptPath();

        if (!is_file($script)) {
            $this->error("File Python tidak ditemukan: {$script}");
            return self::FAILURE;
        }

        $lockPath = storage_path('app/tippler_watcher.lock');
        $lockHandle = fopen($lockPath, 'c');

        if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if ($lockHandle) {
                fclose($lockHandle);
            }
            $this->error('Tippler watcher sudah berjalan pada proses lain.');
            return self::FAILURE;
        }

        register_shutdown_function(function (): void {
            $this->stopAll();
        });

        $this->info('Tippler arduino watcher dimulai.');
        $this->line('Script: ' . $script);

        $presenceInterval = max(1, (int) $this->option('presence-interval'));
        $cameraInterval = max(1, (int) $this->option('camera-interval'));
        $lastPresenceCheck = 0;
        $lastCameraCheck = 0;

        try {
            while (true) {
                $now = time();

                if ($now - $lastPresenceCheck >= $presenceInterval) {
                    $this->syncPortProcesses($script, $probeService);
                    $lastPresenceCheck = $now;
                }

                if ($now - $lastCameraCheck >= $cameraInterval) {
                    $this->checkCameras($probeService);
                    $lastCameraCheck = $now;
                }

                $this->drainProcessOutput();

                usleep(300_000);
            }
        } finally {
            $this->stopAll();
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private function resolveScriptPath(): string
    {
        $option = $this->option('script');

        if (is_string($option) && trim($option) !== '') {
            return $option;
        }

        return base_path('scripts/tippler_serial_bridge.py');
    }

    private function syncPortProcesses(string $script, TipplerConnectivityProbeService $probeService): void
    {
        $ports = ArduinoPort::query()->where('is_active', 1)->get()->keyBy('id');
        $available = $probeService->listAvailableComPorts();

        foreach (array_keys($this->running) as $portId) {
            $port = $ports->get($portId);
            $present = $port && $probeService->isComPortPresent($port->com_port, $available);

            if (!$present) {
                $this->stopWatcher($portId, markOfflineInDb: true);
            }
        }

        foreach ($ports as $port) {
            if (isset($this->running[$port->id])) {
                continue;
            }

            if (!$probeService->isComPortPresent($port->com_port, $available)) {
                continue;
            }

            $this->startWatcher($script, $port);
        }
    }

    private function startWatcher(string $script, ArduinoPort $port): void
    {
        $this->line('[WATCH] ' . $port->com_port . ' terdeteksi aktif, membuka serial reader...');

        $process = new Process($this->buildPythonCommand($script, $port), base_path());
        $process->setTimeout(null);
        $process->setIdleTimeout(null);
        $process->start();

        $this->running[$port->id] = [
            'process' => $process,
            'buffer' => '',
            'port' => $port,
        ];

        $port->update(['status' => 'active', 'last_checked_at' => now()]);
    }

    private function buildPythonCommand(string $script, ArduinoPort $port): array
    {
        $python = (string) $this->option('python');
        $command = [$python];

        $pythonName = strtolower(basename(str_replace('\\', '/', $python)));
        if ($pythonName === 'py' || $pythonName === 'py.exe') {
            $command[] = '-3';
        }

        $command[] = $script;
        $command[] = '--port';
        $command[] = $port->com_port;
        $command[] = '--baud';
        $command[] = (string) ($port->baud_rate ?: 9600);

        return $command;
    }

    private function stopWatcher(int $portId, bool $markOfflineInDb): void
    {
        $entry = $this->running[$portId] ?? null;

        if (!$entry) {
            return;
        }

        if ($entry['process']->isRunning()) {
            $entry['process']->stop(3);
        }

        unset($this->running[$portId]);

        if ($markOfflineInDb) {
            $entry['port']->update(['status' => 'offline', 'last_checked_at' => now()]);
            $this->line('[WATCH] ' . $entry['port']->com_port . ' terputus, serial reader dihentikan.');
        }
    }

    private function drainProcessOutput(): void
    {
        $dead = [];

        foreach ($this->running as $portId => &$entry) {
            $process = $entry['process'];

            if (!$process->isRunning()) {
                $this->warn('[WATCH] Serial reader ' . $entry['port']->com_port . ' berhenti, akan dicoba lagi.');
                $dead[] = $portId;
                continue;
            }

            $entry['buffer'] .= $process->getIncrementalOutput();

            while (($pos = strpos($entry['buffer'], "\n")) !== false) {
                $line = trim(substr($entry['buffer'], 0, $pos));
                $entry['buffer'] = substr($entry['buffer'], $pos + 1);

                if ($line !== '') {
                    $this->handleSerialLine($entry['port'], $line);
                }
            }

            $errOutput = trim($process->getIncrementalErrorOutput());
            if ($errOutput !== '') {
                foreach (preg_split('/\r\n|\r|\n/', $errOutput) as $errLine) {
                    if ($errLine !== '') {
                        $this->warn('[' . $entry['port']->com_port . '] ' . $errLine);
                    }
                }
            }
        }
        unset($entry);

        foreach ($dead as $portId) {
            unset($this->running[$portId]);
        }
    }

    private function handleSerialLine(ArduinoPort $port, string $line): void
    {
        if (str_starts_with($line, '[INFO]') || str_starts_with($line, '[CONNECTED]')) {
            $this->line('[' . $port->com_port . '] ' . $line);
            return;
        }

        $parsed = ArduinoRawDataParser::parse($line);

        $port->update([
            'status' => 'active',
            'last_event_type' => $parsed['event_type'],
            'last_payload' => $parsed['payload'],
            'last_read_at' => now(),
        ]);

        ArduinoPortLog::create([
            'arduino_port_id' => $port->id,
            'camera_id' => $port->camera_id,
            'event_type' => $parsed['event_type'],
            'payload' => $parsed['payload'],
            'raw_data' => $line,
            'read_at' => now(),
        ]);

        $this->info('[' . $port->com_port . '] ' . $parsed['event_type'] . ': ' . $parsed['payload']);
    }

    private function checkCameras(TipplerConnectivityProbeService $probeService): void
    {
        $now = now();

        Camera::query()
            ->where('is_active', 1)
            ->orderBy('id')
            ->limit(6)
            ->get()
            ->each(function (Camera $camera) use ($probeService, $now) {
                $reachable = $probeService->isCameraReachable($camera);

                $camera->update([
                    'connection_status' => $reachable ? 'active' : 'offline',
                    'last_checked_at' => $now,
                ]);
            });
    }

    private function stopAll(): void
    {
        foreach (array_keys($this->running) as $portId) {
            $this->stopWatcher($portId, markOfflineInDb: false);
        }
    }
}
