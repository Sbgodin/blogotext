<?
/************************************************************\
*
*		freeCap v1.4.1 Copyright 2005 Howard Yeend
*		www.puremango.co.uk
*
*    This file is part of freeCap.
*
*    freeCap is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    freeCap is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with freeCap; if not, write to the Free Software
*    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
*
\************************************************************/

session_start();

//////////////////////////////////////////////////////
////// User Defined Vars:
//////////////////////////////////////////////////////

$site_tags[0] = "To avoid spam, please do NOT enter the text if";
$site_tags[1] = "this site is not puremango.co.uk";
// or if you don't want any text:
$site_tags = null;

// where to write the above:
// 0=top
// 1=bottom
// 2=both
$tag_pos = 0;

// functions to call for random number generation
// mt_rand produces 'better' random numbers
// but if your server doesn't support it, it's fine to use rand instead
$rand_func = "mt_rand";
$seed_func = "mt_srand";

// which type of hash to use?
// possible values: "sha1", "md5", "crc32"
// sha1 supported by PHP4.3.0+, md5 supported by PHP3+, crc32 supported by PHP4.0.1+
$hash_func = "sha1";
// store in session so can validate in form processor
$_SESSION['hash_func'] = $hash_func;

// image type:
// possible values: "jpg", "png", "gif"
// jpg doesn't support transparency (transparent bg option ends up white)
// gif may not be supported by your GD Lib.
$output = "png";

// 0=generate pseudo-random string, 1=use dictionary
$use_dict = 1;
// if your server is NOT set up to deny web access to files beginning ".ht"
// then you should ensure the dictionary file is kept outside the web directory
// can leave this blank if not using dictionary
$dict_location = "./.ht_freecap_words_rand";

// used to calculate image width, and for non-dictionary word generation
$max_word_length = 6;

// text colour
// 0=one random colour for all letters
// 1=different random colour for each letter
$col_type = 1;

// maximum times a user can refresh the image
$max_attempts = 1000;

// list of fonts to use
// the GDFontGenerator @ http://www.philiplb.de is excellent for convering ttf to GD
$font_locations = Array("./.ht_freecap_font1.gdf","./.ht_freecap_font2.gdf","./.ht_freecap_font5.gdf");

// background:
// 0=transparent (if jpg, white)
// 1=white bg with grid
// 2=white bg with squiggles
// 3=morphed image blocks
// 'random' background from v1.3 didn't provide any extra security (according to 2 independent experts)
// many thanks to http://ocr-research.org.ua and http://sam.zoy.org/pwntcha/ for testing
// for jpgs, 'transparent' is white
$bg_type = 2;
// should we blur the background? (looks nicer, makes text easier to read, takes longer)
$blur_bg = true;
// for bg_type 3, which images should we use?
// if you add your own, make sure they're fairly 'busy' images (ie a lot of shapes in them)
$bg_images = Array("./.ht_freecap_im1.jpg","./.ht_freecap_im3.jpg","./.ht_freecap_im5.jpg");
// for non-transparent backgrounds only:
	// if 0, merges CAPTCHA with bg
	// if 1, write CAPTCHA over bg
	$merge_type = 1;
	// should we morph the bg? (recommend yes, but takes a little longer to compute)
	$morph_bg = true;


//////////////////////////////////////////////////////
////// Create Images + initialise a few things
//////////////////////////////////////////////////////

// seed random number generator
// PHP 4.2.0+ doesn't need this, but lower versions will
$seed_func(make_seed());

// how faded should the bg be? (100=totally gone, 0=bright as the day)
switch($bg_type) {
	case 0:
		break;
	case 1:
	case 2:
		$bg_fade_pct = 70;
		break;
	case 3:
		$bg_fade_pct = 50;
		break;
}
$bg_fade_pct += $rand_func(-2,2);

