<?php

// Enable phonebook directory as a feature code
$speeddialprefix = _("Speeddial prefix");
$fcc = new featurecode('speeddial', 'callspeeddial');
$fcc->setDescription($speeddialprefix);
$fcc->setDefault('*0');
$fcc->update();
unset($fcc);

$setuserspeedial = _("Set user speed dial");
$fcc = new featurecode('speeddial', 'setspeeddial');
$fcc->setDescription($setuserspeedial);
$fcc->setDefault('*75');
$fcc->update();
unset($fcc);

?>
