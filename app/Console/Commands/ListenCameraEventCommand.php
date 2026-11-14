<?php

namespace App\Console\Commands;

use App\Models\Camera;
use App\Models\CameraCaptureLog;
use App\Services\CameraService;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class ListenCameraEventCommand extends Command
{
    protected $signature = 'camera:listen-events {camera_id}';
    protected $description = 'Listen Hikvision camera events and auto capture snapshot';
    private ?int $lastCapturedAt = null;
    private int $captureCount = 0;
    private bool $motionActive = false;
    private int $maxCapturePerEvent = 3;
    private int $captureIntervalSecond = 1;

    public function handle(CameraService $cameraService)
    {
        $camera = Camera::findOrFail($this->argument('camera_id'));

        $url = $this->cameraBaseUrl($camera)."/ISAPI/Event/notification/alertStream";

        $this->info("Listening camera event: {$camera->name}");
        $this->info($url);

        while (true) {
            try {
                $this->listen($camera, $url, $cameraService);
            } catch (Exception $e) {
                $this->error($e->getMessage());
                sleep(5);
            }
        }
    }



    //
    private function cameraBaseUrl(Camera $camera): string
    {
        $port = $camera->port ?: 80;

        if ((int) $port === 80) {
            return "http://{$camera->ip}";
        }
        return "http://{$camera->ip}:{$port}";
    }
    private function listen(Camera $camera, string $url, CameraService $cameraService): void
    {
        $client = new Client([
            'timeout' => 0,
            'read_timeout' => 30,
        ]);

        $response = $client->request('GET', $url, [
            'auth' => [
                $camera->username,
                $camera->password,
                'basic',
            ],
            'stream' => true,
        ]);

        $body = $response->getBody();

        $buffer = '';

        while (!$body->eof()) {
            $chunk = $body->read(1024);

            if (!$chunk) {
                continue;
            }

            $buffer .= $chunk;

            preg_match_all(
                '/<EventNotificationAlert[\s\S]*?<\/EventNotificationAlert>/',
                $buffer,
                $matches
            );

            foreach ($matches[0] as $xmlText) {
                $this->handleEvent($xmlText, $camera, $cameraService);

                $buffer = str_replace($xmlText, '', $buffer);
            }

            if (strlen($buffer) > 100000) {
                $buffer = '';
            }
        }
    }
    private function handleEvent(string $xmlText, Camera $camera, CameraService $cameraService): void
    {
        $xml = @simplexml_load_string($xmlText);

        if (!$xml) {
            return;
        }

        $eventType = strtolower((string) ($xml->eventType ?? ''));
        $eventState = strtolower((string) ($xml->eventState ?? ''));
        $eventDescription = strtolower((string) ($xml->eventDescription ?? ''));
        $targetType = strtolower((string) ($xml->targetType ?? ''));
        $eventData = [
            'event_type' => $eventType,
            'event_state' => $eventState,
            'event_description' => $eventDescription,
            'target_type' => $targetType,
            'date_time' => (string) ($xml->dateTime ?? ''),
            'channel_name' => (string) ($xml->channelName ?? ''),
        ];
        if ($eventType === 'videoloss') {
            return;
        }

        $this->line("Event: {$eventType} | {$eventState} | {$eventDescription}");

        if (!$this->isTriggerEvent($eventType, $eventDescription)) {
            return;
        }

        // Kalau motion sudah selesai, reset counter
        if ($this->isInactiveEvent($eventState)) {
            $this->motionActive = false;
            $this->captureCount = 0;

            $this->info('Motion selesai, counter capture di-reset.');

            return;
        }

        if (!$this->isActiveEvent($eventState)) {
            return;
        }

        // Kalau event active yang sama masih berjalan, jangan capture ulang
        if ($this->motionActive) {
            $this->line('Motion masih aktif, skip capture ulang.');
            return;
        }

        $this->motionActive = true;

        $this->captureBurst($camera, $cameraService, $eventData);
    }

    //
    private function captureBurst(
        Camera $camera,
        CameraService $cameraService,
        array $eventData = []
    ): void {
        while ($this->captureCount < $this->maxCapturePerEvent) {
            try {
                $result = $cameraService->capture(
                    camera: $camera,
                    folder: 'camera/auto-capture'
                );

                $this->captureCount++;

                CameraCaptureLog::create([
                    'camera_id' => $camera->id,

                    'event_type' => $eventData['event_type'] ?? null,
                    'event_state' => $eventData['event_state'] ?? null,
                    'event_description' => $eventData['event_description'] ?? null,
                    'target_type' => $eventData['target_type'] ?? null,

                    'status' => 'success',
                    'photo_path' => $result['path'],
                    'photo_url' => $result['url'] ?? null,

                    'raw_event' => $eventData,
                    'captured_at' => now(),
                ]);

                $this->info(
                    "Foto {$this->captureCount}/{$this->maxCapturePerEvent} berhasil: " . $result['path']
                );

                if ($this->captureCount < $this->maxCapturePerEvent) {
                    sleep($this->captureIntervalSecond);
                }
            } catch (\Throwable $e) {
                CameraCaptureLog::create([
                    'camera_id' => $camera->id,

                    'event_type' => $eventData['event_type'] ?? null,
                    'event_state' => $eventData['event_state'] ?? null,
                    'event_description' => $eventData['event_description'] ?? null,
                    'target_type' => $eventData['target_type'] ?? null,

                    'status' => 'failed',
                    'error_message' => $e->getMessage(),

                    'raw_event' => $eventData,
                    'captured_at' => now(),
                ]);

                $this->error('Gagal mengambil snapshot dari ' . $camera->name . ': ' . $e->getMessage());

                break;
            }
        }
    }

    //
    private function isTriggerEvent(string $eventType, string $eventDescription): bool
    {
        return str_contains($eventType, 'vmd')
            || str_contains($eventType, 'motion')
            || str_contains($eventType, 'line')
            || str_contains($eventType, 'field')
            || str_contains($eventDescription, 'motion')
            || str_contains($eventDescription, 'vehicle');
    }
    private function isActiveEvent(string $eventState): bool
    {
        return in_array($eventState, [
            'active',
            'actived',
            'triggered',
        ]);
    }
    private function isInactiveEvent(string $eventState): bool
    {
        return in_array($eventState, [
            'inactive',
            'deactive',
            'deactivated',
        ]);
    }


    //
    private function shouldCapture(
        string $eventType,
        string $eventState,
        string $eventDescription
    ): bool {
        if (!in_array($eventState, ['active', 'actived', 'triggered'])) {
            return false;
        }

        return str_contains($eventType, 'vmd')
            || str_contains($eventType, 'motion')
            || str_contains($eventType, 'line')
            || str_contains($eventType, 'field')
            || str_contains($eventDescription, 'motion')
            || str_contains($eventDescription, 'vehicle');
    }
    private function isCooldown(): bool
    {
        if (!$this->lastCapturedAt) {
            return false;
        }

        return time() - $this->lastCapturedAt < 10;
    }
}