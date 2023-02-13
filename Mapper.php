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
 */
class Mapper
{
    static private PDOStatement $selectStmt;
    static private PDOStatement $insertStmt;
    static private PDOStatement $deleteStmt;

    public function __construct(public int     $id,
                                public ?string $name       = null,
                                public ?string $surname    = null,
                                public ?string $birthdate  = null,
                                public ?string $sex        = null,
                                public ?string $birth_city = null
    ) {
        global $db;
        self::$selectStmt = $db->prepare("SELECT * FROM people WHERE `id`=?");
        self::$insertStmt = $db->prepare("INSERT INTO people ( `name`, `surname`, "
                          . "`birthdate`, `sex`, `birth_city` ) VALUES ( ?, ?, ?, ?, ? )");
        self::$deleteStmt = $db->prepare("DELETE FROM people WHERE `id`=?");

        if ($this->id >= 1) {
            $this->find($this->id);
        } else {
            $this->insert($this);
            echo 'Person has been added to database successfully<br>';
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

    private function selectStmt(): PDOStatement
    {
        return self::$selectStmt;
    }

    private function createMapper(array $row): void
    {
        $this->id         =          $row['id'];
        $this->name       =          $row['name'];
        $this->surname    =          $row['surname'];
        $this->birthdate  =          $row['birthdate'];
        $this->sex        = (string) $row['sex'];
        $this->birth_city =          $row['birth_city'];
    }

    private function insert(Mapper $obj): void
    {
        global $db;
        $values = [$obj->name, $obj->surname, $obj->birthdate, $obj->sex, $obj->birth_city];
        self::$insertStmt->execute($values);
        $id = $db->lastInsertId();
        $obj->setId((int)$id);
    }

    public function delete(): void
    {
        if (!$this->find($this->id)) {
            echo 'Non-existent Person<br>';
        } else {
            echo 'Person exists in database<br>';
            print_r($this);

            self::$deleteStmt->execute([$this->id]);

            foreach ($GLOBALS as $k => $v) {
                if ($v === $this) {
                    $GLOBALS[$k] = null;
                    break;
                }
            }
            echo '<br>Deleting of this person has been done successfully<br>';
        }
    }

    /**
     *
     * Function look up to person in database,
     * if person exists it transforms sex to string formation,
     * counts age and creates StdClass object
     *
     * @param int $id
     * @return ?StdClass
     */
    public static function transform(int $id): ?StdClass
    {
        $self = new static($id);
        if (!$self->find($self->id)) {
            echo 'Non-existent Person<br>';
            return null;
        } else {
            echo 'Person exists in database<br>';
            $birthday_remake = str_replace('.', '-', $self->birthdate);

            $now = new DateTime();

            try {
                $birthdate = new DateTime($birthday_remake);
            } catch (Exception $e) {
                echo 'Wrong date and time: ' . $e->getMessage();
                exit();
            }

            $diff = $birthdate->diff($now);
            $years = $diff->y;

            if ($self->sex) {
                $textSex = 'male';
            } else {
                $textSex = 'female';
            }

            return (object) array('id'         => $self->id,
                                  'name'       => $self->name,
                                  'surname'    => $self->surname,
                                  'birthdate'  => $self->birthdate,
                                  'sex'        => $textSex,
                                  'birth_city' => $self->birth_city,
                                  'age'        => $years);
        }
    }
}