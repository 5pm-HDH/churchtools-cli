<?php


namespace CTExport\Commands\MigrateCommands;


use CTApi\Exceptions\CTRequestException;
use CTApi\Models\Song;
use CTApi\Requests\SongArrangementRequest;

class SongArrangementBpmMigration extends Migration
{

    public function migrateModel($model): int|array
    {
        if (is_a($model, Song::class)) {
            return $this->migrateSong($model);
        } else {
            return $this->logModel("Model is not subclass of Song", $model, Migration::RESULT_FAILED);
        }
    }

    private function migrateSong(Song $song): int
    {
        $songArrangements = $song->getArrangements();
        if (empty($songArrangements)) {
            return $this->logModel("Song does not contain any arrangement", $song, Migration::RESULT_SKIPPED);
        }
        $foundBpm = null;
        $inconsistentBpms = [];
        foreach ($songArrangements as $songArrangement) {
            if (!is_null($songArrangement->getBpm())
                && $songArrangement->getBpm() != ""
                && $songArrangement->getBpm() != " ") {
                if (is_null($foundBpm)) {
                    $foundBpm = $songArrangement->getBpm();
                } else {
                    if ($foundBpm != $songArrangement->getBpm()) { // different BPM found
                        $inconsistentBpms[] = $songArrangement->getBpm();
                    }
                }
            }
        }
        if (!empty($inconsistentBpms)) {
            return $this->logModel("Arrangements of song contains different BPMs: " . implode(", ", array_merge($inconsistentBpms, [$foundBpm])), $song, Migration::RESULT_FAILED);
        }
        if (is_null($foundBpm)) {
            return $this->logModel("Could not find a BPM in any Arrangement of this song", $song, Migration::RESULT_FAILED);
        } else {
            return $this->migrateArrangementsToBpm($song, $foundBpm);
        }
    }

    private function migrateArrangementsToBpm(Song $song, string $bpm): int
    {
        $skippedArrangementIds = [];
        $migratedArrangementIds = [];
        $errorMessages = [];

        foreach ($song->getArrangements() as $arrangement) {
            if ($arrangement->getBpm() != $bpm) {
                $arrangement->setBpm($bpm);
                try {
                    if (!$this->isTestRun()) {
                        SongArrangementRequest::update($arrangement);
                    }
                    $migratedArrangementIds[] = $arrangement->getId();
                } catch (CTRequestException $exception) {
                    $errorMessages[] = "Error in Arrangement #" . $arrangement->getId() . ": " . $exception->getMessage();
                }
            } else {
                $skippedArrangementIds[] = $arrangement->getId();
            }
        }

        $migratedMessage = empty($migratedArrangementIds) ? "" : "Migrated-ArrangementIds (to " . $bpm . "-BPM): " . implode(", ", $migratedArrangementIds) . ";";
        $skippedMessage = empty($skippedArrangementIds) ? "" : "Skipped-ArrangementIds (already migrated): " . implode(", ", $skippedArrangementIds) . ";";
        if (!empty($errorMessages)) {
            return $this->logModel("Migration-Errors: " . implode(", ", $errorMessages) . "; " . $migratedMessage . " " . $skippedMessage, $song, Migration::RESULT_FAILED);
        }
        if (!empty($migratedArrangementIds)) {
            return $this->logModel($migratedMessage . " " . $skippedMessage, $song, Migration::RESULT_SUCCESS);
        }
        return $this->logModel($skippedMessage, $song, Migration::RESULT_SKIPPED);
    }
}