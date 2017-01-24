<?php
session_start();
echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="author" content="Xepheris (EU-Blackmoore)" />
<meta name="robots" content="index, nofollow" />
<meta name="language" content="en" />
<meta name="description" content="Advanced Armory Access including Artifact Power, Level, Mythics done, Highest M+, Equip including tooltips, relative colorization and many more functions!" />
<meta name="keywords" lang="en" content="advanced armory access, lookup, statistics, artifact power, artifact level, equip, wow, legion, addon, expansion, tracking, loot, council, loot council" />
<link rel="stylesheet" href="css/core.css" />
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
<title>Advanced Armory Access</title>
<script type="text/javascript" src="js/power.js"></script>
<script src="js/jquery-1.10.1.min.js"></script>
<script>
var wowhead_tooltips = {
	"hide": {
		"droppedby": true,
		"dropchance": true,
		"sellprice": true,
		"maxstack": true,
		"iconizelinks": true
	}
}
</script>
</head>
<body>
<div id="content"><h1 id="cent"><a href="http://check.artifactpower.info/"><u>A</u>dvanced <u>A</u>rmory <u>A</u>ccess</a></h1>
<p id="cent"><a href="http://guild.artifactpower.info/">Guild version including many other functions</a></p>
<p id="cent">known issues: wowhead cannot properly calculate weapon itemlevel on tooltip.</p>
<p id="cent"><img src="img/me.png" alt="404" /> = missing enchant <img src="img/mg.png" alt="404" /> = missing gem</p>';

$server_EU = array();
$server_US = array();

if(!isset($_GET['c']) || !isset($_GET['s']) || !isset($_GET['r'])) {
	echo '<form action="" method="GET" id="cent">
	
	<input type="text" name="c" value="" placeholder="character name" maxlength="12"/>
	<select name="r" id="r">
	<option value="EU">EU</option>
	<option value="US">US</option></select>
	<select name="s" id="s">
				
	</select>	
	<button type="submit">Stalk</button>
	</p>
	</form>';
}
	
