<?php


namespace CTExport\Commands\MigrateCommands;


use CTApi\Exceptions\CTRequestException;
use CTApi\Models\Song;
use CTApi\Models\SongArrangement;
use CTApi\Requests\SongArrangementRequest;

class SongArrangementNameMigration extends Migration
{
    private static array $replaceStrings = [
        "Standard-Arrangement",
        "Standard Arrangement",
        "Neues Arrangement",
        "In [KEY]-Dur",
        "In [KEY]m",
        "In [KEY]",
        "in [KEY]-Dur",
        "in [KEY]m",
        "in [KEY]",
        "[KEY]-Dur",
        "[KEY]",
        "[KEY]-Dur",
        "[KEY]m",
    ];
    private static string $replaceWith = "In [KEY]";


    public function migrateModel($model): int|array
    {
        if (is_a($model, Song::class)) {
            $statusArrays = [];
            $arrangements = $model->getArrangements();
            foreach ($arrangements as $arrangement) {
                $statusArrays[] = $this->migrateArrangement($arrangement);
            }
            return $statusArrays;
        } else {
            return $this->logModel("Model is not subclass of Song", $model, Migration::RESULT_FAILED);
        }
    }

    private function migrateArrangement(SongArrangement $songArrangement): int
    {
        $name = $songArrangement->getName() ?? "";
        $key = $songArrangement->getKeyOfArrangement();
        if (is_null($key) || $key == "") {
            return $this->logModel("Key of model is null. Migration of Arrangement-Name is impossible.", $songArrangement, Migration::RESULT_SKIPPED);
        }
        $newName = $this->migrateArrangementName($name, $key);

        if ($newName == $name) {
            return $this->logModel("No update required for arrangement with key: " . $key . " and name: '" . $name . "'", $songArrangement, Migration::RESULT_SKIPPED);
        }


        $songArrangement->setName($newName);
        try {
            if (!$this->isTestRun()) {
                SongArrangementRequest::update($songArrangement);
            }
            return $this->logModel("Successfully updated arrangement-name (key: " . $key . ") from '" . $name . "' to new name '" . $newName . "'", $songArrangement, Migration::RESULT_SUCCESS);
        } catch (CTRequestException $exception) {
            return $this->logModel("Error on updating arrangement-name: " . $exception->getMessage(), $songArrangement, Migration::RESULT_FAILED);
        }
    }

    /**
     * @param string $name
     * @param string $key
     * @return string
     */
    private function migrateArrangementName(string $name, string $key): string
    {
        $replaceStrings = $this->getReplaceStrings($key);
        $replaceWith = $this->getReplaceWith($key);
        foreach ($replaceStrings as $replaceString) {
            $newName = str_replace($replaceString, $replaceWith, $name);
            if ($newName != $name || str_contains($newName, $replaceWith)) {
                return $newName;
            }
        }
        return $name;
    }

    private function getReplaceStrings(string $key): array
    {
        return str_replace("[KEY]", $key, self::$replaceStrings);
    }

    private function getReplaceWith(string $key): string
    {
        return str_replace("[KEY]", $key, self::$replaceWith);
    }
}