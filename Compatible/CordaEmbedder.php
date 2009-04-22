<?php

function indexOf($haystack, $needle)
{
    $pos = strpos($haystack, $needle);
    $strFound = true;
    if($pos === false) // note: three equal signs
    {
        // not found...
        $strFound = false;
    }

    if($strFound == false)
    {
        return -1;
    }
    return $pos;
}

// CordaEmbedder Constants
define("DEFAULT_HEIGHT", "330");
define("DEFAULT_WIDTH", "540");

define("SVG", "SVG");
define("FLASH", "FLASH");
define("GIF", "GIF");
define("PNG", "PNG");
define("PDF", "PDF");
define("EPS", "EPS");
define("WBMP", "WBMP");
define("URL", "URL");
define("TIFF", "TIFF");
define("JPEG", "JPEG");

define("STRICT", "STRICT");
define("LOOSE", "LOOSE");
define("NONE", "NONE");

define("EN", "EN");

class CordaEmbedder
{
    var $encodeURLAB    = array();    // 1-dimensional array
    var $encodeURLBAA    = array();    // 2-dimensional array
    var $encodeURLdone    = false;    // boolean

    var $CordaEmbedderVersion    = "5.1.2";    // string
    var $EmbedderType    = "PHP";    // string
    var $attributeErrors    = null;        // string

    var $debugOn        = false;    // boolean

    var $externalServerAddress = null;    // string
    var $resultSetPCScript    = null;        // string
    var $pcScript        = null;        // string
    var $loadDataPCScript    = null;        // string
    var $internalCommPortAddress = null;    // string
    var $isPostRequest    = false;    // boolean
    var $clusterMonitorAddress = null;    // string
    var $requestPath    = "";        // string

    var $error        = false;    // boolean
    var $errorString    = null;        // string

    var $commportHost    = null;        // string
    var $useLogData        = false;    // boolean return graphical log
    var $password        = null;        // string for graphical log, save, & flush
    var $appearanceFile    = null;        // string
    var $cmdVector        = array();    // array
    var $objectParamTag    = null;        // string
    var $retrieveImageSS    = false;    // boolean
    var $serverSideImageName = null;    // string
    var $imageType        = null;        // string
    var $useCache        = true;        // boolean cacheImage
    var $width        = 0;        // int
    var $height        = 0;        // int
    var $htmlWidth        = "0";        // string
    var $htmlHeight        = "0";        // string
    var $extraHTMLAttributes = " ";        // string
    var $bgColor        = null;        // string
    var $svgTemplate    = null;        // string
    var $extraCTSCommands    = null;        // string
    var $genericData    = null;        // string
    var $userAgent        = null;        // string
    var $fallback        = null;        // string
    var $returnDescriptiveLink = false;    // boolean
    var $language        = null;        // string dLink Language
    var $tableObject    = null;        // string
    var $appendServerInfoSlash = true;    // boolean
    var $autoSwitchToPNG    = true;        // boolean
    var $makeFullRequest    = false;    // boolean
    var $maxRequestLength    = 0;        // int

    //_________________________________________________________________________
    //
    // CordaEmbedder constructor
    //_________________________________________________________________________
    function CordaEmbedder()
    {
        // if we haven't filled in these encoding arrays yet, do it now.
        if (!$this->encodeURLdone)
            $this->buildURLEncodingArray();

        // debug encoding array data
        /*
        echo "<pre>";
        $i = 0;
        foreach($this->encodeURLBAA as $key => $value)
        {
            $i++;
            echo "$i\t$key => ";
            for($j=0; $j < 3; $j++)
            {
                echo chr($value[$j]);
            }
            echo "\n";
        }
        echo "</pre>";
        */
        // end debug code
    }

