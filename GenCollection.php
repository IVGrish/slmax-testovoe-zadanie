<?php

if (class_exists('Mapper')) {
    /**
     * Class GenCollection
     *
     * Class for working with lists of people,
     * the constructor searches for the id of people in all fields of the database,
     * retrieving an array of instances of the Mapper class from an array
     * with the id of people obtained in the constructor,
     * removing people from the database using instances of the Mapper class
     *
     */
    class GenCollection
    {
        private array $arrId;
        private int   $total   = 0;
        private array $objects = [];

        public  Generator $genObjs;

        static private PDOStatement $moreStmt;
        static private PDOStatement $lessStmt;
        static private PDOStatement $notequalStmt;
        static private PDOStatement $deleteStmt;

        public function __construct(int $id, string $expr)
        {
            global $db;
            self::$moreStmt     = $db->prepare("SELECT * FROM people WHERE `id`>?");
            self::$lessStmt     = $db->prepare("SELECT * FROM people WHERE `id`<?");
            self::$notequalStmt = $db->prepare("SELECT * FROM people WHERE !(`id`=?)");
            self::$deleteStmt   = $db->prepare("DELETE FROM people WHERE `id`=?");

            $this->genObjs = $this->find($id, $expr);
        }

        private function find(int $id, string $expr): ?Generator
        {
            $this->exprStmt($expr)->execute([$id]);
            $this->arrId = $this->exprStmt($expr)->fetchAll();
            $this->exprStmt($expr)->closeCursor();

            return $this->getCollection($this->arrId);
        }

        private function exprStmt(string $expr): ?PDOStatement
        {
            if ($expr === 'more') {
                return self::$moreStmt;
            } elseif ($expr === 'less') {
                return self::$lessStmt;
            } else {
                return self::$notequalStmt;
            }
        }

        private function createMapper(array $row): Mapper
        {
            return new Mapper(         $row['id'],
                                       $row['name'],
                                       $row['surname'],
                                       $row['birthdate'],
                              (string) $row['sex'],
                                       $row['birth_city']);
        }

        private function getCollection(array $row): ?Generator
        {
            $flag = false;
            $this->total = count($row);

            for ($x = 0; $x < $this->total; $x++)
            {
                $flag = true;
                yield $this->getRow($x);
            }

            if($flag) {
                echo 'Collection has been obtained successfully';
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
            $flag = false;

            for ($x = 0; $x < $this->total; $x++)
            {
                $flag = true;
                $this->deleteRow($x);
            }

            if($flag) {
                echo '<br>Deleting of Collection has been done successfully';
            }
        }

        private function deleteRow(int $x): void
        {
            self::$deleteStmt->execute([$this->objects[$x]->id]);
        }
    }
} else {
    throw new Exception('Missing Mapper');
}