$font_widths = array();
for ($i = 0 ; $i < sizeof($font_locations) ; $i++) {
	$handle = fopen($font_locations[$i],"r");
	$c_wid = fread($handle,12);
	$font_widths[$i] = ord($c_wid[8])+ord($c_wid[9])+ord($c_wid[10])+ord($c_wid[11]);
	fclose($handle);
}

// you shouldn't need to use words > 6 chars in length really.
$width = ($max_word_length*(array_sum($font_widths)/sizeof($font_widths))+75);
$height = 90;

$im = ImageCreate($width, $height);
$im2 = ImageCreate($width, $height);



//////////////////////////////////////////////////////
////// Avoid Brute Force Attacks:
//////////////////////////////////////////////////////
if (empty($_SESSION['freecap_attempts'])) {
	$_SESSION['freecap_attempts'] = 1;
} else {
	$_SESSION['freecap_attempts']++;
	if ($_SESSION['freecap_attempts']>$max_attempts)
	{
		$_SESSION['freecap_word_hash'] = false;

		$bg = ImageColorAllocate($im,255,255,255);
		ImageColorTransparent($im,$bg);

		$red = ImageColorAllocate($im, 255, 0, 0);
		// depending on how rude you want to be :-)
		//ImageString($im,5,0,20,"bugger off you spamming bastards!",$red);
		//ImageString($im,5,15,20,"service no longer available",$red);
		ImageString($im,5,15,20,"Max attemps reached",$red);

		sendImage($im);
	}
}


//////////////////////////////////////////////////////
////// Functions:
//////////////////////////////////////////////////////
function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

function rand_color() {
	global $bg_type,$rand_func;
	if ($bg_type==3) {
		// needs darker colour..
		return $rand_func(10,100);
	} else {
		return $rand_func(60,170);
	}
}

function myImageBlur($im) {
	// w00t. my very own blur function
	// in GD2, there's a gaussian blur function. bunch of bloody show-offs... :-)

	$width = imagesx($im);
	$height = imagesy($im);

	$temp_im = ImageCreateTrueColor($width,$height);
	$bg = ImageColorAllocate($temp_im,150,150,150);

	// preserves transparency if in orig image
	ImageColorTransparent($temp_im,$bg);

	// fill bg
	ImageFill($temp_im,0,0,$bg);
	$distance = 1;
	// use $distance=30 to have multiple copies of the word. not sure if this is useful.

	// blur by merging with itself at different x/y offsets:
	ImageCopyMerge($temp_im, $im, 0, 0, 0, $distance, $width, $height-$distance, 70);
	ImageCopyMerge($im, $temp_im, 0, 0, $distance, 0, $width-$distance, $height, 70);
	ImageCopyMerge($temp_im, $im, 0, $distance, 0, 0, $width, $height, 70);
	ImageCopyMerge($im, $temp_im, $distance, 0, 0, 0, $width, $height, 70);
	// remove temp image
	ImageDestroy($temp_im);

	return $im;
}

function sendImage($pic) {
	global $output,$im,$im2,$im3;
	header(base64_decode("WC1DYXB0Y2hhOiBmcmVlQ2FwIDEuNCAtIHd3dy5wdXJlbWFuZ28uY28udWs="));
	switch($output)
	{
		case "jpg":
			header("Content-Type: image/jpeg");
			ImageJPEG($pic);
			break;
		case "gif":
			header("Content-Type: image/gif");
			ImageGIF($pic);
			break;
		case "png":
		default:
			header("Content-Type: image/png");
			ImagePNG($pic);
			break;
	}

	// kill GD images (removes from memory)
	ImageDestroy($im);
	ImageDestroy($im2);
	ImageDestroy($pic);
	if (!empty($im3)) {
		ImageDestroy($im3);
	}
	exit();
}




//////////////////////////////////////////////////////
////// Choose Word:
//////////////////////////////////////////////////////