    //_________________________________________________________________________
    //
    // buildURLEncodingArray
    //_________________________________________________________________________
    function buildURLEncodingArray()
    {
        $this->encodeURLdone = true;

        // 1-dimensional array
        $this->encodeURLAB = array();

        // 2-dimensional array
        $this->encodeURLBAA = array();

        $badCharacters = " #%<>?[]^`{|}~";

        $len = strlen($badCharacters);
        for ($i = 0; $i < $len; $i++)
        {
            $c = ord($badCharacters[$i]);
            $hexChars = dechex("$c");

            $this->encodeURLAB[$c] = true;
            $this->encodeURLBAA[$c][0] = ord('%'); // (byte)'%';
            $this->encodeURLBAA[$c][1] = ord($hexChars[0]); //(byte)hexChars.charAt(0);
            $this->encodeURLBAA[$c][2] = ord($hexChars[1]); //(byte)hexChars.charAt(1);
        }

        // encode linefeeds && newlines as spaces
        $badCharacters = "\r\n";
        $hexChars = dechex(ord(' ')); //Integer.toHexString(' ');
        $len = strlen($badCharacters);
        for ($i = 0; $i < $len; $i++)
        {
            $c = ord($badCharacters[$i]);
            $this->encodeURLAB[$c] = true;
            $this->encodeURLBAA[$c][0] = ord('%'); // (byte)'%';
            $this->encodeURLBAA[$c][1] = ord($hexChars[0]); //(byte)hexChars.charAt(0);
            $this->encodeURLBAA[$c][2] = ord($hexChars[1]); //(byte)hexChars.charAt(1);
        }
    }

    //_________________________________________________________________________
    //
    // getEmbeddingHTML()
    // returns string
    //_________________________________________________________________________

    function getEmbeddingHTML()
    {
        if($this->debugOn)
        {
            return $this->getCommportResponse("@_RTNHTML@_EMBVER".$this->CordaEmbedderVersion."@_EMBTYPE".$this->EmbedderType) . $this->attributeErrors;
        }
        return $this->getCommportResponse("@_RTNHTML@_EMBVER".$this->CordaEmbedderVersion."@_EMBTYPE".$this->EmbedderType);
    }

    //_________________________________________________________________________
    //
    // getExternalServerAddress
    //_________________________________________________________________________

    function getExternalServerAddress($sa)
    {
        $host = "";
        $port = 80;

        $i = indexOf($sa, "://");
        if($i >= 0)
            $sa = substr($sa, $i+3);

        // remove query string
        $i = indexOf($sa, '?');
        if($i >= 0)
        {
            $sa = substr($sa, 0, $i);
        }

        // remove path
        $i = indexOf($sa, '/');
        if($i >= 0)
        {
            $sa = substr($sa, 0, $i);
        }

        // peal off the port number
        $i = indexOf($sa, ':');
        if ($i >= 0)
        {
            $host = substr($sa, 0, $i);
            $port = substr($sa, $i+1);
        }
        else
        {
            $host = $sa;
        }
        return array('host' => $host, 'port' => (int)$port);
    }

    //_________________________________________________________________________
    //
    // setCommportAddress
    //_________________________________________________________________________

    function setCommportAddress($cpa)
    {
        $port = 80;

        $i = indexOf($cpa, "://");
        if($i >= 0)
            $cpa = substr($cpa, $i+3);
        // peal off the port number
        $i = indexOf($cpa, ':');
        if ($i >= 0)
        {
            $this->commportHost = substr($cpa, 0, $i);
            $this->commportPort = (int)substr($cpa, $i+1);
        }
        else
        {
            $this->commportHost = $cpa;
            $this->commportPort = $port;
        }

        $i = indexOf($cpa, "/");
        if ($i >= 0)
        {
            $this->requestPath = substr($cpa, $i);
        }
        
        $this->error = false;
    }

    //_________________________________________________________________________
    //
    // validateHeightWidth
    //
    // if height or width <= 0 set to them default width
    //_________________________________________________________________________

    function validateHeightWidth()
    {
        if($this->height <= 0)
            $this->height = DEFAULT_HEIGHT;

        if($this->width <= 0)
            $this->width = DEFAULT_WIDTH;
    }

