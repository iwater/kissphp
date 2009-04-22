<?php
/**
 * @author liyupeng <liyupeng@tomonline-inc.com>
 * @version $WCREV$ $WCDATE$
 * @package websvn
 * @filesource $WCURL$
 *
 */

class KISS_Tools_WebSvn
{
    private $mRepositoryUrl;        // �ֿ��URL��ַ
    private $mCheckoutRootDir;      // ���ؿ����ĸ�Ŀ¼
    private $mUserName;             // svn���û���
    private $mPassWord;             // svn������
    private $mBaseCommand;          // ��׼��svn����
    private $mCurrentCommand;       // ��ǰ��svn����
    private $mCommandOutput;        // �������к�������Ϣ(һά����)
    private $mExcuteError;          // �������Ƿ�������ȷ
    private $mErrorMessage;         // ���������еĴ�����Ϣ

    /**
     * __construct
     *
     */
    function __construct ($pRepositoryUrl, $pCheckoutRootDir, $pUserName = "", $pPassWord = "")
    {
        $tSvnConfig = parse_ini_file('config.ini', true);
        if(isset($tSvnConfig['Subversion']['config_path']) && isset($tSvnConfig['Subversion']['client_path']))
        {
            $this->mSvnCommand = "{$tSvnConfig['Subversion']['client_path']} --non-interactive --no-auth-cache --config-dir \"{$tSvnConfig['Subversion']['config_path']}\" ";
        }
        else
        {
            throw new Exception("����������config.ini�ļ�");
        }

        $this->mRepositoryUrl   = dirname($pRepositoryUrl) . "/" . basename($pRepositoryUrl) . "/";
        $this->mCheckoutRootDir = dirname($pCheckoutRootDir) . "/" . basename($pCheckoutRootDir) . "/";
        $this->mUserName        = $pUserName;
        $this->mPassWord        = $pPassWord;

        $command_divide = "\"";

        if(substr(PHP_OS,0,3) != 'WIN')
        {
            $command_divide = "\"";

            $patterns[0] = "/\\\/";
            $patterns[1] = "/[$]/";
            $patterns[2] = "/`/";
            $patterns[3] = "/\"/";

            $replacements[0] = "\\\\\\";
            $replacements[1] = "\\" ."\\$";
            $replacements[2] = "\`";
            $replacements[3] = "\\\"";

            $pPassWord = preg_replace($patterns, $replacements, $pPassWord);
        }

        if($pUserName != "" && $pPassWord != "")
        {
            $this->mBaseCommand = $this->mSvnCommand . "--username {$command_divide}{$pUserName}{$command_divide} --password {$command_divide}{$pPassWord}{$command_divide} ";
        }
        else
        {
            $this->mBaseCommand = $this->mSvnCommand;
        }

        $this->mCurrentCommand = $this->mBaseCommand;

        $this->mExcuteError = false;
    }

    /**
     * ���svn��ǰ��������
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @return string
     */
    function getCurrentCommand()
    {
        return $this->mCurrentCommand;
    }

    /**
     * ���svn�������к�����
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @return string
     */
    function getCommandOutput()
    {
        return $this->mCommandOutput;
    }

    /**
     * ��shell����������
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string $pCommand      ���е�����
     * @return string $pReturnFormat ����shell�˵���Ϣ�ĸ�ʽ��string|array
     */
    function runCommand($pCommand = "", $pReturnFormat = "string")
    {
        $tOutPut = array ();

        $tSetLangCmd = eregi("linux", strtolower(PHP_OS)) ? "export LANG=zh_CN.gb18030;" : "";
        $pCommand = $tSetLangCmd . $pCommand;

        $pCommand .= " 2>&1";

        if ($handle = popen($pCommand, "r"))
        {
            while (!feof($handle))
            {
                $tMsg = rtrim(fgets($handle));
                if($tMsg != "")
                {
                    $tOutPut[] = $tMsg;
                }
                if(!$this->mExcuteError && substr($tMsg, 0, 4) == 'svn:')
                {
                    $this->mExcuteError = true;
                }
            }
            pclose($handle);
        }

        if($this->mExcuteError)
        {
            $this->mErrorMessage = implode("\n", $tOutPut);
        }

        if($pReturnFormat == "array")
        {
            if(count($tOutPut) == 1 && current($tOutPut) == "")
            {
                $tOutPut = array();
            }
            return $tOutPut;
        }
        elseif($pReturnFormat == "string")
        {
            return implode("", $tOutPut);
        }
    }

