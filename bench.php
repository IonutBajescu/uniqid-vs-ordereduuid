<?php

use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\UuidFactory;

require 'vendor/autoload.php';

$dsn = 'mysql:dbname=bench;host=mysql';
$user = 'root';

try {
    $db = new PDO($dsn, $user);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

$table = 'uuid_primarykey';
$db->exec("TRUNCATE TABLE $table");

$outTimeTakenFile = __DIR__ . '/out-time-taken.txt';
file_put_contents($outTimeTakenFile, '');

$took = [];
$insertsAtOnce = 25000;
$max = 500;
$codec = new \Ramsey\Uuid\Codec\TimestampFirstCombCodec(\Ramsey\Uuid\Uuid::getFactory()->getUuidBuilder());

$timestampFirstCombFactory = new UuidFactory();
$combGenerator = new CombGenerator(
    $timestampFirstCombFactory->getRandomGenerator(),
    $timestampFirstCombFactory->getNumberConverter()
);
$timestampFirstCombCodec = new TimestampFirstCombCodec(
    $timestampFirstCombFactory->getUuidBuilder()
);
$timestampFirstCombFactory->setRandomGenerator($combGenerator);
$timestampFirstCombFactory->setCodec($timestampFirstCombCodec);

for ($i = 0; $i<=$max; $i++) {

    $placeholders = [];
    $values = [];
    for ($j = 0; $j <= $insertsAtOnce; $j++) {
        if ($table === 'uniqid_primarykey') {
            $id = uniqid();
        } else {
//            $uuid = \Ramsey\Uuid\Uuid::uuid4();
//            $id = $codec->encodeBinary($uuid);
            $id = $timestampFirstCombFactory->uuid4()->getBytes();
        }
        $placeholders[] = "(?)";
        $values[] = $id;
    }
    $placeholders = implode(',', $placeholders);

    $start = microtime(true);
    $stmt = $db->prepare("INSERT INTO $table VALUES $placeholders");
    $stmt->execute($values);
    $taken = microtime(true) - $start;

    file_put_contents($outTimeTakenFile, "$taken\n", FILE_APPEND);
    $took[] = $taken;

    if (($i * $insertsAtOnce) % 500000 === 0) {
        echo "$i / $max\n";
    }
}

$sum = array_sum($took);
$count = count($took);
$avg = array_sum($took) / count($took);
$first = $took[0];
$last = $took[$count - 1];
echo "sum = $sum, count = $count, avg = $avg, first = $first, last = $last \n";

var_dump('db count', $db->query("SELECT COUNT(*) FROM $table")->fetchAll());
var_dump('errorinfo', $db->errorInfo());