    //_________________________________________________________________________
    //
    // validateReturnDescriptiveLink()
    //
    // set language if it's null and returnDescriptiveLink is true.
    //_________________________________________________________________________

    function validateReturnDescriptiveLink()
    {
        if($this->returnDescriptiveLink)
        {
            if(empty($this->language))
            {
                $this->language = "EN";
            }
        }
    }

    //_________________________________________________________________________
    //
    // encodeString(String str)
    //
    //    return the encoded string (for server)
    //_________________________________________________________________________

    function encodeString($str)
    {
        $encodedStr = "";
        $encodeIndex = 0;

        $encodeBuffer = array();

        if(strlen($str) > 0)
        {
            $len = strlen($str);
            $c = 0;
            $wrote = false;

            for($i=0; $i < $len; $i++)
            {
                $c = ord($str[$i]);
                $wrote = false;
                // urlencode bad chars below 0x07F inclusive
                if($c <= 0x07F)
                {
                    // encode the bad character
                    if(isset($this->encodeURLAB[$c]))
                    {
                        $encodeBuffer[$encodeIndex++] = chr($this->encodeURLBAA[$c][0]);
                        $encodeBuffer[$encodeIndex++] = chr($this->encodeURLBAA[$c][1]);
                        $encodeBuffer[$encodeIndex++] = chr($this->encodeURLBAA[$c][2]);
                    }
                    // write the character w/o encoding it
                    else
                    {
                        $encodeBuffer[$encodeIndex++] = $str[$i];
                    }
                }
                // %uxxxx encode chars with ord val greater than 0x07F
                else
                {
                    $encodeBuffer[$encodeIndex++] = '%';
                    $encodeBuffer[$encodeIndex++] = 'u';
                    if($c <= 0x0FF)
                    {
                        $encodeBuffer[$encodeIndex++] = '0';
                    }
                    if($c <= 0x0FFF)
                    {
                        $encodeBuffer[$encodeIndex++] = '0';
                    }

                    $hexChars = dechex("$c");
                    for($hidx=0; $hidx < strlen($hexChars); $hidx++)
                    {
                        $encodeBuffer[$encodeIndex++] = $hexChars[$hidx];
                    }
                }
            }
        }

        // append last part to string
        if($encodeIndex > 0)
        {
            $encodedStr = implode("", $encodeBuffer);
        }

        // delete the temp buffer
        $encodeBuffer = null;

        return $encodedStr;
    }

    //_________________________________________________________________________
    //
    // setServerInfoFromClusterMonitor()
    //
    // returns a workingServerAddress (String)
    //_________________________________________________________________________

    function setServerInfoFromClusterMonitor()
    {
        $workingServerAddress = $this->externalServerAddress;

        $request = $this->clusterMonitorAddress;
        if($this->lastUsedCommPort != null)
            $request = $this->clusterMonitorAddress . "?noresponse^^" . $this->lastUsedCommPort;

        // create a URL to contact the Cluster Monitor
        $serverInfo = $this->getExternalServerAddress($request);
        $query =    "GET $request HTTP/1.0\r\n" .
                "Host: ".$serverInfo['host'].":".$serverInfo['port']."\r\n" .
                "\r\n";

        // open server connection
        $fp = fsockopen($serverInfo['host'], $serverInfo['port']);

        if(!$fp)
        {
            echo "<b>Error Contacting Corda Cluster Monitor - Connection Failed</b>";
        }
        else
        {
            fputs($fp, $query);

            // discard HTTP header
            while(trim(fgets($fp, 1024)) != "");

            // assign the rest of the file to response
            while(!feof ($fp))
            {
                $buffer = fgets($fp, 1024);
                $response .= $buffer;
            }

            // close server connection
            fclose($fp);

            $address = $response;

            $cpIndex = indexOf($address, ',');
            $this->lastUsedCommPort = substr($address, $cpIndex + 1);
            $this->setCommportAddress($this->lastUsedCommPort);
            if($this->externalServerAddress == null)
                $workingServerAddress = "http://" . substr($address, 0, $cpIndex);
//            echo "Results from clusterMonitor : " . $address . "<br />";
        }

        return $workingServerAddress;
    }

