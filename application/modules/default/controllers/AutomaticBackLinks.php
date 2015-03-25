<?php 

/**
* AutomaticBackLinks.com - PHP linking code
* Copy and paste this code in your php document where you want your links to display
* Make sure that this code is inside PHP tags
*/

error_reporting(0);

//Settings
$abCacheFolderName = "/tmp/automaticbacklinks_cache";
$abAccountCode = "6eb29cda9577b8efe7959e20acc84777";
$abCacheHours = "24";


/**
 * Do not change anything below
 * Do not change anything below
 * Do not change anything below
 * Do not change anything below
 * Do not change anything below
 */

$v = "2.4";
$s = "php";
$abMsg = array();
if (trim($_GET['ab_debug']) == '6eb29cda9577b8efe7959e20acc84777') {
    $debug = true;
    echo "<li />Version: ".$v;
    echo "<li />System: ".$s;
    unset($_GET['ab_debug']);
}

//Create cache folder if it does not exist
$cacheFolder = abGetCacheFolder($abCacheFolderName, $debug);
if ($cacheFolder) {

    //Current URL
    $page = abGetPageUrl($debug);
    if (abIsValidUrl($page, $debug)) {

        $cacheFileName = $cacheFolder."/".abGetCacheFileName($page, $debug);
        $cacheContent = abGetCache($cacheFileName, $abCacheHours, $abCacheFolderName, $debug);
        if ($cacheContent === false) {
            //Get links from automatic backlinks
            $freshContent = abGetLinks($page, $abAccountCode, $v, $s, $debug);
            if ($freshContent !== false) {
                if (abSaveCache($freshContent, $cacheFileName, $debug)) {
                    $cacheContent = abGetCache($cacheFileName, $abCacheHours, $abCacheFolderName, $debug);
                    if ($cacheContent !== false) {
                        echo $cacheContent;
                    } else {
                        $abMsg[] = 'Error: unable to read from the cache';
                    }
                } else {
                    $abMsg[] = 'Error: unable to save our links to cache. Please make sure that the folder '.$abCacheFolderName.' located in the folder '.$_SERVER['DOCUMENT_ROOT'].' and has CHMOD 0777';
                }
            } else {
                $abMsg[] = 'Error: unable to get links from server. Please make sure that your site supports either file_get_contents() or the cURL library.';
            }
        } else {
            //Display the cached content
            echo $cacheContent;
        }

    } else {
        $abMsg[] = 'Error: your site reports that it is located on the following URL: '.$page.' - This is not a valid URL and we can not display links on this page. This is probably due to an incorrect setting of the $_SERVER variable.';
    }

} else {
    $abMsg[] = 'Error: Unable to create or read from your link cache folder. Please try to create a folder by the name "'.$abCacheFolderName.'" directly in the root of your site and CHMOD the folder to 0777';
}

foreach ($abMsg as $error) {
    echo $error."<br />";
}

/**
 * Helper functions
 */

function abSaveCache($content, $file, $debug=false) {

    //Prepend a timestamp to the content
    $content = time()."|".$content;

    echo ($debug) ? "<li />Saving Cache: ".$content : "";

    $fh = fopen($file, 'w');
    if ($fh !== false) {
        if (!fwrite($fh, $content)) {
            echo ($debug) ? "<li />Error Saving Cache!" : "";
            return false;
        }
    } else {
        echo ($debug) ? "<li />Error opening cache file for writing!" : "";
        return false;
    }
    if (!fclose($fh)) {
        echo ($debug) ? "<li />Error closing file handle!" : "";
        return false;
    }

    if (!file_exists($file)) {
        echo ($debug) ? "<li />Error could not create cache file!" : "";
        return false;
    } else {
        echo ($debug) ? "<li />Cache file created successfully" : "";
        return true;
    }


}

//Deletes any cache file that is older than 24 hours
function abClearOldCache($cacheFolderName, $cacheHours, $debug=false) {

    $cacheFolder = abGetCacheFolder($cacheFolderName);

    if (is_dir($cacheFolder)) {
       if ($dh = opendir($cacheFolder)) {
           while (($file = readdir($dh)) !== false) {
               if (strpos($file, ".cache")) {
                   $modified = filemtime($cacheFolder."/".$file);
                   $timeCutOff = time()-(60*60*$cacheHours);
                   if ($modified < $timeCutOff) {
                       @unlink($cacheFolder."/".$file);
                   }
               }
           }
           closedir($dh);
       }
    }



}


