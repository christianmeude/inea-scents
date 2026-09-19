<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Merge catalog rows that share a name so each name survives exactly once.
 *
 * Root cause it repairs: DatabaseSeeder used unconditional create(), so every
 * db:seed run cloned the package (+ scents + bookings). The client renders one
 * card per row (times four tier cards on /packages), so each stray seed run
 * showed up as visible duplicates. The seeder itself is idempotent now, and
 * unique indexes block recurrence — this merger heals rows predating those.
 *
 * Survivor rule (packages): most package_scent links, tie-break highest id
 * (keeps the newest enriched row). Scents: most package links, tie-break
 * lowest id (keeps the original). Losers have bookings/pivots repointed
 * BEFORE delete — bookings.package_id is cascadeOnDelete, so deleting first
 * would wipe booking history.
 */
class DuplicateCatalogMerger
{
    public function __invoke(): int
    {
        return DB::transaction(fn () => $this->mergeScents() + $this->mergePackages());
    }

    public function mergePackages(): int
    {
        return DB::transaction(function () {
            $merged = 0;

            $names = DB::table('packages')
                ->select('name')
                ->groupBy('name')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('name');

            foreach ($names as $name) {
                $ids = DB::table('packages')
                    ->where('name', $name)
                    ->orderBy('id')
                    ->pluck('id')
                    ->all();

                $survivor = $this->pickPackageSurvivor($ids);

                foreach (array_diff($ids, [$survivor]) as $loser) {
                    DB::table('bookings')
                        ->where('package_id', $loser)
                        ->update(['package_id' => $survivor]);

                    $this->moveLinks('package_scent', 'package_id', 'scent_id', (int) $loser, $survivor);

                    DB::table('packages')->where('id', $loser)->delete();
                    $merged++;
                }
            }

            return $merged;
        });
    }

    public function mergeScents(): int
    {
        return DB::transaction(function () {
            $merged = 0;

            $names = DB::table('scents')
                ->select('name')
                ->groupBy('name')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('name');

            foreach ($names as $name) {
                $ids = DB::table('scents')
                    ->where('name', $name)
                    ->orderBy('id')
                    ->pluck('id')
                    ->all();

                $survivor = $this->pickScentSurvivor($ids);

                foreach (array_diff($ids, [$survivor]) as $loser) {
                    $this->moveLinks('package_scent', 'scent_id', 'package_id', (int) $loser, $survivor);
                    $this->moveLinks('booking_scent', 'scent_id', 'booking_id', (int) $loser, $survivor);

                    DB::table('scents')->where('id', $loser)->delete();
                    $merged++;
                }
            }

            return $merged;
        });
    }

    private function pickPackageSurvivor(array $ids): int
    {
        $counts = DB::table('package_scent')
            ->whereIn('package_id', $ids)
            ->select('package_id')
            ->selectRaw('COUNT(*) as links')
            ->groupBy('package_id')
            ->pluck('links', 'package_id')
            ->all();

        usort($ids, fn ($a, $b) => [$counts[$b] ?? 0, $b] <=> [$counts[$a] ?? 0, $a]);

        return (int) $ids[0];
    }

    private function pickScentSurvivor(array $ids): int
    {
        $counts = DB::table('package_scent')
            ->whereIn('scent_id', $ids)
            ->select('scent_id')
            ->selectRaw('COUNT(*) as links')
            ->groupBy('scent_id')
            ->pluck('links', 'scent_id')
            ->all();

        usort($ids, fn ($a, $b) => [$counts[$b] ?? 0, $a] <=> [$counts[$a] ?? 0, $b]);

        return (int) $ids[0];
    }

    /**
     * Move link-table rows from $loser to $survivor on the given key,
     * skipping pairs the survivor already has (neither pivot declares a
     * unique pair constraint, so blind inserts would duplicate links).
     */
    private function moveLinks(string $table, string $key, string $other, int $loser, int $survivor): void
    {
        $existing = DB::table($table)
            ->where($key, $survivor)
            ->pluck($other)
            ->all();

        $rows = DB::table($table)
            ->where($key, $loser)
            ->whereNotIn($other, $existing ?: [0])
            ->get();

        foreach ($rows as $row) {
            DB::table($table)->insert([
                $key => $survivor,
                $other => $row->{$other},
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table($table)->where($key, $loser)->delete();
    }
}