    //_________________________________________________________________________
    //
    // validateExtraHTMLAttributes
    //_________________________________________________________________________

    function validateExtraHTMLAttributes()
    {
        if($this->extraHTMLAttributes != null)
        {
            if($this->extraHTMLAttributes[0] != ' ')
                $this->extraHTMLAttributes = " " . $this->extraHTMLAttributes;

            if($this->extraHTMLAttributes[strlen($this->extraHTMLAttributes)-1] != ' ')
                $this->extraHTMLAttributes .= " ";
        }
    }

    //_________________________________________________________________________
    //
    // validateUseLogData()
    //
    // Make sure password property is not null
    //_________________________________________________________________________
    function validateUseLogData()
    {
        if($this->useLogData)
        {
            if($this->password == null)
            {
                $this->addAttributeError("Graphical Log requests require a password to be set.");
            }
        }
    }

    //_________________________________________________________________________
    //
    // getCommportResponse()
    // returns string
    // takes a string
    //_________________________________________________________________________

    function getCommportResponse($type)
    {
        $response = "";    // string
        $notDone = true;    // boolean
        $workingServerAddress = $this->externalServerAddress; // string

        if($this->resultSetPCScript != null)
        {
            if($this->pcScript != null)
                $this->pcScript = $this->resultSetPCScript . $this->pcScript;
            else
                $this->pcScript = $this->resultSetPCScript;
        }

        // add objectName.loadfile(filename,append,name/number) command to pcscript
        if($this->loadDataPCScript != null)
        {
            if($this->pcScript != null)
                $this->pcScript = $this->loadDataPCScript . $this->pcScript;
            else
                $this->pcScript = $this->loadDataPCScript;
        }

        if($this->internalCommPortAddress != null)
        {
            $this->setCommportAddress($this->internalCommPortAddress);
        }

        while($notDone)
        {
            if($this->clusterMonitorAddress != null)
                $workingServerAddress = $this->setServerInfoFromClusterMonitor();
            else
                $notDone = false;

            if($this->error)
                return $this->errorString;
            if($workingServerAddress == null)
                return  "<b>ERROR: No server address specified</b>";
            if($this->commportHost == null)
                return  "<b>ERROR: No commport address specified</b>";

            $request = null; // string
            $request = $type . "@_SVRADDRESS".$workingServerAddress;

            // add the log graph if necessary
            if($this->useLogData)
            {
                $this->validateUseLogData();
                if($this->password != null)
                {
                    // graphical log requires an appearance file
                    // log data pcscript commands must be supplied also
                    $request .= "@_LOGgraph@_PW" . $this->password;
                    if($this->appearanceFile == null && ($this->cmdVector == null || count($this->cmdVector) > 0))
                    {
                        // if no apfile or data provided, use a default
                        // timeline chart & display default log data
                        $this->addPCXML("<?xml version='1.0' encoding='ISO-8859-1' ?> <Chart Version='4.0'> <Textbox Name='textbox' Top='20' Left='0' Width='540' Height='22' Anchor='TopCenter'> <Text>Add Text Here</Text> </Textbox> <Graph Name='timelinegraph' Top='62' Left='20' Width='500' Height='248' Type='TimeLine'> <Properties Effect='2D' NoGap='No'/> <DataLabels ValueString='%_YVALUE' /> <DateInputFormat>%Y/%m/%d:%H</DateInputFormat> <ValueScale Position='Bottom' MinorFont='Size:9;'/> </Graph> </Chart>");
                        if($this->pcScript == null)
                        {
                            // if no pcscript, provide something
                            $this->pcScript = "textbox.settext(So far there have been PCIS.totalHits hits) timelinegraph.series(hits, PCIS.hitsByDaySeries)";
                        }
                    }
                }
            }

            if($this->retrieveImageSS)
            {
                $request .= "@_LOAD" . $this->serverSideImageName;

                // clear serverSideImageName & retrieveImageSS (so object can be used again)
                $this->serverSideImageName = null;
                $this->retrieveImageSS = false;
            }

            if($this->appearanceFile != null)
                $request .= "@_FILE".$this->appearanceFile;

            if($this->imageType != null && strlen(trim($this->imageType)) > 0)
                $request .= "@_IMGTYPE".$this->imageType;

            if(!$this->useCache)
            {
                $request .= "@_DONTCACHE";
            }

            // width and height set explicitly?
            $this->validateHeightWidth();
            $request .= "@_WIDTH".$this->width;
            $request .= "@_HEIGHT".$this->height;

            if($this->htmlWidth != "0")
                $request .= "@_HTMLWIDTH".$this->encodeString($this->htmlWidth);
            if($this->htmlHeight != "0")
                $request .= "@_HTMLHEIGHT".$this->encodeString($this->htmlHeight);
            if($this->extraHTMLAttributes != null && $this->extraHTMLAttributes != " ")
            {
                $this->validateExtraHTMLAttributes();
                $request .= "@_HTMLATTRIBUTES".$this->encodeString($this->extraHTMLAttributes);
            }

            if($this->bgColor != null)
                $request .= "@_BGCOLOR".$this->bgColor;
            if($this->svgTemplate != null)
                $request .= "@_USESVGTEMPLATE".$this->svgTemplate;
            if($this->extraCTSCommands != null)
                $request .= $this->extraCTSCommands;
            if($this->pcScript != null)
                $request .= "@_PCSCRIPT" . $this->encodeString($this->pcScript);
            // add commands in cmdVector to request
            if($this->cmdVector != null && count($this->cmdVector) > 0)
            {
                for($i=0; $i < count($this->cmdVector); $i++)
                {
                    $request .= $this->cmdVector[$i];
                }
            }
            if($this->objectParamTag != null)
                $request .= "@_OPTAG" . $this->encodeString($this->objectParamTag);
            if($this->genericData != null)
                $request .= "@_OBJCMD" . $this->encodeString($this->genericData);
            if($this->userAgent != null)
                $request .= "@_USRAGENT" . $this->encodeString($this->userAgent);
            if($this->fallback != null && (strcasecmp($this->fallback, "none") != 0))
            {
                // valid options: "strict", and "loose"
                if((strcasecmp($this->fallback, "strict") == 0) || (strcasecmp($this->fallback, "loose") == 0))
                    $request .= "@_FALLBACK".$this->fallback;
            }
            if($this->returnDescriptiveLink)
            {
                $this->validateReturnDescriptiveLink();
                $request .= "@_TDREQUIRED".$this->language;
            }
            //if($this->tableObject != null)
            if(isset($this->tableObject))
                $request .= "@_TABLEOBJECT" . $this->encodeString($this->tableObject);

            // appendServerInfoSlash boolean value instructs Corda Server to either
            // add a slash to the server info upon return or not
            // this is a work around for WebLogic using the Corda Servlet Redirector
            if(!$this->appendServerInfoSlash)
                $request .= "@_NOSINFOSLASH";

            // suppress server-side auto switch from gif to png
            if(!$this->autoSwitchToPNG)
                $request .= "@_NOPNGAUTOSWITCH";

            // return full url request(s) within the embedding html
            if($this->makeFullRequest)
            {
                // maxRequestLength is zero when not set
                // must be (greater than zero) and (less than/equal to request len)
                if(($this->maxRequestLength == 0) || ($this->maxRequestLength > 0 && strlen($request) <= $this->maxRequestLength))
                {
                    $request .= "@_MAKEFULLREQUEST";
                }
            }

            // replace carriage return and line feeds from the request
            $request = strtr($request, chr(10), ' ');
            $request = strtr($request, chr(13), ' ');

            // build query
            if($this->isPostRequest)
            {
                $query =    "POST " . $this->requestPath . " HTTP/1.0\r\n" .
                        "Host: $this->commportHost:$this->commportPort\r\n" .
                        "Content-Length: " . strlen($request) . "\r\n" .
                        "Content-Type: text/html\r\n" .
                        "\r\n" .
                        $request;
            }
            else
            {
                if(isset($this->requestPath))
                {
                    $requestPath = $this->requestPath;
                }
                else
                {
                    $requestPath = "/";
                }
                $query =    "GET $requestPath?$request HTTP/1.0\r\n" .
                        "Host: $this->commportHost:$this->commportPort\r\n" .
                        "\r\n";
            }

            // open server connection
            $fp = fsockopen($this->commportHost, $this->commportPort);

            // if connection failed return false;
            if(!$fp)
            {
                if((empty($clusterMonitorAddress)) ||
                    ((!empty($clusterMonitorAddress)) && (indexOf($commportHost, "127.0.0.1") == 0) && ($commportPort == 2002)))
                {
                    $response = "<b>ERROR: Can't connect to the commport of Corda Server - Connection Failed</b>";
                    $notDone = false;
                }
            }
            else
            {
                // send query
                fputs($fp, $query);

                // discard HTTP header
                while(trim(fgets($fp, 1024)) != "");

                // assign the rest of the file to response
                while(!feof ($fp))
                {
                    $buffer = fgets($fp, 1024);
                    $response .= $buffer;
                }

                // close server connection
                fclose($fp);

                $notDone = false;
            }
        }

        return $response;
    }