    /**
     * ����svn����
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @return string $pReturnFormat ����shell�˵���Ϣ�ĸ�ʽ��string|array
     * @access protected
     */
    function runSvnCommand($pReturnFormat = "string")
    {
        $this->mCommandOutput = $this->runCommand($this->mCurrentCommand, $pReturnFormat);
    }

    /**
     * �ж�svn�����Ƿ����гɹ�
     *
     * @return bool $pExcuteError ���гɹ�����true��ʧ�ܷ���false
     * @access protected
     */
    function isFailed()
    {
        return $this->mExcuteError;
    }

    /**
     * ���svn����ʧ�ܵ���Ϣ
     *
     * @return bool $pExcuteError ���гɹ�����true��ʧ�ܷ���false
     * @access protected
     */
    function getFailedMsg()
    {
        if($this->isFailed())
        {
            return $this->mErrorMessage;
        }
        return "";
    }

    /**
     * ׷��svn���������
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  mix    �������Ӷ��svn�Ĳ���
     */
    function supperAddParam()
    {
        $pAddCommandList = func_get_args();
        foreach($pAddCommandList as $param)
        {
            $this->mCurrentCommand .= "{$param} ";
        }
    }

    /**
     * ���õ�ǰ��svn����
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     */
    function resetCurrentCommand()
    {
        $this->mCurrentCommand = $this->mBaseCommand;
    }

    /**
     * ת��svn�������Ϣ�����е������ַ�
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access public
     * @param  string $pMsg      ��Ϣ
     * @return string            ת������Ϣ
     */
    function transferred($pMsg = "")
    {
        $pMsg = str_replace("`", "\\`", $pMsg);        // ת��SHELL�˵������ַ�`
        $pMsg = str_replace("\$", "\\$", $pMsg);       // ת��SHELL�˵������ַ�$
        return $pMsg;
    }

    /**
     * �Ӱ汾����ȡ��һ������
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     */
    function checkout()
    {
        $this->resetCurrentCommand();
        if(!file_exists($this->mCheckoutRootDir) && $this->mCheckoutRootDir != "")
        {
            mkdir($this->mCheckoutRootDir);
        }

        $this->supperAddParam("checkout" , $this->mRepositoryUrl, $this->mCheckoutRootDir);
        $this->runSvnCommand();
    }

    /**
     * ���޸Ĵӹ��������ύ���汾��
     *
     * @author         liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  mix     $pCommitPath  �ύ��Ŀ���ļ��л��ļ�����Ե�ַ(���$this->mCheckoutRootDir)�����������ļ�����һά�����ʾ
     * @param  string  $pMsg         �ύ��Ϣ
     */
    function commit($pCommitPath = "", $pMsg = "")
    {
        $this->resetCurrentCommand();
        $pMsg = $this->transferred($pMsg);
        $this->supperAddParam("commit");
        $tCommitPath = $pCommitPath;

        if(is_array($tCommitPath) && !empty($tCommitPath))
        {
            foreach($tCommitPath as $tPath)
            {
                $this->supperAddParam($this->mCheckoutRootDir . $tPath);
            }
        }
        else
        {
            $this->supperAddParam($this->mCheckoutRootDir . $tCommitPath);
        }

        $this->supperAddParam("-m", "\"{$pMsg}\"");
        $this->runSvnCommand();
    }