if(isset($_GET['c']) && isset($_GET['s']) && isset($_GET['r'])) {
	$c = ucwords(strtolower($_GET['c']));
	$r = $_GET['r'];
	$s = $_GET['s'];
	
	include('mod/dbcon.php');
	
	// ENABLE SSL
	$arrContextOptions=array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, ),);  
	// REMOVE SPACES IN SERVER AND GUILD NAME TO PREVENT BUGS IN URL
	if(strpos($s, ' ') !== false) {
		$s = str_replace(' ', '-', $s);
	}
	
	// REMOVE SLASHES IN SERVER NAME TO ALLOW ACTUAL SEARCH AGAIN
	$s = stripslashes($s);
	
	// CHECK IF CHARACTER IS IN GUILD
	$url = 'https://' .$r. '.api.battle.net/wow/character/' .$s. '/' .$c. '?fields=items,statistics,achievements,talents&locale=en_GB&apikey=KEY_HERE';
	$data = @file_get_contents($url, false, stream_context_create($arrContextOptions));
	if($data != '') {
			
		$data = json_decode($data, true);
			
		// 110 CHECK
		if($data['level'] == '110') {
			
			// LAST LOGOUT
			$llog = substr($data['lastModified'], '0', '10');
			
			// ALL ITEMS
			$items = array('head', 'neck', 'shoulder', 'back', 'chest', 'wrist', 'hands', 'waist', 'legs', 'feet', 'finger1', 'finger2', 'trinket1', 'trinket2');
			foreach($items as $item) {
				${'' .$item. '_id'} = $data['items']['' .$item. '']['id'];
				${'' .$item. '_qual'} = $data['items']['' .$item. '']['quality'];
				${'' .$item. '_ilvl'} = $data['items']['' .$item. '']['itemLevel'];
				if(!empty($data['items']['' .$item. '']['tooltipParams']['enchant'])) {
					${'' .$item. '_ench'} = $data['items']['' .$item. '']['tooltipParams']['enchant'];
				}
				if(!empty($data['items']['' .$item. '']['tooltipParams']['gem0'])) {
					${'' .$item. '_gem0'} = $data['items']['' .$item. '']['tooltipParams']['gem0'];
				}
				else {
					${'' .$item. '_gem0'} = '';
				}
				foreach($data['items']['' .$item. '']['bonusLists'] as $bonus) {
					if(!isset(${'' .$item. '_bonus'})) {
						${'' .$item. '_bonus'} = $bonus;
					}
					elseif(isset(${'' .$item. '_bonus'})) {
						${'' .$item. '_bonus'}.= ':' .$bonus. '';
					}
				}						
			}
		
			$class = $data['class'];		
			
			for($i = '0'; $i <= '4'; $i++) {
				if($specc == '') {
					if($data['talents'][$i]['selected'] == '1') {
						for($k = '0'; $k <= '7'; $k++) {					
							if(isset($data['talents'][$i]['talents'][$k]['spec']['name'])) {
								$specc = $data['talents'][$i]['talents'][$k]['spec']['name'];
							}
						}
					}
				}
			}
			
			$weapon = mysqli_fetch_array(mysqli_query($stream, "SELECT `w` FROM `weapons` WHERE `s` = '" .$specc. "' AND `id` = '" .$class. "'"));
			
			if($data['items']['mainHand']['id'] == $weapon['w']) {
				
				$mhilvl = $data['items']['mainHand']['itemLevel'];
				if(!empty($data['items']['offHand']['itemLevel'])) {
					$ohilvl = $data['items']['offHand']['itemLevel'];
				}
					
			foreach($data['items']['mainHand']['bonusLists'] as $bonus) {
					if(!isset($mh_bonus)) {
						$mh_bonus = $bonus;
					}
				elseif(isset($mh_bonus)) {
					$mh_bonus.= ':' .$bonus. '';
					}
				}
				if(!empty($data['items']['mainHand']['relics'])) {
					$i = '0';
						foreach($data['items']['mainHand']['relics'] as $relic) {
							${'mhrelic' .$i. ''} = $relic['itemId'];
				
							foreach($relic['bonusLists'] as $bonus) {
								if(!isset(${'mhrelicbonus' .$i. ''})) {
									${'mhrelicbonus' .$i. ''} = $bonus;
								}
							elseif(isset(${'mhrelicbonus' .$i. ''})) {
								${'mhrelicbonus' .$i. ''}.= ':' .$bonus. '';
							}
						}
					$i++;
					}
				}
			}
			elseif($data['items']['offHand']['id'] == $weapon['w']) {
				$ohilvl = $data['items']['offHand']['itemLevel'];
				if(!empty($data['items']['mainHand']['itemLevel'])) {
					$mhilvl = $data['items']['mainHand']['itemLevel'];
				}
			
				foreach($data['items']['offHand']['bonusLists'] as $bonus) {
					if(!isset($oh_bonus)) {
						$oh_bonus = $bonus;
					}
					elseif(isset($oh_bonus)) {
						$oh_bonus.= ':' .$bonus. '';
					}
				}
			
				if(!empty($data['items']['offHand']['relics'])) {
					$i = '0';
					foreach($data['items']['offHand']['relics'] as $relic) {
						${'ohrelic' .$i. ''} = $relic['itemId'];
				
						foreach($relic['bonusLists'] as $bonus) {
							if(!isset(${'ohrelicbonus' .$i. ''})) {
								${'ohrelicbonus' .$i. ''} = $bonus;
							}
							elseif(isset(${'ohrelicbonus' .$i. ''})) {
								${'ohrelicbonus' .$i. ''}.= ':' .$bonus. '';
							}
						}
					$i++;
					}
				}
			}
		
			// EQUIPPED ITEMLEVEL						
			$ilvlaverage = $data['items']['averageItemLevelEquipped'];
			
			// BAG ITEMLEVEL						
			$ilvlaveragebags = $data['items']['averageItemLevel'];
			
			// RAID PROGRESS MYTHIC
			
			// EMERALD NIGHTMARE
			$en = '0';					
			$enarray = array($data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['33']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['37']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['41']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['45']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['49']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['53']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['57']['quantity']);
			
			if(in_array('11194', $data['achievements']['achievementsCompleted'])) {
				$aotc_en = 'green';
			}
			else {
				$aotc_en = 'red';
			}
			if(in_array('11191', $data['achievements']['achievementsCompleted'])) {
				$ce_en = 'green';
			}
			else {
				$ce_en = 'red';
			}
					
			foreach($enarray as $enmythic) {
				if($enmythic > '0') {
					$en++;
				}
			}
			
			// TRIAL OF FAILURE
			
			$tov = '0';					
			$tovarray = array($data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['61']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['65']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['69']['quantity']);
			
			if(in_array('11581', $data['achievements']['achievementsCompleted'])) {
				$aotc_tov = 'green';
			}
			else {
				$aotc_tov = 'red';
			}
			
			if(in_array('11580', $data['achievements']['achievementsCompleted'])) {
				$ce_tov = 'green';
			}
			else {
				$ce_tov = 'red';
			}
								
			foreach($tovarray as $tovmythic) {
				if($tovmythic > '0') {
					$tov++;
				}
			}
			
			// NIGHTHOLD
			
			$nh = '0';
			$nharray = array($data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['73']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['77']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['81']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['85']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['89']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['93']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['97']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['101']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['105']['quantity'], $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['109']['quantity']);
			
			if(in_array('11195', $data['achievements']['achievementsCompleted'])) {
				$aotc_nh = 'green';
			}
			else {
				$aotc_nh = 'red';
			}
			
			if(in_array('11192', $data['achievements']['achievementsCompleted'])) {
				$ce_nh = 'green';
			}
			else {
				$ce_nh = 'red';
			}
			
			foreach($nharray as $nhmythic) {
				if($nhmythic > '0') {
					$nh++;
				}
			}
			
			// TOMB OF SARGERAS
			
			$tos = '0';
			$tosarray = array();
			
			$aotc_tos = 'grey';
			$ce_tos = 'grey';
					
			// MYTHIC AND MYTHIC PLUS STATS
			$eoa = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['2']['quantity'];
			$dht = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['5']['quantity'];
			$nel = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['8']['quantity'];
			$hov = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['11']['quantity'];
			$vh1 = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['16']['quantity'];
			$vh2 = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['17']['quantity'];
			$vh = $vh1+$vh2;
			$vow = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['20']['quantity'];
			$brh = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['23']['quantity'];
			$mos = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['26']['quantity'];
			$arc = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['27']['quantity'];
			$cos = $data['statistics']['subCategories']['5']['subCategories']['6']['statistics']['28']['quantity'];
					
			$mythicsum = $eoa+$dht+$nel+$hov+$vh+$vow+$brh+$mos+$arc+$cos;
			
			// HIGHEST M+ IN TIME ACCORDING TO ACHIEVEMENTS
			if(in_array('11162', $data['achievements']['achievementsCompleted'])) {
				$mplus = '<span style="color: green;">15</span>';
			}	
			elseif(in_array('11185', $data['achievements']['achievementsCompleted'])) {
				$mplus = '<span style="color: orange;">10</span>';
			}
			elseif(in_array('11184', $data['achievements']['achievementsCompleted'])) {
				$mplus = '<span style="color: red;">5';
			}
			elseif(in_array('11183', $data['achievements']['achievementsCompleted'])) {
				$mplus = '2';
			}
					
			// ARTIFACT POWER AND LEVEL
			$key = array_search('30103', $data['achievements']['criteria']);
			$key2 = array_search('29395', $data['achievements']['criteria']);
			if(in_array('11162', $data['achievements']['achievementsCompleted'])) {
				$key3 = array_search('11162', $data['achievements']['achievementsCompleted']);
			}
		
			if($key != '') {
				$criterias = array();
				array_push($criterias, $data['achievements']['criteriaQuantity']);
				$criterias = $criterias['0'];
				$totalgained = $criterias[$key];
				$alevel = $criterias[$key2];
				if(in_array('11162', $data['achievements']['achievementsCompleted'])) {
					$timestamp = array();
					array_push($timestamp, $data['achievements']['achievementsCompletedTimestamp']);
					$timestamp = $timestamp['0'];
					$timestamp_15 = $timestamp[$key3];
				}
			}
			elseif($key == '') {
				$totalgained = '0';
				$alevel = '0';
				$timestamp_15 = '';
			}
			
			if(!empty($timestamp_15)) {
				$timestamp_15 = substr($timestamp_15, '0', '-3');
				
				$affix1 = '<a href="http://www.wowhead.com/affix=1/"><img src="img/1.jpg" /></a> ';
				$affix2 = '<a href="http://www.wowhead.com/affix=2/"><img src="img/2.jpg" /></a> ';
				$affix3 = '<a href="http://www.wowhead.com/affix=3/"><img src="img/3.jpg" /></a> ';
				$affix4 = '<a href="http://www.wowhead.com/affix=4/"><img src="img/4.jpg" /></a> ';
				$affix5 = '<a href="http://www.wowhead.com/affix=5/"><img src="img/5.jpg" /></a> ';
				$affix6 = '<a href="http://www.wowhead.com/affix=6/"><img src="img/6.jpg" /></a> ';
				$affix7 = '<a href="http://www.wowhead.com/affix=7/"><img src="img/7.jpg" /></a> ';
				$affix8 = '<a href="http://www.wowhead.com/affix=8/"><img src="img/8.jpg" /></a> ';
				$affix9 = '<a href="http://www.wowhead.com/affix=9/"><img src="img/9.jpg" /></a> ';
				$affix10 = '<a href="http://www.wowhead.com/affix=10/"><img src="img/10.jpg" /></a> ';
							
				// WEEK 1 US RAGING VOLCANIC TYRANNICAL
				// WEEK 1 EU SANGUINE OVERFLOWING TYRANNICAL
											
				if($r == 'EU') {				
					for($i = '0'; $i <= '96'; $i++) {
						if(!isset($week_from_start)) {
							$week_start = 1474009200+60*60*24*7*$i;
							$week_end = 1474009200+60*60*24*7*($i+1);
						
							if($timestamp_15 > $week_start && $timestamp_15 < $week_end) {
								$week_from_start = $i+1;
							}
							
							if($week_from_start == '1' || $week_from_start == '9' || $week_from_start == '17' || $week_from_start == '25' || $week_from_start == '33' || $week_from_start == '41' || $week_from_start == '49' || $week_from_start == '57' || $week_from_start == '65' || $week_from_start == '73' || $week_from_start == '81' || $week_from_start == '89') {
								$affixes = $affix8.$affix1.$affix9; // SANGUINE OVERFLOWING TYRANNICAL
							}
							elseif($week_from_start == '2' || $week_from_start == '10' || $week_from_start == '18' || $week_from_start == '26' || $week_from_start == '34' || $week_from_start == '42' || $week_from_start == '50' || $week_from_start == '58' || $week_from_start == '66' || $week_from_start == '74' || $week_from_start == '82' || $week_from_start == '90') {
								$affixes = $affix5.$affix2.$affix10; // TEEMING SKITTISH FORTIFIED
							}
							elseif($week_from_start == '3' || $week_from_start == '11' || $week_from_start == '19' || $week_from_start == '27' || $week_from_start == '35' || $week_from_start == '43' || $week_from_start == '51' || $week_from_start == '59' || $week_from_start == '67' || $week_from_start == '75' || $week_from_start == '83' || $week_from_start == '91') {
								$affixes = $affix6.$affix4.$affix10; // RAGING NECROTIC FORTIFIED
							}
							elseif($week_from_start == '4' || $week_from_start == '12' || $week_from_start == '20' || $week_from_start == '28' || $week_from_start == '36' || $week_from_start == '44' || $week_from_start == '52' || $week_from_start == '60' || $week_from_start == '68' || $week_from_start == '76' || $week_from_start == '84' || $week_from_start == '92') {
								$affixes = $affix7.$affix1.$affix9; // BOLSTERING OVERFLOWING TYRANNICAL
							}
							elseif($week_from_start == '5' || $week_from_start == '13' || $week_from_start == '21' || $week_from_start == '29' || $week_from_start == '37' || $week_from_start == '45' || $week_from_start == '53' || $week_from_start == '61' || $week_from_start == '69' || $week_from_start == '77' || $week_from_start == '85' || $week_from_start == '93') {
								$affixes = $affix8.$affix3.$affix10; // SANGUINE VOLCANIC FORTIFIED
							}
							elseif($week_from_start == '6' || $week_from_start == '14' || $week_from_start == '22' || $week_from_start == '30' || $week_from_start == '38' || $week_from_start == '46' || $week_from_start == '54' || $week_from_start == '62' || $week_from_start == '70' || $week_from_start == '78' || $week_from_start == '86' || $week_from_start == '94') {
								$affixes = $affix5.$affix4.$affix9; // TEEMING NECROTIC TYRANNICAL
							}
							elseif($week_from_start == '7' || $week_from_start == '15' || $week_from_start == '23' || $week_from_start == '31' || $week_from_start == '39' || $week_from_start == '47' || $week_from_start == '55' || $week_from_start == '63' || $week_from_start == '71' || $week_from_start == '79' || $week_from_start == '87' || $week_from_start == '95') {
								$affixes = $affix6.$affix3.$affix9; // RAGING VOLCANIC TYRANNICAL
							}
							elseif($week_from_start == '8' || $week_from_start == '16' || $week_from_start == '24' || $week_from_start == '32' || $week_from_start == '40' || $week_from_start == '48' || $week_from_start == '56' || $week_from_start == '64' || $week_from_start == '72' || $week_from_start == '80' || $week_from_start == '88' || $week_from_start == '96') {
								$affixes = $affix7.$affix3.$affix9; // BOLSTERING SKITTISH FORTIFIED
							}
						}
					}
				}
				elseif($r == 'US') {
					for($i = '0'; $i <= '96'; $i++) {
						if(!isset($week_from_start)) {
							$week_start = 1473922800+60*60*24*7*$i;
							$week_end = 1473922800+60*60*24*7*($i+1);
						
							if($timestamp_15 > $week_start && $timestamp_15 < $week_end) {
								$week_from_start = $i+1;
							}
							
							if($week_from_start == '1' || $week_from_start == '9' || $week_from_start == '17' || $week_from_start == '25' || $week_from_start == '33' || $week_from_start == '41' || $week_from_start == '49' || $week_from_start == '57' || $week_from_start == '65' || $week_from_start == '73' || $week_from_start == '81' || $week_from_start == '89') {
								$affixes = $affix6.$affix3.$affix9; // RAGING VOLCANIC TYRANNICAL
							}
							elseif($week_from_start == '2' || $week_from_start == '10' || $week_from_start == '18' || $week_from_start == '26' || $week_from_start == '34' || $week_from_start == '42' || $week_from_start == '50' || $week_from_start == '58' || $week_from_start == '66' || $week_from_start == '74' || $week_from_start == '82' || $week_from_start == '90') {
								$affixes = $affix7.$affix3.$affix9; // BOLSTERING SKITTISH FORTIFIED
							}
							elseif($week_from_start == '3' || $week_from_start == '11' || $week_from_start == '19' || $week_from_start == '27' || $week_from_start == '35' || $week_from_start == '43' || $week_from_start == '51' || $week_from_start == '59' || $week_from_start == '67' || $week_from_start == '75' || $week_from_start == '83' || $week_from_start == '91') {
								$affixes = $affix8.$affix1.$affix9; // SANGUINE OVERFLOWING TYRANNICAL
							}
							elseif($week_from_start == '4' || $week_from_start == '12' || $week_from_start == '20' || $week_from_start == '28' || $week_from_start == '36' || $week_from_start == '44' || $week_from_start == '52' || $week_from_start == '60' || $week_from_start == '68' || $week_from_start == '76' || $week_from_start == '84' || $week_from_start == '92') {
								$affixes = $affix5.$affix2.$affix10; // TEEMING SKITTISH FORTIFIED
							}
							elseif($week_from_start == '5' || $week_from_start == '13' || $week_from_start == '21' || $week_from_start == '29' || $week_from_start == '37' || $week_from_start == '45' || $week_from_start == '53' || $week_from_start == '61' || $week_from_start == '69' || $week_from_start == '77' || $week_from_start == '85' || $week_from_start == '93') {
								$affixes = $affix6.$affix4.$affix10; // RAGING NECROTIC FORTIFIED
							}
							elseif($week_from_start == '6' || $week_from_start == '14' || $week_from_start == '22' || $week_from_start == '30' || $week_from_start == '38' || $week_from_start == '46' || $week_from_start == '54' || $week_from_start == '62' || $week_from_start == '70' || $week_from_start == '78' || $week_from_start == '86' || $week_from_start == '94') {
								$affixes = $affix7.$affix1.$affix9; // BOLSTERING OVERFLOWING TYRANNICAL
							}
							elseif($week_from_start == '7' || $week_from_start == '15' || $week_from_start == '23' || $week_from_start == '31' || $week_from_start == '39' || $week_from_start == '47' || $week_from_start == '55' || $week_from_start == '63' || $week_from_start == '71' || $week_from_start == '79' || $week_from_start == '87' || $week_from_start == '95') {
								$affixes = $affix8.$affix3.$affix10; // SANGUINE VOLCANIC FORTIFIED
							}
							elseif($week_from_start == '8' || $week_from_start == '16' || $week_from_start == '24' || $week_from_start == '32' || $week_from_start == '40' || $week_from_start == '48' || $week_from_start == '56' || $week_from_start == '64' || $week_from_start == '72' || $week_from_start == '80' || $week_from_start == '88' || $week_from_start == '96') {
								$affixes = $affix5.$affix4.$affix9; // TEEMING NECROTIC TYRANNICAL								
							}
						}
					}
					
				}
			}
		
			if(strpos($s, '-') !== false) {
				$s = str_replace('-', ' ', $s);
			}
		}
	}	
		
	if($alevel < '35') {
		$alevelthreshold = 'red';
	}
	elseif($alevel >= '35' && $alevel < '54') {
		$alevelthreshold = 'orange';
	}
	elseif($alevel == '54') {
		$alevelthreshold = 'green';
	}
	
	echo '<div class="t">
	<div class="tb">
	<div class="tr">';
	
	$weapon_id = mysqli_fetch_array(mysqli_query($stream, "SELECT `w` FROM `weapons` WHERE `s` = '" .$specc. "' AND `id` = '" .$class. "'"));

	$class = mysqli_fetch_array(mysqli_query($stream, "SELECT `class`, `color` FROM `classes` WHERE `id` = '" .$class. "'"));

	$columnarray = array('', 'Class', 'Role', 'Total AP', 'AL', 'Equipped', 'Bags', 'Weapon', 'Mythics', 'M+ Achvmt', 'EN', 'ToV', 'NH', 'ToS');
		
	foreach($columnarray as $column) {
		echo '<div class="tc" style="border-bottom: 1px solid grey;">' .$column. '</div>';
	}
	echo '</div>';
	
	echo '<div class="tr" style="border-bottom: 1px solid grey;">	
	<div class="tc"><a href="http://' .$r. '.battle.net/wow/en/character/' .$s. '/' .$c. '/simple" title="logged out: ' .round(((time('now')-$llog)/3600), 2). ' hrs. ago">' .$c. '</a></span></div>
	<div class="tc" style="background:' .$class['color']. ';">' .$class['class']. '</div>
	<div class="tc">' .$specc. '</div>
	<div class="tc">' .number_format($totalgained). '</div>
	<div class="tc"><span style="color: ' .$alevelthreshold. ';">' .$alevel. '</span></div>
	<div class="tc">' .$ilvlaverage. '</div>
	<div class="tc">' .$ilvlaveragebags. '</div>';
	
	if(empty($data['oh_bonus'])) {
		$weapon = '<a href="http://wowhead.com/item=' .$weapon_id['w']. '&bonus=' .$mh_bonus. '" rel="gems=' .$mhrelic0. ':' .$mhrelic1. ':' .$mhrelic2. '">' .$mhilvl. '</a>';
	}
	else {
		$weapon = '<a href="http://wowhead.com/item=' .$weapon_id['w']. '&bonus=' .$oh_bonus. '" rel="gems=' .$ohrelic0. ':' .$ohrelic1. ':' .$ohrelic2. '">' .$ohilvl. '</a>';
	}
	
	echo '<div class="tc">' .$weapon. '</div>
	<div class="tc"><span title="ARC ' .$arc. ' BRH ' .$brh. ' COS ' .$cos. ' DHT ' .$dht. ' EOA ' .$eoa. ' HOV ' .$hov. ' MOS ' .$mos. ' NEL ' .$nel. ' VOW ' .$vow. ' VH ' .$vh. '">' .$mythicsum. '</span></div>';
	if($en == '0') { $color_en = 'style="color: red;"'; } elseif($en == '7') { $color_en = 'style="color: green;"'; } elseif($en > '0') { $color_en = 'style="color: orange;"'; }			
	if($tov == '0') { $color_tov = 'style="color: red;"'; } elseif($tov == '3') { $color_tov = 'style="color: green;"'; } elseif($tov > '0') { $color_tov = 'style="color: orange;"'; }
	if($nh == '0') { $color_nh = 'style="color: red;"'; } elseif($nh == '10') { $color_nh = 'style="color: green;"'; } elseif($nh > '0') { $color_nh = 'style="color: orange;"'; }
	if($tos == '0') { $color_tos = 'style="color: red;"'; } elseif($tos == '10') { $color_tos = 'style="color: green;"'; } elseif($tos > '0') { $color_tos = 'style="color: orange;"'; }
			
	echo '<div class="tc">' .$mplus. ' ' .$affixes. '</div>
	<div class="tc"><span ' .$color_en. '>' .$en. '/7</span></div>
	<div class="tc"><span ' .$color_tov. '>' .$tov. '/3</span></div>
	<div class="tc"><span ' .$color_nh. '>' .$nh. '/10</span></div>
	<div class="tc"><span ' .$color_tos. '>' .$tos. '/9</span></div></div>
	<div class="tr">';	
	
	$columnarray = array('Head', 'Neck', 'Shoulder', 'Back', 'Chest', 'Wrist', 'Hands', 'Waist', 'Legs', 'Feet', 'Rng1', 'Rng2', 'Trnkt1', 'Trnkt2');
	foreach($columnarray as $column) {
		echo '<div class="tc">' .$column. '</div>';
	}
	echo '</div>
	<div class="tr">';
	
	$items = array('head', 'neck', 'shoulder', 'back', 'chest', 'wrist', 'hands', 'waist', 'legs', 'feet', 'finger1', 'finger2', 'trinket1', 'trinket2');
	foreach($items as $item) {
		$socketcheck = strpos(${'' .$item. '_gem0'}, '1808');
		if(${'' .$item. '_gem0'} != '0') { $gem = '<a href="http://wowhead.com/item=' .${'' .$item. '_gem0'}. '&lvl=110"><img src="" /></a>'; }			
		elseif(${'' .$item. '_gem0'} == '0' && $socketcheck > '-1') { $gem = '<img src="img/mg.png" alt="404" />'; }
					
		if($item == 'neck' || $item == 'shoulder' || $item == 'back' || $item == 'finger1' || $item == 'finger2') {
			if(${'' .$item. '_ench'} == '') {
				$enchant = '<img src="img/me.png" alt="404" />';
			}
			elseif(${'' .$item. '_ench'} != '') {#
				$swaparray = array('5437' => '128551', '5439' => '128553', '5883' => '140219', '5889' => '141908', '5890' => '141909', '5891' => '141910', '5431' => '128545', '5432' => '128546', '5433' => '128547', '5434' => '128548', '5435' => '128549', '5436' => '128550', '5423' => '128537', '5424' => '128538', '5425' => '128539', '5426' => '128540', '5427' => '128541', '5428' => '128542', '5429' => '128543', '5430' => '128544', '5442' => '140214', '5882' => '140218', '5440' => '128554', '5883' => '140219', '5441' => '140213', '5443' => '140215', '5881' => '140217');
							
				foreach($swaparray as $old => $new) {
					if($data['' .$row. '_e'] == $old) {
						$enchant = '<a href="http://wowhead.com/item=' .$new. '"><img src="" /></a>';
					}
				}
			}
		}
		if(${'' .$item. '_ilvl'} >= '905') { $quality = 'style="color: green;"'; }
		if(${'' .$item. '_ilvl'} >= '870' && ${'' .$item. '_ilvl'} < '905') { $quality = 'style="color: orange;"'; }
		if(${'' .$item. '_ilvl'} < '870') { $quality = 'style="color: red;"'; }
		echo '<div class="tc" style="border-bottom: 1px solid grey;"><a href="http://wowhead.com/item=' .${'' .$item. '_id'}. '&bonus=' .${'' .$item. '_bonus'}. '" rel="gems=' .${'' .$item. '_gem0'}. '&ench=' .${'' .$item. '_ench'}. '" ' .$quality. '>' .${'' .$item. '_ilvl'}. '</a> ' .$gem. ' ' .$enchant. '</div>';
		unset($gem); unset($enchant);
	}
	echo '</div></div></div>
	<div class="t">
	<div class="tr">
	<div class="tc"><a href="http://www.wowhead.com/achievement=11194/" style="text-decoration: none; color: ' .$aotc_en. ';">AOTC: Xavius</a></div>
	<div class="tc"><a href="http://www.wowhead.com/achievement=11191/" style="text-decoration: none; color: ' .$ce_en. ';">CE: Xavius</a></div>
	<div class="tc"><a href=http://www.wowhead.com/achievement=11581/" style="text-decoration: none; color: ' .$aotc_tov. ';">AOTC: Helya</a></div>
	<div class="tc"><a href="http://www.wowhead.com/achievement=11580/" style="text-decoration: none; color: ' .$ce_tov. ';">CE: Helya</a></div>
	<div class="tc"><a href="http://www.wowhead.com/achievement=11195/" style="text-decoration: none; color: ' .$aotc_nh. ';">AOTC: Gul\'dan</a></div>
	<div class="tc"><a href="http://www.wowhead.com/achievement=11192/" style="text-decoration: none; color: ' .$ce_nh. ';">CE: Gul\'dan</a></div>
	<div class="tc"><span style="color: ' .$aotc_tos. ';">AOTC: Kil\'jaeden</span></div>
	<div class="tc"><span style="color: ' .$ce_tos. ';">CE: Kil\'jaeden</span></div>
	</div>
	</div>';
}


