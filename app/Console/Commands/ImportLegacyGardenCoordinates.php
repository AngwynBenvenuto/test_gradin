<?php

namespace App\Console\Commands;

use App\Models\Garden;
use App\Models\GardenCoordinate;
use App\Models\GardenCoordinateSurvey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Import koordinat kebun legacy dari database/Query_Result.sql (tabel lama
 * `Query_Result`, format flat per-titik) ke skema gardens +
 * garden_coordinate_surveys + garden_coordinates.
 *
 * Sumber lama tidak punya village/district/land_ownership per kebun, jadi
 * field itu diisi placeholder (--village/--district/--land-ownership) --
 * WAJIB dicek ulang manual setelah import. Jalankan --dry-run dulu untuk
 * lihat ringkasan (jumlah titik & luas per kodekebun) sebelum commit.
 *
 * Pola gardens.source_registration_garden_id/source_spk_id placeholder
 * (tidak menunjuk baris nyata) dan survey tanpa farmer_registration_gardens
 * asli sama seperti GardenController::storeFromLan -- endpoint itu memang
 * didesain untuk kebun company-owned/legacy tanpa data petani/registrasi.
 */
class ImportLegacyGardenCoordinates extends Command
{
    protected $signature = 'garden:import-legacy-coordinates
        {--file=database/Query_Result.sql : Path file SQL sumber, relatif ke root project}
        {--company=WGI : company_code untuk kebun yang diimport}
        {--village=- : Placeholder village kalau tidak diketahui}
        {--district=- : Placeholder district kalau tidak diketahui}
        {--land-ownership=HGU : Placeholder land_ownership kalau tidak diketahui}
        {--dry-run : Tampilkan ringkasan tanpa menyimpan apa pun}
        {--sql= : Tulis SQL INSERT/UPDATE ke file ini (path relatif ke root project) TANPA menyentuh DB sama sekali}';

    protected $description = 'Import koordinat kebun legacy dari Query_Result.sql ke gardens/garden_coordinate_surveys';