if ($use_dict==1) {
	// load dictionary and choose random word
	$words = @file($dict_location);
	$word = strtolower($words[$rand_func(0,sizeof($words)-1)]);
	// cut off line endings/other possible odd chars
	$word = preg_replace("#[^a-z]#","",$word);
	// might be large file so forget it now (frees memory)
	$words = "";
	unset($words);
} else {
	// based on code originally by breakzero at hotmail dot com
	// (http://uk.php.net/manual/en/function.rand.php)
	// generate pseudo-random string

	$consonants = 'bcdghklmnpqrsvwxyz';
	$vowels = 'aeuo';
	$word = "";

	$wordlen = $rand_func(5,$max_word_length);

	for ($i=0 ; $i<$wordlen ; $i++) {
		// don't allow to start with 'vowel'
		if ($rand_func(0,4)>=2 && $i!=0) {
			$word .= $vowels{$rand_func(0,strlen($vowels)-1)};
		} else {
			$word .= $consonants{$rand_func(0,strlen($consonants)-1)};
		}
	}
}

// save hash of word for comparison
$_SESSION['freecap_word_hash'] = $hash_func($word);




//////////////////////////////////////////////////////
////// Fill BGs and Allocate Colours:
//////////////////////////////////////////////////////

$tag_col = ImageColorAllocate($im,10,10,10);
$site_tag_col2 = ImageColorAllocate($im2,0,0,0);

$debug = ImageColorAllocate($im, 255, 0, 0);
$debug2 = ImageColorAllocate($im2, 255, 0, 0);

$bg = ImageColorAllocate($im, 254, 254, 254);
$bg2 = ImageColorAllocate($im2, 254, 254, 254);

ImageColorTransparent($im,$bg);
ImageColorTransparent($im2,$bg2);

// fill backgrounds
ImageFill($im,0,0,$bg);
ImageFill($im2,0,0,$bg2);