    /**
     * �����ļ���Ŀ¼��������ӵ��ֿ���
     *
     * @author         liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pAddPath     ���ӵ�Ŀ���ļ��л��ļ���Ĭ�ϴ�$this->mCheckoutRootDir���ӹ�������
     * @access protected
     */
    function add($pAddPath = "*")
    {
        if($pAddPath == "")
        {
            $pAddPath = "*";
        }
        $this->resetCurrentCommand();
        $this->mCurrentCommand = $this->mSvnCommand . "add " . $this->mCheckoutRootDir . $pAddPath;
        $this->mCurrentCommand = str_replace("--non-interactive --no-auth-cache ", "", $this->mCurrentCommand);
        $this->runSvnCommand();
    }

    /**
     * �ݹ��ύһ��·���Ŀ�����url
     *
     * @author         liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pAddPath            ���ӵ�Ŀ���ļ��л��ļ���·��
     * @param  string  $pAddFileName        �����Ŀ��ֿ�url���ļ���������·����(���$this->mRepositoryUrl)
     * @param  string  $pMsg                �ύ��Ϣ
     * @access protected
     */
    function import($pAddPath, $pAddFileName="", $pMsg="import")
    {
        $this->resetCurrentCommand();
        $pMsg = $this->transferred($pMsg);

        if($pAddPath != "")
        {
            if(trim($pAddFileName)=="")
            {
                $pAddFileName = basename($pAddPath);
            }
            $this->supperAddParam("import" , $pAddPath, $this->mRepositoryUrl . $pAddFileName);
            $this->supperAddParam("-m", "\"{$pMsg}\"");
            $this->runSvnCommand();
        }
    }

    /**
     * ����һ���ļ��е�url
     *
     * @author         liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pAddDir         ���ӵ�Ŀ���ļ��е�·��������ڲֿ�ĸ�URL�����Ӷ��Ŀ¼�����á�,���ָ�
     * @param  string  $pMsg            �ύ��Ϣ
     * @access protected
     */
    function mkdir($pAddDir, $pMsg = "mkdir")
    {
        $this->resetCurrentCommand();
        $pMsg = $this->transferred($pMsg);

        if($pAddDir != "")
        {
            $tAddDirList = explode(",", $pAddDir);
            $this->supperAddParam("mkdir");
            foreach($tAddDirList as $value)
            {
                $this->supperAddParam($this->mRepositoryUrl . trim($value));
            }
            $this->supperAddParam("-m", "\"{$pMsg}\"");
            $this->runSvnCommand();
        }
    }

    /**
     * �ڲֿ��URl��ַ���ƶ�һ���ļ���Ŀ¼
     *
     * @author         liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pRenameSrc   ��������Ŀ���ļ���Ŀ¼
     * @param  string  $pRenameDst   ����������ļ���Ŀ¼
     * @param  string  $pMsg         �ύ��Ϣ
     */
    function move($pRenameSrc, $pRenameDst, $pMsg = "rename")
    {
        $this->resetCurrentCommand();
        $pMsg = $this->transferred($pMsg);

        if($pRenameSrc != "")
        {
            $this->supperAddParam("move", $this->mRepositoryUrl . $pRenameSrc, $this->mRepositoryUrl . $pRenameDst);
            $this->supperAddParam("-m", "\"{$pMsg}\"");
            $this->runSvnCommand();
        }
    }

    /**
     * �ڱ��ؿ����ƶ�һ���ļ���Ŀ¼
     *
     * @author         liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pRenameSrc   ��������Ŀ���ļ���Ŀ¼
     * @param  string  $pRenameDst   ����������ļ���Ŀ¼
     */
    function localMove($pRenameSrc, $pRenameDst)
    {
        $this->resetCurrentCommand();

        if($pRenameSrc != "")
        {
            $this->supperAddParam("move", $this->mCheckoutRootDir . $pRenameSrc, $this->mCheckoutRootDir . $pRenameDst);
            $this->runSvnCommand();
        }
    }

    /**
     * ͨ��urlֱ��ɾ���ֿ��е��ļ���Ŀ¼
     *
     * @author         liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pDeletePath  ɾ����Ŀ���ļ��л��ļ���url��Ե�ַ(���$this->mRepositoryUrl)
     * @param  string  $pMsg         �ύ��Ϣ
     * @access protected
     */
    function delete($pDeletePath, $pMsg = "delete")
    {
        $this->resetCurrentCommand();
        $pMsg = $this->transferred($pMsg);

        if($pDeletePath != "")
        {
            $this->supperAddParam("delete" , $this->mRepositoryUrl . $pDeletePath, "-m", "\"{$pMsg}\"");
            $this->runSvnCommand();
        }
    }

