<?php

if (class_exists('Mapper')) {
    /**
     * Class GenCollection
     *
     * Сlass for working with lists of people,
     * the constructor searches for the id of people in all fields of the database,
     * retrieving an array of instances of the Mapper class from an array
     * with the id of people obtained in the constructor,
     * removing people from the database using instances of the Mapper class
     */
    class GenCollection
    {
        public  \Generator    $genObjs;
        private array         $arrId;
        private int           $total = 0;
        private array         $objects = [];
        private \PDOStatement $moreStmt;
        private \PDOStatement $lessStmt;
        private \PDOStatement $notequalStmt;
        private \PDOStatement $deleteStmt;

        public function __construct(int $id, string $expr)
        {
            global $db;
            $this->moreStmt = $db->prepare("SELECT * FROM people WHERE `id`>?");
            $this->lessStmt = $db->prepare("SELECT * FROM people WHERE `id`<?");
            $this->notequalStmt = $db->prepare("SELECT * FROM people WHERE !(`id`=?)");
            $this->deleteStmt = $db->prepare("DELETE FROM people WHERE `id`=?");

            $this->genObjs = $this->find($id, $expr);
        }

        private function find(int $id, string $expr): ?\Generator
        {
            $this->exprStmt($expr)->execute([$id]);
            $this->arrId = $this->exprStmt($expr)->fetchAll();
            $this->exprStmt($expr)->closeCursor();

            $objs = $this->getCollection($this->arrId);
            return $objs;
        }

        private function exprStmt(string $expr): ?\PDOStatement
        {
            if ($expr === 'more') {
                return $this->moreStmt;
            } elseif ($expr === 'less')
            {
                return $this->lessStmt;
            } else {
                return $this->notequalStmt;
            }
        }

        private function createMapper(array $row): Mapper
        {
            $obj = new Mapper((int)    $row['id'],
                                       $row['name'],
                                       $row['surname'],
                                       $row['birthdate'],
                              (string) $row['sex'],
                                       $row['birthcity']);
            return $obj;
        }

        private function getCollection(array $row): ?\Generator
        {
            $this->total = count($row);

            for ($x = 0; $x < $this->total; $x++)
            {
                yield $this->getRow($x);
            }
        }

        private function getRow(int $x): ?Mapper
        {
            if ($x >= $this->total || $x < 0) {
                return null;
            }
            if (isset($this->objects[$x])) {
                return $this->objects[$x];
            }
            if (isset($this->arrId[$x])) {
                $this->objects[$x] = $this->createMapper($this->arrId[$x]);
                return $this->objects[$x];
            }
            return null;
        }

        public function deleteCollection(): void
        {
            for ($x = 0; $x < $this->total; $x++)
            {
                $this->deleteRow($x);
            }
        }

        private function deleteRow(int $x): void
        {
            $this->deleteStmt->execute([$this->objects[$x]->id]);
        }
    }
} else {
    throw new \Exception('Missing Mapper');
}