    //_________________________________________________________________________
    //
    // addHTMLTable(String obj, String title)
    //
    // no parameters: use "graph" element and title if there is one
    // parameters: obj, title (for an appearance file graph object and title)
    // title parameter is optional
    //
    // this function can be called multiple times and the parameters will
    // be appended to the previous ones.
    //
    // calling it with no parameters clears the list
    //_________________________________________________________________________

    function addHTMLTable($obj=null, $title=null)
    {
        $delim = "PCDELIM";
        $bTag = "<".$delim.">";
        $eTag = "</".$delim.">";

        if(empty($obj))
        {
            $this->tableObject = "";
        }
        else
        {
            // replace empty or null tableObject string
            if(!$this->tableObject)
            {
                $this->tableObject = $bTag . $obj . $eTag;
            }
            else // append to tableObject string
            {
                $this->tableObject .= $bTag . $obj . $eTag;
            }

            // add title to tableObject string
            if(!empty($title))
            {
                $this->tableObject .= $bTag . $title . $eTag;
            }
            else
            {
                $this->tableObject .= $bTag . $eTag;
            }
        }
    }

    //_________________________________________________________________________
    //
    // loadCommandFile(String ldRQ, String encoding=null)
    //
    // takes a url or filename
    // add ldRQ string with @_LOADREQUEST to cmdVector
    //_________________________________________________________________________
    