if ($bg_type!=0) {
	// generate noisy background, to be merged with CAPTCHA later
	$im3 = ImageCreateTrueColor($width,$height);
	$temp_bg = ImageCreateTrueColor($width*1.5,$height*1.5);
	$bg3 = ImageColorAllocate($im3,255,255,255);
	ImageFill($im3,0,0,$bg3);
	$temp_bg_col = ImageColorAllocate($temp_bg,255,255,255);
	ImageFill($temp_bg,0,0,$temp_bg_col);
	// we draw all noise onto temp_bg

	if($bg_type==1) {

		// draw grid on x
		for ($i=$rand_func(6,20) ; $i<$width*2 ; $i+=$rand_func(10,25)) {
			ImageSetThickness($temp_bg,$rand_func(2,6));
			$text_r = $rand_func(100,200);
			$text_g = $rand_func(100,200);
			$text_b = $rand_func(100,200);
			$text_colour3 = ImageColorAllocate($temp_bg, $text_r, $text_g, $text_b);

			ImageLine($temp_bg,$i,0,$i,$height*2,$text_colour3);
		}
		// draw grid on y
		for ($i=$rand_func(6,20) ; $i<$height*2 ; $i+=$rand_func(10,25)) {
			ImageSetThickness($temp_bg,$rand_func(2,6));
			$text_r = $rand_func(100,150);
			$text_g = $rand_func(100,150);
			$text_b = $rand_func(100,150);
			$text_colour3 = ImageColorAllocate($temp_bg, $text_r, $text_g, $text_b);

			ImageLine($temp_bg,0,$i,$width*2, $i ,$text_colour3);
		}
	} else if ($bg_type==2) {

		$bg3 = ImageColorAllocate($im3,255,255,255);
		ImageFill($im3,0,0,$bg3);
		ImageSetThickness($temp_bg,4);

		for ($i=0 ; $i<strlen($word)+1 ; $i++) {
			$text_r = $rand_func(100,150);
			$text_g = $rand_func(100,150);
			$text_b = $rand_func(100,150);
			$text_colour3 = ImageColorAllocate($temp_bg, $text_r, $text_g, $text_b);

			$points = Array();
			for ($j=1 ; $j<$rand_func(5,10) ; $j++)
			{
				$points[] = $rand_func(1*(20*($i+1)),1*(50*($i+1)));
				$points[] = $rand_func(30,$height+30);
			}

			ImagePolygon($temp_bg,$points,intval(sizeof($points)/2),$text_colour3);
		}

	} else if ($bg_type==3) {
		for ($i=0 ; $i<sizeof($bg_images) ; $i++) {
			$temp_im[$i] = ImageCreateFromJPEG($bg_images[$i]);
			$temp_width[$i] = imagesx($temp_im[$i]);
			$temp_height[$i] = imagesy($temp_im[$i]);
		}

		$blocksize = $rand_func(20,60);
		for ($i=0 ; $i<$width*2 ; $i+=$blocksize) {
			for ($j=0 ; $j<$height*2 ; $j+=$blocksize) {
				$image_index = $rand_func(0,sizeof($temp_im)-1);
				$cut_x = $rand_func(0,$temp_width[$image_index]-$blocksize);
				$cut_y = $rand_func(0,$temp_height[$image_index]-$blocksize);
				ImageCopy($temp_bg, $temp_im[$image_index], $i, $j, $cut_x, $cut_y, $blocksize, $blocksize);
			}
		}
		for ($i=0 ; $i<sizeof($temp_im) ; $i++) {
			// remove bgs from memory
			ImageDestroy($temp_im[$i]);
		}

	}

	if ($morph_bg) {
		$morph_chunk = $rand_func(1,5);
		$morph_y = 0;
		for ($x=0 ; $x<$width ; $x+=$morph_chunk)
		{
			$morph_chunk = $rand_func(1,5);
			$morph_y += $rand_func(-1,1);
			ImageCopy($im3, $temp_bg, $x, 0, $x+30, 30+$morph_y, $morph_chunk, $height*2);
		}

		ImageCopy($temp_bg, $im3, 0, 0, 0, 0, $width, $height);

		$morph_x = 0;
		for ($y=0 ; $y<=$height; $y+=$morph_chunk)
		{
			$morph_chunk = $rand_func(1,5);
			$morph_x += $rand_func(-1,1);
			ImageCopy($im3, $temp_bg, $morph_x, $y, 0, $y, $width, $morph_chunk);

		}
	} else {
		ImageCopy($im3,$temp_bg,0,0,30,30,$width,$height);
	}

	ImageDestroy($temp_bg);

	if ($blur_bg) {
		myImageBlur($im3);
	}
}



//////////////////////////////////////////////////////
////// Write Word
//////////////////////////////////////////////////////

$word_start_x = $rand_func(5,32);
$word_start_y = 15;

if ($col_type==0) {
	$text_r = rand_color();
	$text_g = rand_color();
	$text_b = rand_color();
	$text_colour2 = ImageColorAllocate($im2, $text_r, $text_g, $text_b);
}

// write each char in different font
for ($i=0 ; $i<strlen($word) ; $i++) {
	if ($col_type==1) {
		$text_r = rand_color();
		$text_g = rand_color();
		$text_b = rand_color();
		$text_colour2 = ImageColorAllocate($im2, $text_r, $text_g, $text_b);
	}
	$j = $rand_func(0,sizeof($font_locations)-1);
	$font = ImageLoadFont($font_locations[$j]);
	ImageString($im2, $font, $word_start_x+($font_widths[$j]*$i), $word_start_y, $word{$i}, $text_colour2);
}
$font_pixelwidth = $font_widths[$j];



//////////////////////////////////////////////////////
////// Morph Image:
//////////////////////////////////////////////////////


$word_pix_size = $word_start_x+(strlen($word)*$font_pixelwidth);

// firstly move each character up or down a bit:
$y_pos = 0;
for ($i=$word_start_x ; $i<$word_pix_size ; $i+=$font_pixelwidth) {
	$prev_y = $y_pos;
	do {
		$y_pos = $rand_func(-5,5);
	} while ($y_pos<$prev_y+2 && $y_pos>$prev_y-2);
	ImageCopy($im, $im2, $i, $y_pos, $i, 0, $font_pixelwidth, $height);
}

