<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<style>
      tr:nth-of-type(odd) {
      background-color:#e8e8e8;
    }
</style>

</head>
<body>

<?

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

// Change the specific code in this file to match your feed...
// Need to include the code to read ID3 tags
// also reads lots of different formats
// we'll implement mp3, mpa (quicktime), asf (wma) and riff (wav/avi)
// alter the path to your getid3 directory location
require_once('./lib_getid3/getid3/getid3.php');
// Set these to sensible values for your site
// Maximum number of mp3s to offer on the page
$maxFeed = 5000;
//Title of page	Used by iPodder for download subdirectory name
$titleTAG="";
//iPodder 1.0 seems to ignore everything below
//URL of site feed applies too
$linkTAG="";
//Description
$descriptionTAG="";
//Copyright for feed
$copyrightTAG="";
//Your email address
$webMasterTAG="";
// set this string to include all of the allowable file types
// supported file types
// leading "(" is REQUIRED!
$sftypes = "(.mp3 .m4a .asf .wma .wav .avi .mov .m4b)";
  // Define a list of excluded files or patterns
 $excludedFiles = array('.htaccess', 'error.log', 'error_log');
?>
<hr>
<p><a name="List"></a>
    <? 
///////////////
/////////////
// Main Code

///////////
$rootMP3URL = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$rootMP3URL =  substr($rootMP3URL, 0, strrpos ($rootMP3URL, "/")); // Trim off script name itself
echo "		<h1>$titleTAG</h1>\n";
//echo "		<h2>from: <a href='$linkTAG'>$linkTAG</a></h2>\n";
//echo "		<p><font size='4'>$descriptionTAG</font></p>\n";
echo "      <table width=100% align='left' cellpadding='2'>\n<tr><td colspan='7'>$titleTAG</td></tr>\n";
echo "      <tr><td>Series</td><td>Filename</td><td>Title</td><td>Speaker</td><td>Created</td><td>Filesize</td><td>Filetype</td></tr>";
$dirArray = getDir(".", $sftypes);	// Get a list of the current directory
foreach ($dirArray as $filename => $filedate) 
{
    if ($maxFeed <= 0) {
        break;
    }
	// Check if the current file is in the excluded list
	if (in_array(basename($filename), $excludedFiles)) {
		continue; // Skip this file
	}

    $mp3file = new CMP3File;
    $mp3file->getid3($filename);
	if($filename != "")
	{
		echo "<tr>";
		// album tag
		echo "<td><b>$mp3file->album</b></td>\n";
		// title tag
		$encodedFilename = htmlentities(str_replace(' ', '%20', $filename));
		echo "<td><b><a href=\"$rootMP3URL/$encodedFilename\">" . urldecode($encodedFilename) . "</a></b></td>\n";
		if($mp3file->title != null)
		{
		echo "<td><a href=\"$rootMP3URL/$encodedFilename\">" . str_replace('_', ' ', $mp3file->title) . "</a></td>\n";
		}
		else
		{
			echo "<td><a href=\"$rootMP3URL/$encodedFilename\">File</a></td>\n";
		}
		// composer tag
		
		echo "<td><b>$mp3file->artist</b></td>\n";
		echo "<td><i>" . date("r", $filedate) . "</i></td>\n";
		echo "<td><i>" . filesize($filename) . " (bytes)</i></td>\n";
		echo "<p><td>{$mp3file->mime_type}</td>\n";
		print "</tr>\n";
	}
    $maxFeed--;
}
 
echo  "</table>\n";
/*
$dirArray = getDir(".", $sftypes);	// Get a list of the current directory
foreach ($dirArray as $filename => $filedate) {
    if ($maxFeed <= 0) {
        break;
    }
    echo "<p>&nbsp;</p>\n";
    $maxFeed--;
}
*/
 
 echo "<p>&nbsp;</p>\n";
 echo "<p>&nbsp;</p>\n";

