<?php


namespace CTExport\Commands\Traits;


use CTApi\Models\Group;
use CTApi\Models\PersonGroup;
use CTApi\Requests\GroupRequest;
use CTApi\Requests\PersonRequest;

trait LoadGroups
{
    /**
     * Load all groups
     * @return array
     */
    protected function loadGroups(): array
    {
        return GroupRequest::all();
    }

    /**
     * Load all groups for logged in user.
     * @return array
     */
    protected function loadMyGroups(): array
    {
        $personGroups = PersonRequest::whoami()?->requestGroups()?->get() ?? [];
        return array_map(function (PersonGroup $personGroup) {
            return $personGroup->getGroup();
        }, $personGroups);
    }

    /**
     * @param array $groupId
     * @return array Arrag of GroupMember
     */
    protected function loadGroupMember(string $groupId): array
    {
        return Group::createModelFromData(["id" => $groupId])->requestMembers()?->get() ?? [];
    }

}