ImageFilledRectangle($im2,0,0,$width,$height,$bg2);

$y_chunk = 1;
$morph_factor = 1;
$morph_x = 0;
for ($j=0 ; $j<strlen($word) ; $j++) {
	$y_pos = 0;
	for ($i=0 ; $i<=$height; $i+=$y_chunk) {
		$orig_x = $word_start_x+($j*$font_pixelwidth);
		$morph_x += $rand_func(-$morph_factor,$morph_factor);
		ImageCopyMerge($im2, $im, $orig_x+$morph_x, $i+$y_pos, $orig_x, $i, $font_pixelwidth, $y_chunk, 100);
	}
}


ImageFilledRectangle($im,0,0,$width,$height,$bg);
// now do the same on the y-axis
$y_pos = 0;
$x_chunk = 1;
for ($i=0 ; $i<=$width ; $i+=$x_chunk) {
	$y_pos += $rand_func(-1,1);
	ImageCopy($im, $im2, $i, $y_pos, $i, 0, $x_chunk, $height);
}


myImageBlur($im);

if ($output!="jpg" && $bg_type==0) {
	ImageColorTransparent($im,$bg);
}


//////////////////////////////////////////////////////
////// Try to avoid 'free p*rn' style CAPTCHA re-use
//////////////////////////////////////////////////////



ImageFilledRectangle($im2,0,0,$width,$height,$bg2);
if (is_array($site_tags)) {
	for ($i=0 ; $i<sizeof($site_tags) ; $i++) {
		$tag_width = strlen($site_tags[$i])*6;
		if ($tag_pos==0 || $tag_pos==2) {
			ImageString($im2, 2, intval($width/2)-intval($tag_width/2), (10*$i), $site_tags[$i], $site_tag_col2);
		}
		if ($tag_pos==1 || $tag_pos==2) {
			ImageString($im2, 2, intval($width/2)-intval($tag_width/2), ($height-34+($i*10)), $site_tags[$i], $site_tag_col2);
		}
	}
}
ImageCopyMerge($im2,$im,0,0,0,0,$width,$height,80);
ImageCopy($im,$im2,0,0,0,0,$width,$height);


//////////////////////////////////////////////////////
////// Merge with obfuscated background
//////////////////////////////////////////////////////

if ($bg_type!=0) {
	// fade bg:
	if ($bg_type!=3) {
		$temp_im = ImageCreateTrueColor($width,$height);
		$white = ImageColorAllocate($temp_im,255,255,255);
		ImageFill($temp_im,0,0,$white);
		ImageCopyMerge($im3,$temp_im,0,0,0,0,$width,$height,$bg_fade_pct);
		ImageDestroy($temp_im);
		$c_fade_pct = 50;
	} else {
		$c_fade_pct = $bg_fade_pct;
	}

	// captcha over bg:
	if ($merge_type==1) {
		ImageCopyMerge($im3,$im,0,0,0,0,$width,$height,100);
		ImageCopy($im,$im3,0,0,0,0,$width,$height);
	} else {
		ImageCopyMerge($im,$im3,0,0,0,0,$width,$height,$c_fade_pct);
	}
}


//////////////////////////////////////////////////////
////// Write tags, remove variables and output!
//////////////////////////////////////////////////////

// feel free to remove/change
$tag_str = "freeCap v1.41 - puremango.co.uk";

$tag_width = strlen($tag_str)*6;
// write tag
ImageString($im, 2, $width-$tag_width, $height-13, $tag_str, $tag_col);

unset($word);
unset($use_dict);
unset($dict_location);
unset($max_word_length);
unset($bg_type);
unset($bg_images);
unset($merge_type);
unset($bg_fade_pct);
unset($morph_bg);
unset($col_type);
unset($max_attempts);
unset($font_locations);

sendImage($im);

?>