// Functions and Classes
function stripJunk ($text) {
// Strip non-text characters
	for ($c=0; $c<strlen($text); $c++) 
	{
		if (ord($text[$c]) >= 32 AND ord($text[$c]) <= 122)
			$outText.=$text[$c];
	}
	return $outText;
}
// read a text frame from the ID3 tag. V2.2 and 2.3 are supported
function readFrame ($frameTag, $header, $ver) {
	if ($ver == 3) {
		$lengthOffset = 6;
		$textOffset = 11;
	} 
	if ($ver == 2) {
		$lengthOffset = 4;
		$textOffset = 7;
	}
	
	// find the tag we're looking for
	$tagStart = strpos ($header, $frameTag) ;
	// this code only reads the first 256 bytes on larger frames
	$tagLength = ord (substr($header, $tagStart + 1 + $lengthOffset, 1));
	$textStart = $tagStart + $textOffset;
	if ($frameTag == "COMM" || $frameTag == "COM") {
		$textStart = $textStart + 4;
		$tagLength = $tagLength - 4;
		}
	$tagText = substr($header, $textStart, $tagLength - 1);
	return stripJunk($tagText);
}
class CMP3File 
{
    //properties
    var $title;
    var $artist;
    var $album;
    var $year;
    var $comment;
    var $genre;
	var $composer;
	var $copyright;
	var $mime_type;
	function getid3 ($file) {
	// Initialize getID3 engine
		if (file_exists($file))
		{ //after verifying the file exists,
			$getID3 = new getID3;
			// Analyze file and store returned data in $ThisFileInfo
			$ThisFileInfo = $getID3->analyze($file);
			// Optional: copies data from all subarrays of [tags] into [comments] so
			// metadata is all available in one location for all tag formats
			// metainformation is always available under [tags] even if this is not called
			getid3_lib::CopyTagsToComments($ThisFileInfo);
			// Output desired information in whatever format you want
			// Note: all entries in [comments] or [tags] are arrays of strings
			// See structure.txt for information on what information is available where
			// or check out the output of /demos/demo.browse.php for a particular file
			// to see the full detail of what information is returned where in the array
			//echo @$ThisFileInfo['comments']['artist'][0]; // artist from any/all available tag formats
		$mim = @$ThisFileInfo['mime_type']; // artist from any/all available tag formats
		switch (strrchr(strtolower($file), "."))
			{
			case ".mp3";
				$tit = @$ThisFileInfo['id3v2']['comments']['title'][0]; // artist from any/all available tag formats
				$alb = @$ThisFileInfo['id3v2']['comments']['album'][0]; // artist from any/all available tag formats
				$art = @$ThisFileInfo['id3v2']['comments']['artist'][0]; // artist from any/all available tag formats
				$com = @$ThisFileInfo['id3v2']['comments']['comment'][3]; // artist from any/all available tag formats
				$cmp = @$ThisFileInfo['id3v2']['comments']['composer'][0]; // artist from any/all available tag formats
				$gen = @$ThisFileInfo['id3v2']['comments']['genre'][0]; // artist from any/all available tag formats
				break;
			case ".m4a";
				$tit = @$ThisFileInfo['quicktime']['comments']['title'][0]; // artist from any/all available tag formats
				$alb = @$ThisFileInfo['quicktime']['comments']['album'][0]; // artist from any/all available tag formats
				$art = @$ThisFileInfo['quicktime']['comments']['artist'][0]; // artist from any/all available tag formats
				$com = @$ThisFileInfo['quicktime']['comments']['comment'][0]; // artist from any/all available tag formats
				$cmp = @$ThisFileInfo['quicktime']['comments']['writer'][0]; // artist from any/all available tag formats
				//	$gen = @$ThisFileInfo['quicktime']['comments']['genre'][0]; // artist from any/all available tag formats
				break;
			case ".m4b";
				$tit = @$ThisFileInfo['quicktime']['comments']['title'][0]; // artist from any/all available tag formats
				$alb = @$ThisFileInfo['quicktime']['comments']['album'][0]; // artist from any/all available tag formats
				$art = @$ThisFileInfo['quicktime']['comments']['artist'][0]; // artist from any/all available tag formats
				$com = @$ThisFileInfo['quicktime']['comments']['comment'][0]; // artist from any/all available tag formats
				$cmp = @$ThisFileInfo['quicktime']['comments']['writer'][0]; // artist from any/all available tag formats
				//	$gen = @$ThisFileInfo['quicktime']['comments']['genre'][0]; // artist from any/all available tag formats
				break;
			case ".mov";
				$tit = @$ThisFileInfo['quicktime']['comments']['title'][0]; // artist from any/all available tag formats
				$alb = @$ThisFileInfo['quicktime']['comments']['album'][0]; // artist from any/all available tag formats
				$art = @$ThisFileInfo['quicktime']['comments']['artist'][0]; // artist from any/all available tag formats
				$com = @$ThisFileInfo['quicktime']['comments']['comment'][0]; // artist from any/all available tag formats
				$cmp = @$ThisFileInfo['quicktime']['comments']['director'][0]; // artist from any/all available tag formats
				//	$gen = @$ThisFileInfo['quicktime']['comments']['genre'][0]; // artist from any/all available tag formats
				break;
			case ".asf";
				$tit = @$ThisFileInfo['asf']['comments']['title'][0]; // artist from any/all available tag formats
				$alb = @$ThisFileInfo['asf']['comments']['album'][0]; // artist from any/all available tag formats
				$art = @$ThisFileInfo['asf']['comments']['artist'][0]; // artist from any/all available tag formats
				$com = @$ThisFileInfo['asf']['comments']['comment'][0]; // artist from any/all available tag formats
				$cmp = @$ThisFileInfo['asf']['comments']['composer'][0]; // artist from any/all available tag formats
				$gen = @$ThisFileInfo['asf']['comments']['genre'][0]; // artist from any/all available tag formats
				break;
			case ".wma";
				$tit = @$ThisFileInfo['asf']['comments']['title'][0]; // artist from any/all available tag formats
				$alb = @$ThisFileInfo['asf']['comments']['album'][0]; // artist from any/all available tag formats
				$art = @$ThisFileInfo['asf']['comments']['artist'][0]; // artist from any/all available tag formats
				$com = @$ThisFileInfo['asf']['comments']['comment'][0]; // artist from any/all available tag formats
				$cmp = @$ThisFileInfo['asf']['comments']['composer'][0]; // artist from any/all available tag formats
				$gen = @$ThisFileInfo['asf']['comments']['genre'][0]; // artist from any/all available tag formats
				break;
			default;
				$tit = $file; // artist from any/all available tag formats
			}
			if(isset($tit)) { $this->title = $tit; };
			if(isset($cmp)) {$this->composer = $cmp;};
			if(isset($alb)) {$this->album = $alb;};
			if(isset($com)) {$this->comment = $com;};
			if(isset($cmp)) {$this->copyright = $cmp;};
			if(isset($art)) {$this->artist = $art;};
			if(isset($mim)) {$this->mime_type = $mim;};
		return true;
		} else {
		return false; // file doesn't exist
		}
	}
}
function getDir(string $mp3Dir, string $supported_file_types): array
{
    // Returns directory as array[file]=date in newest to oldest order
    $dirArray = [];
    $diskdir = "./{$mp3Dir}/";
    if (is_dir($diskdir)) {
        $dh = opendir($diskdir);
        while (($file = readdir($dh)) !== false) {
            if (is_file($diskdir . $file) && $file[0] != '.') {
                $fext = strtolower(strrchr($file, '.'));
                if (strpos($supported_file_types, $fext) !== false) {
                    $ftime = filemtime("{$mp3Dir}/{$file}");
                    $dirArray[$file] = $ftime;
                }
            }
        }
        closedir($dh);
    }
    asort($dirArray);
    $dirArray = array_reverse($dirArray);
    return $dirArray;
}
 
