<?php


namespace CTExport\Commands\Traits;


use CTApi\CTClient;
use CTApi\Models\Group;
use CTApi\Models\PersonGroup;
use CTApi\Requests\GroupMeetingRequest;
use CTApi\Requests\GroupRequest;
use CTApi\Requests\PersonRequest;
use CTApi\Utils\CTResponseUtil;

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
        $personGroups = PersonRequest::whoami()->requestGroups()?->get() ?? [];
        return array_map(function (PersonGroup $personGroup) {
            return $personGroup->getGroup();
        }, $personGroups);
    }

    /**
     * Load group for given id
     * @param int $id
     * @return Group
     */
    protected function loadGroupById(int $id): Group
    {
        return GroupRequest::findOrFail($id);
    }

    /**
     * Load Groups for given id-array
     * @param array $groupIds
     * @return array
     */
    protected function loadGroupsByIds(array $groupIds): array
    {
        $groups = [];
        foreach ($groupIds as $id) {
            if ($id != null) {
                $group = GroupRequest::find((int)$id);
                if ($group != null) {
                    $groups[] = $group;
                }
            }
        }
        return $groups;
    }

    /**
     * @param string $groupId
     * @return array Arrag of GroupMember
     */
    protected function loadGroupMember(string $groupId): array
    {
        return Group::createModelFromData(["id" => $groupId])->requestMembers()?->get() ?? [];
    }

    protected function loadGroupMeetings(int $groupId, string $startDate, string $endDate): array
    {
        return GroupMeetingRequest::forGroup($groupId)
            ->where("start_date", $startDate)
            ->where("end_date", $endDate)
            ->get();
    }

    protected function loadStatuses(): array
    {
        $ctClient = CTClient::getClient();
        $response = $ctClient->get('/api/statuses');
        return CTResponseUtil::dataAsArray($response);
    }
}