    public function handle(): int
    {
        $path = base_path($this->option('file'));

        if (!is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        $sqlSource = file_get_contents($path);

        preg_match_all(
            "/\('(\d+)',\s*(?:NULL|'[^']*'),\s*(?:NULL|'[^']*'),\s*(?:NULL|'[^']*'),\s*'([^']+)',\s*(\d+),\s*'(-?[\d.]+)',\s*'(-?[\d.]+)',/",
            $sqlSource,
            $matches,
            PREG_SET_ORDER
        );

        if (empty($matches)) {
            $this->error('Tidak ada baris koordinat yang berhasil di-parse dari file.');
            return self::FAILURE;
        }

        $groups = [];
        foreach ($matches as $m) {
            [, , $kodekebun, $urutan, $x, $y] = $m;
            $groups[$kodekebun][] = [
                'urutan' => (int) $urutan,
                'longitude' => (float) $x,
                'latitude' => (float) $y,
            ];
        }

        $this->info(count($groups) . ' kodekebun ditemukan, ' . count($matches) . ' titik total.');

        $companyCode = strtoupper(trim((string) $this->option('company')));
        $dryRun = (bool) $this->option('dry-run');
        $sqlOutput = $this->option('sql');
        $summary = [];
        $validGroups = [];

        foreach ($groups as $gardenCode => $points) {
            usort($points, fn ($a, $b) => $a['urutan'] <=> $b['urutan']);

            if (count($points) < 3) {
                $this->warn("Lewati {$gardenCode}: cuma " . count($points) . ' titik (< 3).');
                continue;
            }

            $areaHa = $this->polygonAreaHa($points);
            $summary[] = [$gardenCode, count($points), number_format($areaHa, 4)];
            $validGroups[$gardenCode] = ['points' => $points, 'area_ha' => $areaHa];
        }

        $this->table(['kodekebun', 'jumlah titik', 'area_ha (hitung)'], $summary);

        if ($dryRun) {
            $this->comment('Dry run -- tidak ada yang disimpan/ditulis. Jalankan tanpa --dry-run untuk commit atau dengan --sql untuk generate file.');
            return self::SUCCESS;
        }

        if ($sqlOutput) {
            $this->writeSqlFile($sqlOutput, $validGroups);
            return self::SUCCESS;
        }

        foreach ($validGroups as $gardenCode => $group) {
            DB::transaction(function () use ($gardenCode, $group, $companyCode) {
                $this->importGarden($gardenCode, $group['points'], $group['area_ha'], $companyCode);
            });
        }

        $this->info('Import selesai. village/district/land_ownership pakai placeholder -- cek ulang manual.');

        return self::SUCCESS;
    }

    /**
     * Generate SQL murni (read-only terhadap DB, tidak menulis apa pun) yang
     * langsung insert ke `garden_coordinates` saja -- tidak menyentuh
     * `gardens`/`garden_coordinate_surveys`.
     *
     * `survey_id` wajib diisi (NOT NULL) tapi di DB ini `garden_coordinate_surveys`
     * kosong sementara `garden_coordinates`/`gardens.current_coordinate_survey_id`
     * sudah lama diisi angka placeholder yang tidak menunjuk baris nyata --
     * jadi kalau kodekebun-nya sudah ada di `gardens` dan punya
     * `current_coordinate_survey_id`, angka itu dipakai ulang (konsisten
     * dengan data yang sudah ada). Kalau belum ada, dialokasikan placeholder
     * baru (nomor urut setelah nilai tertinggi yang sudah dipakai).
     */
    private function writeSqlFile(string $relativePath, array $validGroups): void
    {
        $pdo = DB::connection()->getPdo();
        $quote = fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value);
        $now = $quote(now()->toDateTimeString());

        $nextSurveyId = (int) max(
            DB::table('garden_coordinates')->max('survey_id') ?? 0,
            DB::table('gardens')->max('current_coordinate_survey_id') ?? 0
        );

        $statements = [];

        foreach ($validGroups as $gardenCode => $group) {
            $points = $group['points'];

            $existingSurveyId = Garden::query()
                ->where('garden_code', $gardenCode)
                ->value('current_coordinate_survey_id');

            if ($existingSurveyId) {
                $surveyId = (int) $existingSurveyId;
            } else {
                $nextSurveyId++;
                $surveyId = $nextSurveyId;
            }

            $statements[] = "-- {$gardenCode} (survey_id {$surveyId})";

            $valueRows = [];
            foreach ($points as $i => $point) {
                $lat = number_format($point['latitude'], 8, '.', '');
                $lng = number_format($point['longitude'], 8, '.', '');
                $seq = $i + 1;
                $valueRows[] = "({$surveyId}, 1, 0, {$seq}, " . $quote(GardenCoordinate::ROLE_BOUNDARY) . ", {$lat}, {$lng}, NULL, NULL, NULL, {$now}, {$now})";
            }

            $statements[] = "INSERT INTO `garden_coordinates` "
                . '(`survey_id`, `polygon_no`, `ring_no`, `sequence_no`, `point_role`, `latitude`, `longitude`, '
                . '`altitude_m`, `accuracy_m`, `captured_at`, `created_at`, `updated_at`) VALUES '
                . implode(', ', $valueRows) . ';';
        }

        $outputPath = base_path($relativePath);
        $header = "-- Generated by `php artisan garden:import-legacy-coordinates --sql`\n"
            . '-- ' . now()->toDateTimeString() . "\n"
            . "-- Cuma insert ke garden_coordinates -- gardens/garden_coordinate_surveys TIDAK disentuh.\n"
            . "-- survey_id untuk kodekebun yang belum ada di gardens dialokasikan sebagai placeholder baru\n"
            . "-- (tidak menunjuk baris garden_coordinate_surveys yang nyata, sama seperti data lama).\n\n"
            . "START TRANSACTION;\n\n";
        $footer = "\nCOMMIT;\n";

        file_put_contents($outputPath, $header . implode("\n\n", $statements) . $footer);

        $this->info(count($statements) . " statement SQL ditulis ke {$relativePath}. Tidak ada yang disentuh di DB.");
    }

    private function importGarden(string $gardenCode, array $points, float $areaHa, string $companyCode): void
    {
        [$landType, $regionCode, $sectionCode] = $this->parseGardenCodeSegments($gardenCode);

        $garden = Garden::query()
            ->where('garden_code', $gardenCode)
            ->lockForUpdate()
            ->first();

        $attributes = [
            'land_type' => $landType,
            'region_code' => $regionCode,
            'section_code' => $sectionCode,
            'company_code' => $companyCode,
            'garden_name' => $garden->garden_name ?? '',
            'address' => $garden->address ?? '-',
            'village' => $garden->village ?? $this->option('village'),
            'district' => $garden->district ?? $this->option('district'),
            'land_ownership' => strtoupper($garden->land_ownership ?? $this->option('land-ownership')),
            'area_ha' => $areaHa,
            'status' => Garden::STATUS_ACTIVE,
            'source' => Garden::SOURCE_CLOUD,
            'lan_reference_id' => $gardenCode,
            'measured_at' => now(),
        ];

        if (!$garden) {
            $garden = Garden::create(array_merge($attributes, [
                'garden_code' => $gardenCode,
                'source_registration_garden_id' => $this->nextPlaceholderId('source_registration_garden_id'),
                'source_spk_id' => $this->nextPlaceholderId('source_spk_id'),
                'approved_at' => now(),
            ]));
        } else {
            $garden->forceFill($attributes)->save();
        }

        $this->storeCoordinates($garden, $points);
    }