?>
<hr>
 <h1><a name="subscribe"></a>Subscribe to Our Podcasts</h1>
 <p><a href="dircaster.php"><img src="podcast_logo_grey.png" width="96" height="44" border="0" align="absmiddle"></a>
     <? $rootMP3URL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$rootMP3URL =  substr($rootMP3URL, 0, strrpos ($rootMP3URL, "/"));
$feedurl = $rootMP3URL."/dircaster.php";
echo "Copy this for iPodder --> $feedurl"; 
# echo ("<a href=\"".$feedurl."\">".$feedurl."</a>"); 
?>
 </p>
 <p><em>(if you are using <a href="http://www.dopplerradio.net"><img src="doppler.jpg" alt="doppler" width="66" height="19" border="0" align="absbottom"></a> drag the &quot;podcast&quot; logo onto the open Doppler window. If you are using <a href="http://juicereceiver.sourceforge.net/index.php"><img src="juice_receiver.gif" width="80" height="21" border="0"></a> use the link URL above to copy and paste the URL to add the feed manually. )</em></p>
<!-- <p><a href="../subscribe.html" target="_blank">Click here for detailed doppler and JuiceReceiver setup instructions if you are not already using an RSS aggregator</a></p> --->
 <p><a href="#List">Back to the podcast list</a> </p>
 <hr>
 <h1><a name="about"></a>About Podcasting</h1>
 <p><strong>About podcasting<br>
 </strong>Podcasting became popular back in 2004 as a method of publishing sound files to the Internet, allowing users to subscribe to a feed and receive new audio files automatically. Podcasting is distinct from other types of audio content delivery because of its subscription model, which uses the RSS 2.0 file format. This technique has enabled independent producers to create self-published, syndicated &quot;radio&quot; shows, and has given broadcast radio programs a new distribution channel.</p>
 <p><strong>Differences from traditional broadcasting</strong><br>
  Unlike radio programs, which are generally listened to as they are broadcast, podcasts are transferred to the listener as a digital media file and are consumed at the listener's convenience, similar to a VCR playing back a pre-recorded TV show.</p>
 <p>From the producer's perspective, podcasts cannot have live participation or immediately reach large audiences as quickly as radio can. However, podcasting allows individuals to easily transmit content worldwide without the need for expensive equipment or licenses, and is frequently used together with an online interactive bulletin board or blog.</p>
 <p><a href="#List">Back to the podcast list</a> </p>
 <p>&nbsp;</p>
</body>
</html>