    /**
     * ͨ��������ַɾ���ļ���Ŀ¼���������
     *
     * @author         liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pDeletePath  ɾ����Ŀ���ļ��л��ļ���������Ե�ַ(���$this->mCheckoutRootDir)
     * @param  string  $pMsg         �ύ��Ϣ
     * @access protected
     */
    function localDelete($pDeletePath)
    {
        $this->resetCurrentCommand();

        if($pDeletePath != "")
        {
            $this->supperAddParam("delete" , $this->mCheckoutRootDir . $pDeletePath);
            $this->runSvnCommand();
        }
    }

    /**
     * ���¹�������
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @param string  $pUpdatePath  ���µ�Ŀ���ļ��л��ļ������·����Ĭ�ϸ���������������
     * @access protected
     */
    function update($pUpdatePath = "")
    {
        $this->resetCurrentCommand();

        if($pUpdatePath != "")
        {
            $this->supperAddParam("update" , $this->mCheckoutRootDir . $pUpdatePath);
        }
        else
        {
            $this->supperAddParam("update" , $this->mCheckoutRootDir);
        }
        $this->runSvnCommand();
    }

    /**
     * �ݹ�������������
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @param string  $pCleanPath  ������Ŀ���ļ��л��ļ������·����Ĭ������������������
     * @access protected
     */
    function clean($pCleanPath)
    {
        $this->resetCurrentCommand();

        if($pCleanPath != "")
        {
            $this->supperAddParam("cleanup" , $this->mCheckoutRootDir . $pCleanPath);
        }
        else
        {
            $this->supperAddParam("cleanup" , $this->mCheckoutRootDir);
        }
        $this->runSvnCommand();
    }

    /**
     * �ݹ�ָ���������
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @param string  $pCleanPath  �ָ���Ŀ���ļ��л��ļ������·����Ĭ�ϻָ�������������
     * @access protected
     */
    function revert($pCleanPath = "")
    {
        $this->resetCurrentCommand();
        $this->supperAddParam("revert", "-R", $this->mCheckoutRootDir . $pCleanPath);
        $this->mCurrentCommand = str_replace("--non-interactive --no-auth-cache ", "", $this->mCurrentCommand);
        $this->runSvnCommand();
    }