//Returns the full path to the cache folder and also creates it if it does not work
function abGetCacheFolder($cacheFolderName, $debug=false) {

    if (isset($_SERVER['DOCUMENT_ROOT'])) {
		$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'],"/"); //Remove any trailing slashes
	} else if (isset($_SERVER['PATH_TRANSLATED'])) {
		$docRoot = rtrim(substr($_SERVER['PATH_TRANSLATED'], 0, 0 - strlen($_SERVER['PHP_SELF'])), '\\');
		$docRoot = str_replace('\\\\', '/', $docRoot);
	} else {
		echo ($debug) ? "<li />Error: Could not construct cache path" : "";
	}
    $cacheFolder = $docRoot."/".$cacheFolderName;

    echo ($debug) ? "<li />Cache folder is: ".$cacheFolder : "";

    if (!file_exists($cacheFolder)) {
        echo ($debug) ? "<li />Cache folder does not exist: ".$cacheFolder : "";
        if (!@mkdir($cacheFolder,0777)) {
            echo ($debug) ? "<li />Error - could not create cache folder: ".$cacheFolder : "";
            return false;
        } else {
            echo ($debug) ? "<li />Successfully created cache folder" : "";
            //Also make an empty default html file
            $blankFile = $cacheFolder."/index.html";
            if (!file_exists($blankFile)) {
                $newFile = @fopen($blankFile,"w");
                @fclose($newFile);
            }
        }
    }

    return $cacheFolder;

}

//Url validation
function abIsValidUrl($url, $debug=false) {

    $urlBits = @parse_url($url);
    if ($urlBits['scheme'] != "http" && $urlBits['scheme'] != "https") {
        echo ($debug) ? "<li />Error! URL does not start with http: ".$url : "";
        return false;
    } else if (strlen($urlBits['host']) < 4 || strpos($urlBits['host'], ".") === false) {
        echo ($debug) ? "<li />Error! URL is incorrect: ".$url : "";
        return false;
    }

    return true;
}


//Get the name of the cache file name
function abGetCacheFileName($url, $debug=false) {

    $cacheFileName = md5($url).".cache";
    echo ($debug) ? "<li />Cache file name for URL: ".$url." is ".$cacheFileName : "";
    return $cacheFileName;

}

//Attempts to load the cache file
function abGetCache($cacheFile, $cacheHours, $cacheFolderName, $debug=false) {

    //If the url is called with ab_cc=1 then discard the cache file
    if (isset($_GET['ab_cc']) && $_GET['ab_cc'] == "1") {
        echo ($debug) ? "<li />Clear cache invoked!" : "";
        abRemoveCacheFile($cacheFile);
        unset($_GET['ab_cc']);
        return false;
    }

    if (!file_exists($cacheFile)) {
        echo ($debug) ? "<li />Error! Cache file does not exist! ".$cacheFile : "";
        return false;
    }

    $cache_contents = @file_get_contents($cacheFile);

    if ($cache_contents === false) {
        echo ($debug) ? "<li />Error: Cache file is completely empty!" : "";
        return false;
    } else {
        echo ($debug) ? "<li />Cache file contents".$cache_contents : "";

        //Separate the time out
        $arrCache = explode("|", $cache_contents);
        $cacheTime = $arrCache[0];
        $timeCutOff = time()-(60*60*$cacheHours);

        //Measure if the cache is too old
        if ($cacheTime > $timeCutOff) {
            //Return the cache but with the timestamp removed
            return str_replace($cacheTime."|", "", $cache_contents);
        } else {
            //echo "cacheTime ($cacheTime) <= timeCutOff ($timeCutOff)";
            abRemoveCacheFile($cacheFile, $debug);
            abClearOldCache($cacheFolderName, $cacheHours, $debug); //Also remove other old cache files
            return false;
        }
    }

}


