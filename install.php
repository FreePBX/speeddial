<?php

// Enable phonebook directory as a feature code
$fcc = new featurecode('speeddial', 'callspeeddial');
$fcc->setDescription('Speeddial prefix');
$fcc->setDefault('*0');
$fcc->update();
unset($fcc);

$fcc = new featurecode('speeddial', 'setspeeddial');
$fcc->setDescription('Set user speed dial');
$fcc->setDefault('*75');
$fcc->update();
unset($fcc);

?>
