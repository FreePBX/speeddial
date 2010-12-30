<?php
 /* $Id:$ */

function speeddial_get_config($engine) {
        $modulename = 'speeddial';

        // This generates the dialplan
        global $ext;
        switch($engine) {
		case "asterisk":
			$fcc = new featurecode('speeddial', 'callspeeddial');
			$callcode = $fcc->getCodeActive();
			unset($fcc);
			
			$fcc = new featurecode('speeddial', 'setspeeddial');
			$setcode = $fcc->getCodeActive();
			unset($fcc);

			if (!empty($code)) {
				$ext->add('app-pbdirectory', $code, '', new ext_answer(''));
				$ext->add('app-pbdirectory', $code, '', new ext_wait(1));
				$ext->add('app-pbdirectory', $code, '', new ext_goto(1,'pbdirectory'));
			}

			// [macro-speeddial-lookup]
			// arg1 is speed dial location,  arg2 (optional) is user caller ID
			$ext->add('macro-speeddial-lookup', 's', '', new ext_gotoif('$["${ARG2}"=""]]','lookupsys'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_set('SPEEDDIALNUMBER',''));
			$ext->add('macro-speeddial-lookup', 's', 'lookupuser', new ext_dbget('SPEEDDIALNUMBER','AMPUSER/${ARG2}/speeddials/${ARG1}'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_gotoif('$["${SPEEDDIALNUMBER}"=""]','lookupsys'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_noop('Found speeddial ${ARG1} for user ${ARG2}: ${SPEEDDIALNUMBER}'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_goto('end'));
			$ext->add('macro-speeddial-lookup', 's', 'lookupsys', new ext_dbget('SPEEDDIALNUMBER','sysspeeddials/${ARG1}'), 'lookupuser',101);
			$ext->add('macro-speeddial-lookup', 's', '', new ext_gotoif('$["${SPEEDDIALNUMBER}"=""]','failed'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_noop('Found system speeddial ${ARG1}: ${SPEEDDIALNUMBER}'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_goto('end'));
			$ext->add('macro-speeddial-lookup', 's', 'failed', new ext_noop('No system or user speeddial found'), 'lookupsys',101);
			$ext->add('macro-speeddial-lookup', 's', 'end', new ext_noop('End of Speeddial-lookup'));
			
			if (!empty($callcode)) {
				$ext->add('app-speeddial', '_'.$callcode.'.', '', new ext_macro('user-callerid',''));
				$ext->add('app-speeddial', '_'.$callcode.'.', '', new ext_set('SPEEDDIALLOCATION','${EXTEN:'.(strlen($callcode)).'}'));
				$ext->add('app-speeddial', '_'.$callcode.'.', 'lookup', new ext_macro('speeddial-lookup','${SPEEDDIALLOCATION},${AMPUSER}'));
				$ext->add('app-speeddial', '_'.$callcode.'.', '', new ext_gotoif('$["${SPEEDDIALNUMBER}"=""]','failed'));
				$ext->add('app-speeddial', '_'.$callcode.'.', '', new ext_goto('1','${SPEEDDIALNUMBER}','from-internal'));
				
				$ext->add('app-speeddial', '_'.$callcode.'.', 'failed', new ext_playback('speed-dial-empty'), 'lookup',101);
				$ext->add('app-speeddial', '_'.$callcode.'.', '', new ext_congestion(''));
				
			}
			
			if (!empty($setcode)) {
				$ext->add('app-speeddial', $setcode, '', new ext_goto(1, 's', 'app-speeddial-set'));
			}
			
			
			
			$ext->add('app-speeddial-set', 's', '', new ext_macro('user-callerid',''));
			// "enter speed dial location number"
			$ext->add('app-speeddial-set', 's', 'setloc', new ext_read('newlocation','speed-enterlocation'));
			$ext->add('app-speeddial-set', 's', 'lookup', new ext_macro('speeddial-lookup','${newlocation},${AMPUSER}'));
			$ext->add('app-speeddial-set', 's', 'lookup', new ext_gotoif('$["${SPEEDDIALNUMBER}"!=""]', 'conflicts'));
			
			// "enter phone number"
			$ext->add('app-speeddial-set', 's', 'setnum', new ext_read('newnum','speed-enternumber'));
			
			
			$ext->add('app-speeddial-set', 's', 'success', new ext_dbput('AMPUSER/${AMPUSER}/speeddials/${newlocation}','${newnum}'));
			// "speed dial location "
			$ext->add('app-speeddial-set', 's', '', new ext_playback('speed-dial'));
			$ext->add('app-speeddial-set', 's', '', new ext_saydigits('${newlocation}'));
			// "is set to "
			$ext->add('app-speeddial-set', 's', '', new ext_playback('is-set-to'));
			$ext->add('app-speeddial-set', 's', '', new ext_saydigits('${newnum}'));
			$ext->add('app-speeddial-set', 's', '', new ext_hangup(''));

			
			// conflicts menu
			// "speed dial location"
			$ext->add('app-speeddial-set', 's', 'conflicts', new ext_playback('speed-dial'));
			$ext->add('app-speeddial-set', 's', '', new ext_saydigits('${newlocation}'));
			// "is already set."
			$ext->add('app-speeddial-set', 's', '', new ext_playback('is-in-use'));
			// "Press 1 to hear current phone number, 2 to pick a new location, 3 to set a new phone number"
			$ext->add('app-speeddial-set', 's', '', new ext_background('press-1&to-listen-to-it&press-2&to-enter-a-diff&location&press-3&to-change&telephone-number'));
			$ext->add('app-speeddial-set', 's', '', new ext_waitexten('60'));
			
			// "speed dial location"
			$ext->add('app-speeddial-set', '1', '', new ext_playback('speed-dial'));
			$ext->add('app-speeddial-set', '1', '', new ext_saydigits('${newlocation}'));
			// "is set to "
			$ext->add('app-speeddial-set', '1', '', new ext_playback('is-set-to'));
			$ext->add('app-speeddial-set', '1', '', new ext_saydigits('${SPEEDDIALNUMBER}'));
			$ext->add('app-speeddial-set', '1', '', new ext_goto('conflicts','s'));
			
			$ext->add('app-speeddial-set', '2', '', new ext_goto('setloc','s'));
			
			$ext->add('app-speeddial-set', '3', '', new ext_goto('setnum','s'));
			
			$ext->add('app-speeddial-set', 't', '', new ext_congestion(''));
			
			
			$ext->addInclude('from-internal-additional', 'app-speeddial');
			
		break;
        }
}

?>
