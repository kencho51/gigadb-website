<?php
$this->pageTitle = 'GigaDB - Dataset Types';

?>
<h1>GigaDB - Dataset Types</h1>

<?php

$type = $mainSection->getHeadline()['types'];
echo $type;
//foreach ($types as $type) {
//    $name = $type->name;
//    echo $name;
//    $description = $type->description;
//    echo $description;
//}