    function loadCommandFile($ldRQ, $encoding=null)
    {
        if(!empty($encoding))
        {
            $this->cmdVector[] = "@_ENCLOADREQUEST" . $encoding . "," . $this->encodeString($ldRQ);
        }
        else
        {
            $this->cmdVector[] = "@_LOADREQUEST" . $this->encodeString($ldRQ);
        }
    }

    //_________________________________________________________________________
    //
    // saveImageToCordaServer(String fileName)
    //_________________________________________________________________________

    function saveImageToCordaServer($fileName, $pw=null)
    {
        // use class password variable if pw not passed
        if($pw != null)
        {
            $passwd = $pw;
        }
        else
        {
            $passwd = $this->password;
        }

        // save imageType
        $saveImageAs = "";
        if($this->imageType != null && strlen(trim($this->imageType)) > 0)
        {
            $saveImageAs = $this->imageType;
        }

        // change image type to URL so we get a url back from the comm port
        $this->imageType = "URL";

        // request a url from the comm port
        $requestStr = $this->getCommportResponse("@_RTNHTML@_EMBVER".$this->CordaEmbedderVersion."@_EMBTYPE".$this->EmbedderType);

        // add the saved image type to the new request, the @_SAVE command and the @_PW command
        $requestStr .= "@_" . strtoupper($saveImageAs) . "@_SAVE" . $fileName;

        // restore saved imageType
        $this->imageType = $saveImageAs;

        if( ($passwd != null) && (strlen($passwd) > 0) )
        {
            $requestStr .= "@_PW" . $passwd;
        }

        $serverInfo = $this->getExternalServerAddress($requestStr);
        $query =    "GET $requestStr HTTP/1.0\r\n" .
                "Host: ".$serverInfo['host'].":".$serverInfo['port']."\r\n" .
                "\r\n";

        // open server connection
        $fp = fsockopen($serverInfo['host'], $serverInfo['port']);

        if(!$fp)
        {
            echo "<b>Error Contacting Corda Server - Connection Failed</b>";
        }
        else
        {
            fputs($fp, $query);

            // assign the rest of the file to response
            while(!feof ($fp))
            {
                $buffer = fgets($fp, 1024);
                $response .= $buffer;
            }

            // close server connection
            fclose($fp);
        }
    }