//Delete a cache file
function abRemoveCacheFile($cacheFile, $debug=false) {
    if (!@unlink($cacheFile)) {
        echo ($debug) ? "<li />Error: Could not remove cache file: ".$cacheFile : "";
        return false;
    } else {
        echo ($debug) ? "<li />Successfully removed the cache file: ".$cacheFile : "";
        return true;
    }
}



//Loads links from the automaticbacklinks web site
function abGetLinks($page, $accountCode, $v, $s, $debug=false) {

    //Make the URL
    $url = "http://links.automaticbacklinks.com/links.php";
    $url = $url."?a=".$accountCode;
    $url = $url."&v=".$v;
    $url = $url."&s=".$s;
    $url = $url."&page=".urlencode($page);

    echo ($debug) ? "<li />Making call to AB: ".$url : "";

    ini_set('default_socket_timeout', 10);
    if (intval(get_cfg_var('allow_url_fopen')) && function_exists('file_get_contents') && 1 == 2) {
        echo ($debug) ? "<li />Using file_get_contents()" : "";
        $links = @file_get_contents($url);
    } else if (intval(get_cfg_var('allow_url_fopen')) && function_exists('file') && 1 == 2) {
        echo ($debug) ? "<li />Using file()" : "";
        if ($content = @file($url)) {
            $links = @join('', $content);
        }
    } else if (function_exists('curl_init')) {
        echo ($debug) ? "<li />Using cURL()" : "";
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $links = curl_exec($ch);
        curl_close ($ch);
    } else {
        echo ($debug) ? "<li />Error: no method available to fetch links!" : "";
        return false;
    }

    return $links;

}


//remove ab_cc etc. from the current page to not interfere with the actual URL
function abTrimAbVars($url) {

    $url = str_replace("?ab_cc=1", "", $url);
    $url = str_replace("&ab_cc=1", "", $url);
    $url = str_replace("?ab_debug=6eb29cda9577b8efe7959e20acc84777", "", $url);
    $url = str_replace("&ab_debug=6eb29cda9577b8efe7959e20acc84777", "", $url);
    $url = str_replace("&phpinfo=1", "", $url);

    return $url;

}

//Get page
function abGetPageUrl($debug=false) {

    $query = "";
    $protocol = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != "off") ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];

    if ($_SERVER["REDIRECT_URL"]) {
        //Redirect
        if (isset($_SERVER['REDIRECT_SCRIPT_URI'])) {
            //Use URI - it is complete
            $page = $_SERVER['REDIRECT_SCRIPT_URI'];
        } else {
            //Use file and query
            $file = $_SERVER["REDIRECT_URL"];
            if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
                $query = "?".$_SERVER['REDIRECT_QUERY_STRING'];
            }
        }
    } else {
        //No redirect
        if (isset($_SERVER['REQUEST_URI'])) {
            //Use URI
            if (substr($_SERVER['REQUEST_URI'],0,4) == "http") {
                //Request URI has host in it
                $page = $_SERVER['REQUEST_URI'];
            } else {
                //Request uri lacks host
                $page = $protocol.$host.$_SERVER['REQUEST_URI'];
            }
        } else if (isset($_SERVER['SCRIPT_URI'])) {
            //Use URI - it is complete
            $page = $_SERVER['SCRIPT_URI'];
        } else {
            $file = $_SERVER['SCRIPT_NAME'];
            if (isset($_SERVER['QUERY_STRING'])) {
                $query = "?".$_SERVER['QUERY_STRING'];
            }
        }
    }
    if (!$page) {
        $page = $protocol.$host.$file.$query;
    }

    $page = abTrimAbVars($page);

    echo ($debug) ? "<li />This page is reported as: ".$page : "";

    return $page;

}


//Show phpinfo if debug is on and phpinfo is requested
if ($debug && $_GET['phpinfo']) {

    ?>
	<style type="text/css">
		#phpinfo_div {
			position:fixed;
			bottom:0;
			left:0;
			height:300px;
			width:100%;
			overflow:scroll;
			background:#F0F0F0;
			color:#000;
			border-top:2px solid;
			z-index:999;
		}
	</style>
    <div id="phpinfo_div">
        <?php echo phpinfo(); ?>
    </div>
    <?php

}