    private function storeCoordinates(Garden $garden, array $points): void
    {
        $survey = $garden->current_coordinate_survey_id
            ? GardenCoordinateSurvey::query()
                ->whereKey($garden->current_coordinate_survey_id)
                ->lockForUpdate()
                ->first()
            : null;

        $centroidLatitude = round(array_sum(array_column($points, 'latitude')) / count($points), 8);
        $centroidLongitude = round(array_sum(array_column($points, 'longitude')) / count($points), 8);

        if (!$survey) {
            $survey = GardenCoordinateSurvey::create([
                'survey_uuid' => (string) Str::uuid(),
                'registration_garden_id' => $garden->source_registration_garden_id,
                'garden_id' => $garden->getKey(),
                'company_code' => $garden->company_code,
                'revision_no' => 1,
                'source' => GardenCoordinateSurvey::SOURCE_IMPORT,
                'source_reference_id' => $garden->garden_code,
                'status' => GardenCoordinateSurvey::STATUS_APPROVED,
                'is_current' => true,
                'capture_method' => GardenCoordinateSurvey::CAPTURE_IMPORT,
                'surveyed_at' => now(),
                'synced_at' => now(),
                'approved_at' => now(),
            ]);

            $garden->forceFill([
                'current_coordinate_survey_id' => $survey->getKey(),
            ])->save();
        }

        $survey->forceFill([
            'calculated_area_ha' => $garden->area_ha,
            'centroid_latitude' => $centroidLatitude,
            'centroid_longitude' => $centroidLongitude,
            'synced_at' => now(),
        ])->save();

        GardenCoordinate::query()->where('survey_id', $survey->getKey())->delete();

        $now = now();
        $rows = [];
        foreach ($points as $i => $point) {
            $rows[] = [
                'survey_id' => $survey->getKey(),
                'polygon_no' => 1,
                'ring_no' => 0,
                'sequence_no' => $i + 1,
                'point_role' => GardenCoordinate::ROLE_BOUNDARY,
                'latitude' => number_format($point['latitude'], 8, '.', ''),
                'longitude' => number_format($point['longitude'], 8, '.', ''),
                'altitude_m' => null,
                'accuracy_m' => null,
                'captured_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        GardenCoordinate::query()->insert($rows);
    }

    /**
     * Segmen land_type/region_code/section_code dari garden_code berformat
     * legacy LAN, mis. 'PL.T029S00600103' -> PL/T029/S006.
     */
    private function parseGardenCodeSegments(string $gardenCode): array
    {
        if (preg_match('/^([A-Z]{2})\.([A-Z]\d{3})(S\d{3})/', strtoupper($gardenCode), $m)) {
            return [$m[1], $m[2], $m[3]];
        }

        return [null, null, null];
    }

    /**
     * gardens.source_registration_garden_id/source_spk_id wajib diisi &
     * unik tapi tidak punya baris nyata untuk kebun legacy -- dipenuhi
     * placeholder yang tidak menunjuk ke apa pun, sama seperti pola di
     * GardenController::nextGardenPlaceholderId().
     */
    private function nextPlaceholderId(string $column): int
    {
        return (int) (Garden::query()->max($column) ?? 0) + 1;
    }

    /**
     * Luas poligon dari titik lat/lng (shoelace formula, proyeksi
     * equirectangular sederhana) -- cukup akurat untuk luas kebun skala
     * hektar dan tidak butuh library GIS tambahan.
     */
    private function polygonAreaHa(array $points): float
    {
        $latRef = array_sum(array_column($points, 'latitude')) / count($points);
        $metersPerDegLat = 111320.0;
        $metersPerDegLng = 111320.0 * cos(deg2rad($latRef));

        $projected = array_map(fn ($p) => [
            'x' => $p['longitude'] * $metersPerDegLng,
            'y' => $p['latitude'] * $metersPerDegLat,
        ], $points);

        $area = 0.0;
        $n = count($projected);
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $projected[$i]['x'] * $projected[$j]['y'];
            $area -= $projected[$j]['x'] * $projected[$i]['y'];
        }

        $areaM2 = abs($area) / 2;

        return max($areaM2 / 10000, 0.0001);
    }
}