    //_________________________________________________________________________
    //
    // resetPCXML -- clear cmdVector
    //_________________________________________________________________________

    function resetPCXML()
    {
        $this->cmdVector = array();
    }

    //_________________________________________________________________________
    //
    // loadPCXML -- load a file or http url
    //
    // add path-filename or url with @_LOADPCXML to cmdVector
    //_________________________________________________________________________

    function loadPCXML($pcxmlFile)
    {
        $this->cmdVector[] = "@_LOADPCXML" . $this->encodeString($pcxmlFile);
    }

    //_________________________________________________________________________
    //
    // addObjectParamTag -- Add name="value" to <object><param /></object> tag
    //
    // Can be called multiple times.
    //_________________________________________________________________________

    function addObjectParamTag($name, $value)
    {
        $delim = "PCDELIM";
        $bTag = "<".$delim.">";
        $eTag = "</".$delim.">";

        if($this->objectParamTag != null && strlen($this->objectParamTag) > 0)
        {
            $this->objectParamTag .= $bTag . $name . $eTag . $bTag . $value . $eTag;
        }
        else
        {
            $this->objectParamTag = $bTag . $name . $eTag . $bTag . $value . $eTag;
        }
    }

    //_________________________________________________________________________
    //
    // addPCXML -- add or set xml data tailored to Corda Server
    //
    // add pcXml string with @_PCXML to cmdVector
    //_________________________________________________________________________

    function addPCXML($pcxml)
    {
        $this->cmdVector[] = "@_PCXML" . $this->encodeString($pcxml);
    }

    //_________________________________________________________________________
    //
    // getCordaEmbedderVersion()
    //_________________________________________________________________________

    function getCorda5EmbedderVersion()
    {
        return "CordaEmbedder Version " . $this->CordaEmbedderVersion;
    }

    //_________________________________________________________________________
    //
    // getCordaEmbedderVersion()
    //
    // Always point this function to the current embedder version
    //_________________________________________________________________________

    function getCordaEmbedderVersion()
    {
        return $this->getCorda5EmbedderVersion();
    }

    //_________________________________________________________________________
    //
    // loadServerSideImage(String fileName)
    //_________________________________________________________________________

    function loadServerSideImage($fileName)
    {
        $this->retrieveImageSS = true;
        $this->serverSideImageName = $fileName;
    }

    //_________________________________________________________________________
    //
    // loadData(objectName, path/url, append, id, encoding)
    //
    // 1) objectName must be an existing graph object name in the current apFile
    // 2) path/url is a filename or url with the data we want to load
    // 3) append is a boolean value which signifies whether you want to override
    // existing data or add to it
    // 4) id is the number of the table in the file or a named id of some type
    // 5) encoding is the character encoding string for the file/url
    //_________________________________________________________________________

