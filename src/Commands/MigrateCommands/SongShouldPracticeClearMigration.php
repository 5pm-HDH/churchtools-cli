<?php


namespace CTExport\Commands\MigrateCommands;


use CTApi\Exceptions\CTRequestException;
use CTApi\Models\Song;
use CTApi\Requests\SongRequest;

class SongShouldPracticeClearMigration extends Migration
{
    public function migrateModel($model): int|array
    {
        if (is_a($model, Song::class)) {
            $shouldPracticeFlag = $model->getShouldPractice() ?? true;
            if ($shouldPracticeFlag) {
                $model->setShouldPractice(false);
                try {
                    if (!$this->isTestRun()) {
                        SongRequest::update($model);
                    }
                    return $this->logModel("Updated song and set should-practice-flag to false.", $model, Migration::RESULT_SUCCESS);
                } catch (CTRequestException $exception) {
                    return $this->logModel("Exception when update song: " . $exception->getMessage(), $model, Migration::RESULT_FAILED);
                }
            } else {
                return $this->logModel("Should-practice-flag is already false.", $model, Migration::RESULT_SKIPPED);
            }
        } else {
            return $this->logModel("Model is not subclass of Song", $model, Migration::RESULT_FAILED);
        }
    }
}