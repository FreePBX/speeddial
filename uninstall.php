<?php
$fcc = new featurecode('speeddial', 'callspeeddial');
$fcc->delete();
unset($fcc);

$fcc = new featurecode('speeddial', 'setspeeddial');
$fcc->delete();
unset($fcc);

?>