    function loadData($objName, $path, $append_replace="replace", $id=null, $encoding=null)
    {
        // pcscript loadfile command will be modified
        // on server-side to accept filenames or urls
        $ldPCS = $objName . ".loadfile(" . $path . "," . $append_replace;

        if(!empty($id))
        {
            $ldPCS .= "," . $id;
        }

        if(!empty($encoding))
        {
            if(empty($id))
            {
                $ldPCS .= ",";
            }
            $ldPCS .= "," . $encoding;
        }

        $ldPCS .= ")";

        if($this->loadDataPCScript != null)
            $this->loadDataPCScript .= $ldPCS;
        else
            $this->loadDataPCScript = $ldPCS;

    }

    //_________________________________________________________________________
    //
    // loadMapData(objectName, layerName, path/url, id, encoding)
    //
    // 1) objectName must be an existing graph object name in the current apFile
    // 2) layerName is the layer to apply the data to
    // 3) path/url is a filename or url with the data we want to load
    // 4) id is the number of the table in the file or a named id of some type
    // 5) encoding is the character encoding string for the file/url
    //_________________________________________________________________________

    function loadMapData($objName, $layerName, $path, $id=null, $encoding=null)
    {
        // pcscript loadfile command will be modified
        // on server-side to accept filenames or urls
        $ldPCS = $objName . ".loadmapfile(" . $layerName . "," . $path;

        if(!empty($id))
        {
            $ldPCS .= "," . $id;
        }

        if(!empty($encoding))
        {
            if(empty($id))
            {
                $ldPCS .= ",";
            }
            $ldPCS .= "," . $encoding;
        }

        $ldPCS .= ")";

        if($this->loadDataPCScript != null)
            $this->loadDataPCScript .= $ldPCS;
        else
            $this->loadDataPCScript = $ldPCS;

    }

    //_________________________________________________________________________
    //
    // setData(objName, dataString)
    //
    // send one or many generic xml data sets to Corda Server
    // targeted at specific objects
    //_________________________________________________________________________

    function setData($objName, $dataString)
    {
        $delim = "PCDELIM";
        $bTag = "<".$delim.">";
        $eTag = "</".$delim.">";

        // replace all occurances of \n with <PCNL> in dataString
        $dataString = preg_replace("/\n/", "<PCNL>", $dataString);

        $tmpData = $bTag . "setdata" . $eTag . $bTag . $objName . $eTag . $bTag . $dataString . $eTag;

        if($this->genericData != null && strlen(trim($this->genericData)) > 0)
        {
            $this->genericData .= $tmpData;
        }
        else //genericData empty or null
        {
            $this->genericData = $tmpData;
        }
    }

    //_________________________________________________________________________
    //
    // setMapData(objName, dataString)
    //
    // send one or many generic xml data sets to Corda Server
    // targeted at specific objects
    //_________________________________________________________________________

    function setMapData($objName, $layerName, $dataString)
    {
        $delim = "PCDELIM";
        $bTag = "<".$delim.">";
        $eTag = "</".$delim.">";

        // replace all occurances of \n with <PCNL> in dataString
        $dataString = preg_replace("/\n/", "<PCNL>", $dataString);

        $tmpData = $bTag . "setmapdata" . $eTag . $bTag . $objName . $eTag . $bTag . $layerName . $eTag . $bTag . $dataString . $eTag;

        if($this->genericData != null && strlen(trim($this->genericData)) > 0)
        {
            $this->genericData .= $tmpData;
        }
        else //genericData empty or null
        {
            $this->genericData = $tmpData;
        }
    }

    //_________________________________________________________________________
    //
    // addAttributeError(String)
    //_________________________________________________________________________

    function addAttributeError($attrError)
    {
        if($this->attributeErrors == null)
        {
            $this->attributeErrors = "";
        }
        $this->attributeErrors .= "<p>" . attrError . "</p>\n";
    }

} // end of class definition

?>
