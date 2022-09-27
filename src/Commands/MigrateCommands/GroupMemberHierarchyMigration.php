<?php


namespace CTExport\Commands\MigrateCommands;


use CTApi\Exceptions\CTRequestException;
use CTApi\Models\Group;
use CTApi\Models\Person;
use CTApi\Requests\GroupMemberRequest;

class GroupMemberHierarchyMigration extends Migration
{
    private $parentGroupMemberPersons = [];
    private $parentGroupMemberPersonIds = [];

    public function __construct(
        private Group $parentGroup
    )
    {
        $this->parentGroupMemberPersons = $this->getPersonsOfGroup($this->parentGroup);
        $this->parentGroupMemberPersonIds = array_filter(array_map(function (Person $person) {
            return $person->getId();
        }, $this->parentGroupMemberPersons), function ($id) {
            return !is_null($id);
        });
    }

    private function getPersonsOfGroup(Group $group, ?int &$statusIds = null): array
    {
        $members = [];
        try {
            $members = $group->requestMembers()?->get() ?? [];
        } catch (CTRequestException) {
            // ignore
        }

        if (empty($members)) {
            $statusIds = $this->logModel("Could not find members of group.", $group, Migration::RESULT_SKIPPED);
        }

        $persons = [];
        foreach ($members as $groupMember) {
            try {
                $person = $groupMember->requestPerson();
                if ($person == null) {
                    throw new CTRequestException();
                }
                $persons[] = $person;
            } catch (CTRequestException) {
                $this->logModel("Could not retrieve person with id " . $groupMember->getPersonId(), $groupMember, Migration::RESULT_SKIPPED);
            }
        }
        return $persons;
    }


    public function migrateModel($model): int|array
    {
        if (is_a($model, Group::class)) {
            $statusId = null;
            $persons = $this->getPersonsOfGroup($model, $statusId);
            if (!is_null($statusId)) {
                return $statusId;
            }

            $statusIds = [];

            foreach ($persons as $person) {
                $statusIds[] = $this->migratePersonToParentGroup($person);
            }

            return $statusIds;
        } else {
            return $this->logModel("Model is not subclass of Group", $model, Migration::RESULT_FAILED);
        }
    }

    public function migratePersonToParentGroup(Person $person): int
    {
        if (is_null($person->getId())) {
            return $this->logModel("Id of person is null.", $person, Migration::RESULT_FAILED);
        }

        if (in_array($person->getId(), $this->parentGroupMemberPersonIds)) {
            return $this->logModel("Person is already in parent-group.", $person, Migration::RESULT_SKIPPED);
        }

        try {
            if (!$this->isTestRun()) {
                $groupMember = GroupMemberRequest::addMember($this->parentGroup->getIdAsInteger(), $person->getIdAsInteger());
                $groupMember->setComment("Migrated by CT-CLI Tool.");
                GroupMemberRequest::updateMember($this->parentGroup->getIdAsInteger(), $groupMember);
            }
            return $this->logModel("Successfully added person to parent-group.", $person, Migration::RESULT_SUCCESS);
        } catch (CTRequestException $exception) {
            return $this->logModel("Error when tried to add person: " . $exception->getMessage(), $person, Migration::RESULT_FAILED);
        }
    }

}