    /**
     * �鿴һ��xml�ַ����Ƿ��ܱ���ȷ����
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pXmlString  Ҫ������xml�ַ���
     * @return bool    ���Ա���ȷ�����򷵻�true���������򷵻�false
     */
    public function checkXmlParse($pXmlString = "")
    {
        $tPointer = xml_parser_create();
        $tParse = xml_parse_into_struct($tPointer, $pXmlString, $vals, $index);
        xml_parser_free($tPointer);

        if($tParse == 0)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * �鿴�ֿ���ļ��б�
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pListUrl  Ŀ���ļ��л��ļ�����Ե�ַ(���$this->mRepositoryUrl)
     * @return array
     */
    function listInfo($pListUrl = "")
    {
        $this->resetCurrentCommand();

        $this->supperAddParam("list" , $this->mRepositoryUrl . $pListUrl, "--xml");
        $this->runSvnCommand();

        if(!$this->checkXmlParse($this->mCommandOutput))
        {
            return array();
        }

        $tListInfo = array();
        $tArray = simplexml_load_string($this->mCommandOutput);

        if(!isset($tArray->list)||!isset($tArray->list->entry))
        {
            return array();
        }

        $tResult = $tArray->xpath("list/entry");
        foreach($tResult as $key => $value)
        {
            $tListInfo[$key]["author"] = iconv("UTF-8", "GB18030", $value->commit[0]->author);
            $tListInfo[$key]["name"] = iconv("UTF-8", "GB18030", $value->name);
            $tListInfo[$key]["size"] = intval(iconv("UTF-8", "GB18030", $value->size));
            $tListInfo[$key]["date"] = preg_replace("/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2}).\d{6}Z$/", "$1 $2", iconv("UTF-8", "GB18030", $value->commit->date));
            $tBjTime = strtotime($tListInfo[$key]["date"])+3600*8;
            $tListInfo[$key]["date"] = date("Y-m-d H:i:s", $tBjTime);
            foreach($tArray->list->entry->commit[0]->attributes() as $tagKey => $tagValue)
            {
                $tListInfo[$key][$tagKey] = strval($tagValue);
            }
            foreach($tArray->list->entry[$key]->attributes() as $tagKey => $tagValue)
            {
                $tListInfo[$key][$tagKey] = strval($tagValue);
            }
        }
        return $tListInfo;
    }

    /**
     * �鿴�ֿ��ļ���·����Ϣ
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pListUrl  Ŀ���ļ��л��ļ�����Ե�ַ(���$this->mRepositoryUrl)
     * @return array
     */
    function info($pListUrl = "")
    {
        $this->resetCurrentCommand();
        $this->supperAddParam("info" , $this->mRepositoryUrl . $pListUrl, "--xml");

        $this->runSvnCommand();

        if(!$this->checkXmlParse($this->mCommandOutput))
        {
            return array();
        }

        $tArray = simplexml_load_string($this->mCommandOutput);
        $tResult = $tArray->xpath("/info/entry");

        $tInfoList = array();
        foreach($tResult as $key => $value)
        {
            foreach($tArray->entry[$key]->attributes() as $tagKey => $tagValue)
            {
                $tInfoList[$key][$tagKey] = strval($tagValue);
            }

            $tInfoList[$key]["url"] = iconv("UTF-8", "GB18030",$value->url);
            $tInfoList[$key]["repository"]["root"] = iconv("UTF-8", "GB18030",$value->repository->root);

            foreach($tArray->entry[$key]->commit->attributes() as $tagKey => $tagValue)
            {
                $tInfoList[$key]["commit"][$tagKey] = strval($tagValue);
            }
            $tInfoList[$key]["commit"]["author"] = iconv("UTF-8", "GB18030",$value->commit->author);
            $tInfoList[$key]["commit"]["date"] = preg_replace("/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2}).\d{6}Z$/", "$1 $2", iconv("UTF-8", "GB18030", $value->commit->date));
            $tBjTime = strtotime($tInfoList[$key]["commit"]["date"])+3600*8;
            $tInfoList[$key]["commit"]["date"] = date("Y-m-d H:i:s", $tBjTime);
        }

        return $tInfoList;
    }

    /**
     * �鿴�ֿ��ļ�����ʷ��־��Ϣ
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pListUrl         Ŀ���ļ��л��ļ�����Ե�ַ(���$this->mRepositoryUrl)
     * @param  string  $pStartReversion  �鿴Ŀ�����ʼ�汾��,������Ϊ�鿴�����ĵ�����ʽ����Ϊ���֣����ڣ�֧�ֹؼ��֣�"HEAD", "BASE", "COMMITTED", "PREV"
     * @param  string  $pEndReversion    �鿴Ŀ��Ľ����汾��,������Ϊ�鿴�����ĵ�����ʽ����Ϊ���֣����ڣ�֧�ֹؼ��֣�"HEAD", "BASE", "COMMITTED", "PREV"
     * @return array
     */
    function log($pListUrl = "", $pStartReversion = "", $pEndReversion = "")
    {
        $this->resetCurrentCommand();

        $this->supperAddParam("log" , $this->mRepositoryUrl . $pListUrl, "--xml", "-v");

        $pReversion = $this->getStandardReversions($pStartReversion, $pEndReversion);

        if($pReversion != "")
        {
            $this->supperAddParam("-r" , $pReversion);
        }

        $this->runSvnCommand();

        if(!$this->checkXmlParse($this->mCommandOutput))
        {
            return array();
        }

        $tArray = simplexml_load_string($this->mCommandOutput);
        $tResult = $tArray->xpath("/log/logentry");
        $tLogInfo = array();
        foreach($tResult as $key => $value)
        {
            $tLogInfo[$key]["author"] = iconv("UTF-8", "GB18030", $value->author);
            $tLogInfo[$key]["date"] = preg_replace("/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2}).\d{6}Z$/", "$1 $2", iconv("UTF-8", "GB18030", $value->date));
            $tBjTime = strtotime($tLogInfo[$key]["date"])+3600*8;
            $tLogInfo[$key]["date"] = date("Y-m-d H:i:s", $tBjTime);
            $tLogInfo[$key]["msg"] = iconv("UTF-8", "GB18030", $value->msg);

            $i = 0;
            foreach($tArray->logentry[$key]->paths->path as $tPathKey => $tPath)
            {
                $tLogInfo[$key]["paths"][$i]["path"] = strval($tPath);
                foreach($tPath->attributes() as $tagPathKey => $tagPathValue)
                {
                    $tLogInfo[$key]["paths"][$i][$tagPathKey] = strval($tagPathValue);
                }
                $i ++;
            }

            foreach($tArray->logentry[$key]->attributes() as $tagKey => $tagValue)
            {
                $tLogInfo[$key][$tagKey] = strval($tagValue);
            }
        }

        return $tLogInfo;
    }

    /**
     * ͨ��URLȡ���ض��ļ�������
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access public
     * @param  string $pUrl      �ļ���URL��ַ
     * @param  int    $pVersion  �ļ��İ汾��
     * @return string            �ļ�������
     */
    function cat($pUrl, $pVersion=0)
    {
        $this->resetCurrentCommand();

        $this->supperAddParam("cat" , $this->mRepositoryUrl . $pUrl);

        if($pVersion > 0)
        {
            $this->supperAddParam("-r" , $pVersion);
        }

        ob_start();
        passthru($this->getCurrentCommand());
        $this->mCommandOutput = ob_get_contents();
        ob_clean();

        return $this->mCommandOutput;
    }

    /**
     * ��reversion�����淶Ϊsvn -rѡ����õĲ���
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access public
     * @param  string  $pStartReversion  Ŀ�����ʼ�汾��,������Ϊ�鿴�����ĵ�����ʽ����Ϊ���֣�����
     * @param  string  $pEndReversion    Ŀ��Ľ����汾��,������Ϊ�鿴�����ĵ�����ʽ����Ϊ���֣�����
     * @return string                    ƴ�Ӻ���ļ��İ汾��
     */
    function getStandardReversions($pStartReversion = "", $pEndReversion = "")
    {
        $pStartReversion = $this->specReversion($pStartReversion);
        $pEndReversion = $this->specReversion($pEndReversion);

        if($pStartReversion != "")
        {
            if($pEndReversion != "")
            {
                return $pStartReversion . ":" . $pEndReversion;
            }
            else
            {
                return $pStartReversion;
            }
        }
        else
        {
            return "";
        }
    }

    /**
     * ��reversion�����淶Ϊsvn -rѡ����õĲ���
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access public
     * @param  int    $pVersion  �ļ��İ汾��
     * @return string            �ļ��İ汾��
     */
    function specReversion($pReversion = "")
    {
        $gReversionKeyWords = array("HEAD", "BASE", "COMMITTED", "PREV");

        if($pReversion == "")
        {
            return $pReversion;
        }

        if(strtotime($pReversion))
        {
            return "{\"" . $pReversion . "\"}";
        }
        elseif(in_array($pReversion, $gReversionKeyWords))
        {
            return $pReversion;
        }
        else
        {
            return intval($pReversion);
        }
    }

    /**
     * �鿴�ļ�/Ŀ¼�Ƿ������ֿ���
     *
     * @author        liyupeng <liyupeng@tomonline-inc.com>
     * @access protected
     * @param  string  $pUrl         Ŀ���ļ��л��ļ�����Ե�ַ(���$this->mRepositoryUrl)
     * @return array
     */
    function isInRepository($pUrl = "")
    {
        $tUrlInfo = $this->info($pUrl);
        if(empty($tUrlInfo))
        {
            return false;
        }
        return true;
    }
}
?>