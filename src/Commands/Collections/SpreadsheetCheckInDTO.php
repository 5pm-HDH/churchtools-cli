<?php


namespace CTExport\Commands\Collections;


use CTApi\Models\GroupMeeting;
use CTApi\Models\GroupMeetingMember;
use CTApi\Models\Person;

class SpreadsheetCheckInDTO
{
    /**
     * [
     *     "status21" => [
     *          "present" => 3,
     *          "absent" => 0,
     *          "unsure" => 9
     *     ],
     *     ...
     *  ]
     *
     * @var array $attendeeStatistics
     */
    private array $attendeeStatistics = [];

    private array $presentPersonIds = [];
    private array $absentPersonIds = [];
    private array $unsurePersonIds = [];

    public function __construct(
        protected GroupMeeting $groupMeeting
    )
    {
    }

    public function addGroupMembers(array $groupMeetingMembers)
    {
        foreach ($groupMeetingMembers as $groupMeetingMember) {
            $this->addGroupMember($groupMeetingMember);
        }
    }

    public function addGroupMember(GroupMeetingMember $groupMeetingMember)
    {
        $groupMember = $groupMeetingMember->getMember();
        if (is_null($groupMember)) {
            return null;
        }

        $person = $groupMember->getPerson();
        if (!is_null($person)) {
            $translatedGroupRole = SpreadsheetCheckInDTO::addPerson($person, $groupMember->getGroupTypeRoleId());

            if (!array_key_exists($translatedGroupRole, $this->attendeeStatistics)) {
                $this->attendeeStatistics[$translatedGroupRole] = [
                    "present" => 0,
                    "absent" => 0,
                    "unsure" => 0
                ];
            }
            $status = $groupMeetingMember->getStatus();
            if (!is_null($status) && array_key_exists($status, $this->attendeeStatistics[$translatedGroupRole])) {
                $this->attendeeStatistics[$translatedGroupRole][$status] += 1;
            }
        }

        switch ($groupMeetingMember->getStatus()) {
            case "absent":
                $this->absentPersonIds[] = $groupMember->getPersonId();
                break;
            case "present":
                $this->presentPersonIds[] = $groupMember->getPersonId();
                break;
            case "unsure":
                $this->unsurePersonIds[] = $groupMember->getPersonId();
                break;
        }
    }

    /**
     * @param int $personId
     * @return string either "Y" (for attending) or "N" (for not attending)
     */
    public function getAttendeeStatusOfPerson(int $personId): string
    {
        if (in_array($personId, $this->presentPersonIds)) {
            return "Y";
        } else if (in_array($personId, $this->absentPersonIds)) {
            return "N";
        } else {
            return "?";
        }
    }

    public function getIdentifierForHeading(): string
    {
        return $this->groupMeeting->getDateFrom() ?? "undefined";
    }

    public function getStatistics(): array
    {
        return $this->attendeeStatistics;
    }

    /**
     * STATIC
     */

    private static array $groupMeetingDTOMap = []; // groupMeetingId => GroupMeetingDTO
    private static array $knownPersonMap = []; // personId => Person
    private static array $knownPersonMemberStatusMap = []; // personId => memberStatusId
    private static array $knownMemberStatusArray = []; // memberStatusId
    private static array $memberStatusTranslation = [
        59 => "Leiter",
        60 => "Mitarbeiter",
        61 => "Teilnehmer"
    ];

    public static function findOrCreate(GroupMeeting $groupMeeting): SpreadsheetCheckInDTO
    {
        $groupMeetingId = $groupMeeting->getIdOrFail();
        if (!array_key_exists($groupMeetingId, self::$groupMeetingDTOMap)) {
            self::$groupMeetingDTOMap[$groupMeetingId] = new SpreadsheetCheckInDTO($groupMeeting);
        }
        return self::$groupMeetingDTOMap[$groupMeetingId];
    }

    public static function addPerson(Person $person, ?string $memberStatus)
    {
        $id = $person->getId();
        if (is_null($id)) {
            return;
        }
        if (!array_key_exists($id, self::$knownPersonMap)) {
            self::$knownPersonMap[$id] = $person;
        }
        if (is_null($memberStatus)) {
            return;
        }
        $translation = $memberStatus;
        if (array_key_exists($memberStatus, self::$memberStatusTranslation)) {
            $translation = self::$memberStatusTranslation[$memberStatus];
        }
        if (!array_key_exists($id, self::$knownPersonMemberStatusMap)) {
            self::$knownPersonMemberStatusMap[$id] = $translation;
        }
        if (!in_array($translation, self::$knownMemberStatusArray)) {
            self::$knownMemberStatusArray[] = $translation;
        }
        return $translation;
    }

    public static function getAllDTOs(): array
    {
        return array_values(self::$groupMeetingDTOMap);
    }

    public static function getAllPersons(): array
    {
        return array_values(self::$knownPersonMap);
    }


    public static function getAllMemberStatus(): array
    {
        return array_values(self::$knownMemberStatusArray);
    }

    public static function getPersonMemberStatus(int $personId): ?string
    {
        if (array_key_exists($personId, self::$knownPersonMemberStatusMap)) {
            return self::$knownPersonMemberStatusMap[$personId];
        }
        return null;
    }

}