echo '</div>
<p id="cent" style="font-size: 12px; text-align: center;"><a href="https://github.com/xepheris/World-of-Warcraft---Legion-Advanced-Armory-Access">source code</a></p>
</body>
</html>';


?>
<script type="text/javascript">
server_EU=new Array("Aegwynn","Aerie Peak","Agamaggan","Aggra","Aggramar","Ahn'Qiraj","Al'Akir","Alexstrasza","Alleria","Alonsus","Aman'Thul","Ambossar","Anachronos","Anetheron","Antonidas","Anub'arak","Arak-arahm","Arathi","Arathor","Archimonde","Area 52","Argent Dawn","Arthas","Arygos","Aszune","Auchindoun","Azjol-Nerub","Azshara","Azuremyst","Baelgun","Balnazzar","Blackhand","Blackmoore","Blackrock","Blade's Edge","Bladefist","Bloodfeather","Bloodhoof","Bloodscalp","Blutkessel","Boulderfist","Bronze Dragonflight","Bronzebeard","Burning Blade","Burning Legion","Burning Steppes","C'Thun","Chamber of Aspects","Chants \u00e9ternels","Cho'gall","Chromaggus","Colinas Pardas","Confr\u00e9rie du Thorium","Conseil des Ombres","Crushridge","Culte de la Rive Noire","Daggerspine","Dalaran","Dalvengyr","Darkmoon Faire","Darksorrow","Darkspear","Das Konsortium","Das Syndikat","Deathwing","Defias Brotherhood","Dentarg","Der abyssische Rat","Der Mithrilorden","Der Rat von Dalaran","Destromath","Dethecus","Die Aldor","Die Arguswacht","Die ewige Wacht","Die Nachtwache","Die Silberne Hand","Die Todeskrallen","Doomhammer","Draenor","Dragonblight","Dragonmaw","Drak'thul","Drek'Thar","Dun Modr","Dun Morogh","Dunemaul","Durotan","Earthen Ring","Echsenkessel","Eitrigg","Eldre'Thalas","Elune","Emerald Dream","Emeriss","Eonar","Eredar","Euskal Encounter","Executus","Exodar","Festung der St\u00fcrme","Forscherliga","Frostmane","Frostmourne","Frostwhisper","Frostwolf","Garona","Garrosh","Genjuros","Ghostlands","Gilneas","Gorgonnash","Grim Batol","Gul'dan","Hakkar","Haomarush","Hellfire","Hellscream","Hyjal","Illidan","Jaedenar","Kael'Thas","Karazhan","Kargath","Kazzak","Kel'Thuzad","Khadgar","Khaz Modan","Khaz'goroth","Kil'Jaeden","Kilrogg","Kirin Tor","Kor'gall","Krag'jin","Krasus","Kul Tiras","Kult der Verdammten","La Croisade \u00e9carlate","Laughing Skull","Les Clairvoyants","Les Sentinelles","Lightbringer","Lightning's Blade","Lordaeron","Los Errantes","Lothar","Madmortem","Magtheridon","Mal'Ganis","Malfurion","Malorne","Malygos","Mannoroth","Mar\u00e9cage de Zangar","Mazrigos","Medivh","Minahonda","Molten Core","Moonglade","Mug'thol","Nagrand","Nathrezim","Naxxramas","Nazjatar","Nefarian","Nemesis","Neptulon","Ner'zhul","Nera'thor","Nethersturm","Nordrassil","Norgannon","Nozdormu","Onyxia","Outland","Perenolde","Pozzo dell'Eternit\u00e0","Proudmoore","Quel'Thalas","Ragnaros","Rajaxx","Rashgarroth","Ravencrest","Ravenholdt","Rexxar","Runetotem","Sanguino","Sargeras","Saurfang","Scarshield Legion","Sen'jin","Shadowmoon","Shadowsong","Shattered Halls","Shattered Hand","Shattrath","Shen'dralar","Silvermoon","Sinstralis","Skullcrusher","Spinebreaker","Sporeggar","Steamwheedle Cartel","Stonemaul","Stormrage","Stormreaver","Stormscale","Sunstrider","Suramar","Sylvanas","Taerar","Talnivarr","Tarren Mill","Teldrassil","Temple noir","Terenas","Terokkar","Terrordar","The Maelstrom","The Sha'tar","The Venture Co","Theradras","Thrall","Throk'Feroth","Thunderhorn","Tichondrius","Tirion","Todeswache","Trollbane","Turalyon","Twilight's Hammer","Twisting Nether","Tyrande","Uldaman","Uldum","Un'Goro","Varimathras","Vashj","Vek'lor","Vek'nilash","Vol'jin","Warsong","Wildhammer","Wrathbringer","Xavius","Ysera","Ysondre","Zenedar","Zirkel des Cenarius","Zul'jin","Zuluhed","\u0410\u0437\u0443\u0440\u0435\u0433\u043e\u0441","\u0411\u043e\u0440\u0435\u0439\u0441\u043a\u0430\u044f \u0442\u0443\u043d\u0434\u0440\u0430","\u0412\u0435\u0447\u043d\u0430\u044f \u041f\u0435\u0441\u043d\u044f","\u0413\u0430\u043b\u0430\u043a\u0440\u043e\u043d\u0434","\u0413\u043e\u043b\u0434\u0440\u0438\u043d\u043d","\u0413\u043e\u0440\u0434\u0443\u043d\u043d\u0438","\u0413\u0440\u043e\u043c","\u0414\u0440\u0430\u043a\u043e\u043d\u043e\u043c\u043e\u0440","\u041a\u043e\u0440\u043e\u043b\u044c-\u043b\u0438\u0447","\u041f\u0438\u0440\u0430\u0442\u0441\u043a\u0430\u044f \u0431\u0443\u0445\u0442\u0430","\u041f\u043e\u0434\u0437\u0435\u043c\u044c\u0435","\u0420\u0430\u0437\u0443\u0432\u0438\u0439","\u0420\u0435\u0432\u0443\u0449\u0438\u0439 \u0444\u044c\u043e\u0440\u0434","\u0421\u0432\u0435\u0436\u0435\u0432\u0430\u0442\u0435\u043b\u044c \u0414\u0443\u0448","\u0421\u0435\u0434\u043e\u0433\u0440\u0438\u0432","\u0421\u0442\u0440\u0430\u0436 \u0421\u043c\u0435\u0440\u0442\u0438","\u0422\u0435\u0440\u043c\u043e\u0448\u0442\u0435\u043f\u0441\u0435\u043b\u044c","\u0422\u043a\u0430\u0447 \u0421\u043c\u0435\u0440\u0442\u0438","\u0427\u0435\u0440\u043d\u044b\u0439 \u0428\u0440\u0430\u043c","\u042f\u0441\u0435\u043d\u0435\u0432\u044b\u0439 \u043b\u0435\u0441");
server_US=new Array("Aegwynn","Aerie Peak","Agamaggan","Aggramar","Akama","Alexstrasza","Alleria","Altar of Storms","Alterac Mountains","Aman'Thul","Andorhal","Anetheron","Antonidas","Anub'arak","Anvilmar","Arathor","Archimonde","Area 52","Argent Dawn","Arthas","Arygos","Auchindoun","Azgalor","Azjol-Nerub","Azralon","Azshara","Azuremyst","Baelgun","Balnazzar","Barthilas","Black Dragonflight","Blackhand","Blackrock","Blackwater Raiders","Blackwing Lair","Blade's Edge","Bladefist","Bleeding Hollow","Blood Furnace","Bloodhoof","Bloodscalp","Bonechewer","Borean Tundra","Boulderfist","Bronzebeard","Burning Blade","Burning Legion","Caelestrasz","Cairne","Cenarion Circle","Cenarius","Cho'gall","Chromaggus","Coilfang","Crushridge","Daggerspine","Dalaran","Dalvengyr","Dark Iron","Darkspear","Darrowmere","Dath'Remar","Dawnbringer","Deathwing","Demon Soul","Dentarg","Destromath","Dethecus","Detheroc","Doomhammer","Draenor","Dragonblight","Dragonmaw","Drak'tharon","Drak'thul","Draka","Drakkari","Dreadmaul","Drenden","Dunemaul","Durotan","Duskwood","Earthen Ring","Echo Isles","Eitrigg","Eldre'Thalas","Elune","Emerald Dream","Eonar","Eredar","Executus","Exodar","Farstriders","Feathermoon","Fenris","Firetree","Fizzcrank","Frostmane","Frostmourne","Frostwolf","Galakrond","Gallywix","Garithos","Garona","Garrosh","Ghostlands","Gilneas","Gnomeregan","Goldrinn","Gorefiend","Gorgonnash","Greymane","Grizzly Hills","Grizzly Hills","Gul'dan","Gundrak","Gurubashi","Hakkar","Haomarush","Hellscream","Hydraxis","Hyjal","Icecrown","Illidan","Jaedenar","Jubei'Thos","Kael'thas","Kalecgos","Kargath","Kel'Thuzad","Khadgar","Khaz Modan","Khaz'goroth","Kil'Jaeden","Kilrogg","Kirin Tor","Korgath","Korialstrasz","Kul Tiras","Laughing Skull","Lethon","Lightbringer","Lightning's Blade","Lightninghoof","Llane","Lothar","Madoran","Maelstrom","Magtheridon","Maiev","Mal'Ganis","Malfurion","Malorne","Malygos","Mannoroth","Medivh","Misha","Mok'Nathal","Moon Guard","Moonrunner","Mug'thol","Muradin","Nagrand","Nathrezim","Nazgrel","Nazjatar","Nemesis","Ner'zhul","Nesingwary","Nordrassil","Norgannon","Onyxia","Perenolde","Proudmoore","Quel'Dorei","Quel'Thalas","Ragnaros","Ravencrest","Ravenholdt","Rexxar","Rivendare","Runetotem","Sargeras","Saurfang","Scarlet Crusade","Scilla","Sen'Jin","Sentinels","Shadow Council","Shadowmoon","Shadowsong","Shandris","Shattered Halls","Shattered Hand","Shu'Halo","Silver Hand","Silvermoon","Sisters of Elune","Skullcrusher","Skywall","Smolderthorn","Spinebreaker","Spirestone","Staghelm","Steamwheedle Cartel","Stonemaul","Stormrage","Stormreaver","Stormscale","Suramar","Tanaris","Terenas","Terokkar","Thaurissan","The Forgotten Coast","The Scryers","The Underbog","The Venture Co","Thorium Brotherhood","Thrall","Thunderhorn","Thunderlord","Tichondrius","Tol Barad","Tortheldrin","Trollbane","Turalyon","Twisting Nether","Uldaman","Uldum","Undermine","Ursin","Uther","Vashj","Vek'nilash","Velen","Warsong","Whisperwind","Wildhammer","Windrunner","Winterhoof","Wyrmrest Accord","Ysera","Ysondre","Zangarmarsh","Zul'jin","Zuluhed");
		
populateSelect();
			
$(function() {
	$('#r').change(function(){
			populateSelect();
		});
	});
			
	function populateSelect(){
		region=$('#r').val();
		$('#s').html('');
		
		if(region=='EU'){
			server_EU.forEach(function(t) { 
				$('#s').append('<option>'+t+'</option>');
			});
		}
		
		if(region=='US'){
			server_US.forEach(function(t) {
				$('#s').append('<option>'+t+'</option>');
			});
		}
}
</script>	