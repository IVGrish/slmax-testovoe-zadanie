<?php

/**
 * Author: Ivan Grishankov
 *
 * Date of implementation: 03.11.2022 17:47
 *
 * Database utility: phpMyAdmin
 */
try {
    $db = new PDO('mysql:host=localhost;dbname=people', 'johnsnest', 'jnpassword');
} catch (PDOException $e) {
    echo "Can't connect: " . $e->getMessage();
    exit();
}

$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

require_once 'Mapper.php';
require_once 'GenCollection.php';

$jess = new Mapper(-1, 'Jess', 'Frick',  '2000.05.10', '0', 'Dallas');
$tim  = new Mapper(-1, 'Tim',  'Fried',  '1999.04.11', '1', 'York');
$mike = new Mapper(-1, 'Mike', 'Friend', '1998.10.11', '1', 'Sydney');

$sel = new Mapper(3);
$sel->delete();

$trans = Mapper::transform(2);
var_dump($trans);

$people = new GenCollection(3, 'l');
foreach ($people->genObjs as $man)
{
    var_dump($man);
}
$people->deleteCollection();