<?php

/**
 * Class Mapper
 *
 * Class for working with a database of people;
 * the constructor creates a person in the database with the specified information
 * or takes information from the database by id;
 * deleting a person from the database in accordance with the object id;
 * static conversion of date of birth to age,
 * gender conversion to textual information
 * and output of a new instance of the stdClass class
 *
 * TODO:
 * [*] Validate inserting values into datebase
 */
class Mapper
{
    private \PDOStatement $selectStmt;
    private \PDOStatement $insertStmt;
    private \PDOStatement $deleteStmt;

    public function __construct(public int     $id,
                                public ?string $name = null,
                                public ?string $surname = null,
                                public ?string $birthdate = null,
                                public ?string $sex = null,
                                public ?string $birthcity = null)
    {
        global $db;
        $this->selectStmt = $db->prepare("SELECT * FROM people WHERE `id`=?");
        $this->insertStmt = $db->prepare("INSERT INTO people ( `name`, `surname`, "
                          . "`birthdate`, `sex`, `birthcity` ) VALUES ( ?, ?, ?, ?, ? )");
        $this->deleteStmt = $db->prepare("DELETE FROM people WHERE `id`=?");

        if ($this->id >= 1) {
            $this->find($this->id);
        } else {
            $this->insert($this);
        }
    }

    private function setId(int $id): void
    {
        $this->id = $id;
    }

    private function find(int $id): ?Mapper
    {
        $this->selectStmt()->execute([$id]);
        $row = $this->selectStmt()->fetch();
        $this->selectStmt()->closeCursor();

        if (! is_array($row)) {
            return null;
        }

        if (! isset($row['id'])) {
            return null;
        }

        $this->createMapper($row);
        return $this;
    }

    private function selectStmt(): \PDOStatement
    {
        return $this->selectStmt;
    }

    private function createMapper(array $row): void
    {
        $this->id        = (int)    $row['id'];
        $this->name      =          $row['name'];
        $this->surname   =          $row['surname'];
        $this->birthdate =          $row['birthdate'];
        $this->sex       = (string) $row['sex'];
        $this->birthcity =          $row['birthcity'];
    }

    private function insert(Mapper $obj): void
    {
        global $db;
        $values = [$obj->name, $obj->surname, $obj->birthdate, $obj->sex, $obj->birthcity];
        $this->insertStmt->execute($values);
        $id = $db->lastInsertId();
        $obj->setId((int)$id);
    }

    public function delete(): void
    {
        $this->deleteStmt->execute([$this->id]);
    }

    /**
     * @param int $id
     * @return StdClass
     */
    public static function transform(int $id): StdClass
    {
        $self = new static($id);
        $remaked = str_replace('.', '-', $self->birthdate);

        $now = new DateTime();

        try {
            $birthdate = new DateTime($remaked);
        } catch (Exception $e) {
            echo 'Wrong date and time: ' . $e->getMessage();
            exit();
        }

        $diff = $birthdate->diff($now);
        $years = $diff->y;

        if($self->sex) {
            $textSex = 'male';
        } else {
            $textSex = 'female';
        }

        return (object) array('id'        => $self->id,
                              'name'      => $self->name,
                              'surname'   => $self->surname,
                              'birthdate' => $self->birthdate,
                              'sex'       => $textSex,
                              'birthcity' => $self->birthcity,
                              'age'       => $years);
    }
}