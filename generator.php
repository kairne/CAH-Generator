<?php

// If this script doesn't seem to work:
// 1) ensure you have changed $tlpath
// 2) ensure file permissions are OK. The web server user (www-data on ubuntu) must be able to create directories and files
//    in $tlpath/files, access files in the img directory and create files in the top level directory.
// 3) ensure you have imagemagick, perl and zip installed. 

// Change the following to point to the CAH-Generator top level directory.
$tlpath = "/home/web/html/cah-gen";

// ------------------------------

// Sanity check the POST content.
if (empty($_POST['card-text'])) {
   die("Error: Provide at least one card.");
}
if (empty($_POST['batch-id'])) {
   die("Error: No batch-id provided.");
}
if (empty($_POST['card-color'])) {
   die("Error: No card color selected.");
}
if (empty($_POST['icon'])) {
   die("Error: No card set selected.");
}
// $_POST['mechanic'] is optional.

$card_color = '';
$fill = '';
$icon = '';
$mechanic = '';
$card_text = explode("\n", $_POST['card-text']);
$card_count = count($card_text);
$batch = escapeshellcmd($_POST['batch-id']);
$path = "$tlpath/files/$batch";

// Valid card colors: black and white.
if ($_POST['card-color'] == 'black') {
	$card_color = 'black';
   $fill = 'white';
}
else ($_POST['card-color'] == 'white') {
   $card_color = 'white';
   $fill = 'black';
   $mechanic = '';
}
else {
   die ("Unknown card-color: $_POST['card-color']");
}

switch ($_POST['icon']) {
	case "reddit":
		$icon = 'reddit-';
		break;
	case "maple":
		$icon = 'canada-';
		break;
	case "pax":
		$icon = 'pax-';
		break;
	case "snow":
		$icon = 'christmas-';
		break;
	case "ferengi":
		$icon = 'ferengi-';
		break;
	case "reject":
		$icon = 'reject-';
		break;
	case "HOC":
		$icon = 'HOC-';
		break;
	case "box":
		$icon = 'box-';
		break;
	case "hat":
		$icon = 'hat-';
		break;
   case "retail":
		$icon = 'retail-';
		break;
	case "tabletop":
		$icon = 'tabletop-';
		break;
   case "safe":
		$icon = 'safe-';
		break;
	case "sloth":
		$icon = 'sloth-';
		break;
   case "paxeast2013A":
      $icon = 'paxeast2013A-';
      break;
   case "paxeast2013B":
      $icon = 'paxeast2013B-';
      break;
   case "paxeast2013C":
      $icon = 'paxeast2013C-';
      break;
}

switch ($_POST['mechanic']) {
	case "p2":
		$mechanic = '-mechanic-p2';
		break;
	case "d2p3":
		$mechanic = '-mechanic-d2p3';
		break;
	case "gear":
		$mechanic = '-mechanic-gears';
		break;
}

// There are currently no White Cards with Mechanics - could change
if ($card_color == 'white') {
	$mechanic = '';
}

// Mechanic cards with expansion icons have not been created yet
if ($mechanic == '-mechanic-gears') {
	$icon = '';
}

$card_back = "back-$card_color.png";
$card_front = "$icon$card_color$mechanic.png";


if ($batch != '' && $card_count < 31) {
	mkdir($path);

	foreach ($card_text as $i => $text) {

		// Replaces formatted quotations and apostrophes used by Microsoft Word
		$text = str_replace ('\“', '\"', $text);
		$text = str_replace ('\”', '\"', $text);
		$text = str_replace ('\’', '\'', $text);

		$text = escapeshellcmd($text);

		$text = str_replace ('\\\\x\\{201C\\}', '\\x{201C}', $text);
		$text = str_replace ('\\\\x\\{201D\\}', '\\x{201D}', $text);
		$text = str_replace ('\\\\x\\{2019\\}', '\\x{2019}', $text);
		$text = str_replace ('\\\\n', '\\n', $text);
		
		exec('perl -e \'binmode(STDOUT, ":utf8"); print "' . $text . '\n";\'' . " | tee -a $tlpath/card_log.txt | convert $tlpath/img/" . $card_front . ' -page +444+444 -units PixelsPerInch -background ' . $card_color . ' -fill ' . $fill . " -font $tlpath/fonts/HelveticaNeueBold.ttf -pointsize 15 -kerning -1 -density 1200 -size 2450x caption:@- -flatten " . $path . '/temp.png; mv ' . $path . '/temp.png ' . $path . '/' . $batch . '_' . $i . '.png');
	}

	exec("cd $path; zip $batch.zip *.png");
}

?>
