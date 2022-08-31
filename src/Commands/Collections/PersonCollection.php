<?php

namespace CTExport\Commands\Collections;

use CTApi\Models\GroupMember;
use CTApi\Models\Person;
use CTApi\Requests\PersonRequest;

class PersonCollection
{
    private array $personIds = [];

    public function __construct(array $personArray = [])
    {
        $this->pushPersonArray($personArray);
    }

    private function pushPersonArray(array $personArray)
    {
        foreach ($personArray as $person) {
            if ($person instanceof Person) {
                array_push($this->personIds, $person->getId());
            } else if ($person instanceof GroupMember) {
                array_push($this->personIds, $person->getPersonId());
            } else {
                array_push($this->personIds, $person);
            }
        }
    }

    /**
     * @return array
     */
    public function getPersonIds(): array
    {
        return $this->personIds;
    }

    public function getPersonAsObject(): array
    {
        return array_values(array_map(function ($personId) {
            return PersonRequest::findOrFail($personId);
        }, $this->personIds));
    }

    public function addPersons(array $personArray)
    {
        $this->pushPersonArray($personArray);
    }

    public function makePersonUnique()
    {
        $this->personIds = array_unique($this->personIds);
    }

    public static function InnerJoin(PersonCollection $personCollectionA, PersonCollection $personCollectionB): PersonCollection
    {
        $personIds = array_intersect($personCollectionA->getPersonIds(), $personCollectionB->getPersonIds());
        return new PersonCollection($personIds);
    }

    public static function LeftOuterJoin(PersonCollection $personCollectionA, PersonCollection $personCollectionB): PersonCollection
    {
        $idsA = $personCollectionA->getPersonIds();
        $idsB = $personCollectionB->getPersonIds();

        $leftOuterIds = [];
        foreach ($idsA as $id) {
            if (!in_array($id, $idsB)) {
                array_push($leftOuterIds, $id);
            }
        }

        return new PersonCollection($leftOuterIds);
    }
}