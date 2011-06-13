<?php // $Id: language.lib.php 11883 2009-08-19 12:32:24Z dimitrirambout $
if ( count( get_included_files() ) == 1 ) die( '---' );
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/**
 * Get the currently time
 *
 * @return  - time in microseconds
 */

function get_time ()
{
 $mtime = microtime();
 $mtime = explode(" ",$mtime);
 $mtime = $mtime[1] + $mtime[0];

 return $mtime;
}

/**
 * Browse path with language files and extract variables name
 * and their values (retrieve_lang_vars function).
 * Script used in extract_var_from_lang_files.php
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $dirPath - directory path
 * @param  - $languageName - language name of the translation file
 */

function glance_through_dir_lang ($dirPath, $languageName)
{
    chdir ($dirPath) ;
    $handle = opendir($dirPath);

    $fileList = array();
    $dirList  = array();

    while ($element = readdir($handle) )
    {
        if ( $element == "." || $element == ".." || strstr($element,"~")
             || strstr($element,"#"))
        {
            continue; // skip the current and parent directories and some files
        }

        // browse only old file name .php and LANG_COMPLETE_FILENAME (complete.lang.php)

        $pos = strpos($element,'.lang.php');

        if ( is_file($element)
             && $element != 'locale_settings.php'
             && substr(strrchr($element, '.'), 1) == 'php'
             && ( strlen($element) != $pos + strlen('.lang.php') || $element == LANG_COMPLETE_FILENAME)
           )
        {
            $fileList[] = $dirPath."/".$element;
        }
        if ( is_dir($element) )
        {
            $dirList[] = $dirPath."/".$element;
        }
    }

    if ( sizeof($fileList) > 0)
    {
        echo "<ol>";
        foreach($fileList as $thisFile)
        {
            echo "<li>" . $thisFile . "</li>\n";
            retrieve_lang_var($thisFile, $languageName);
        }
        echo "</ol>\n";
        echo "<p>" . sizeof($fileList) . " file(s).</p>\n";
    }

    if ( sizeof($dirList) > 0)
    {
        foreach($dirList as $thisDir)
        {
            glance_through_dir_lang ($thisDir, $languageName); // recursion
        }
    }
}

/**
 * Get defined language variables of the script and store them.
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - string $fileName - language file where to retrieve get_lang(' variable') translation
 * @param  - string $languageName - language name of the translation
 */

function retrieve_lang_var($fileName, $languageName)
{
    global $_lang;

    $_lang = array();

    include($fileName);

    store_lang_var($_lang, $fileName, $languageName);
}


function initialize_lang_var()
{

    global $problemMessage, $tbl_translation;
    
    $sql = "CREATE TABLE IF NOT EXIST ". $tbl_translation ." (
     id INTEGER NOT NULL auto_increment,
     language VARCHAR(250) NOT NULL,
     varName VARCHAR(250) BINARY NOT NULL,
     varContent VARCHAR(250) NOT NULL,
     varFullContent TEXT NOT NULL,
     sourceFile VARCHAR(250) NOT NULL,
     used tinyint(4) default 0,
     INDEX index_language (language,varName),
     INDEX index_content  (language,varContent),
     PRIMARY KEY(id))";
    
    claro_sql_query($sql) or die($problemMessage);

}


/**
 * store the lang variables in a centralized repository
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - array $languageVarList - list of the language variable
 *           'key' is the variable name, 'content' is the variable content
 * @param  - string $sourceFileName - file name from where the variables
 *           are coming
 * @param  - string $languageName - name of the language translation
 */

function store_lang_var($languageVarList, $sourceFileName, $languageName)
{

    global $problemMessage, $tbl_translation;

    foreach ( $languageVarList as $thisVarKey => $thisVarContent )
    {
        if ( ! empty($thisVarContent) )
        {
            $sql = "INSERT INTO " . $tbl_translation . " SET
             VarName    = \"". claro_sql_escape($thisVarKey)."\",
             VarContent = \"". claro_sql_escape($thisVarContent) ."\",
             varFullContent  = \"". claro_sql_escape($thisVarContent) ."\",
             language   = \"".claro_sql_escape($languageName)."\",
             sourceFile = \"" . str_replace(get_path('rootSys'),"",$sourceFileName) ."\"";
            mysql_query($sql) or die($problemMessage);
        }
    }

}
    
    
    function google_translation($from,$to,$string)
    {
        $string = urlencode($string);
        ### recherche la source chez google avec le mot à traduire: $q
        pushClaroMessage(__LINE__ . '<pre>"http://translate.google.com/translate_t?text=$string&langpair=$from|$to&hl=fr&ie=UTF-8&oe=UTF-8" ='.var_export("http://translate.google.com/translate_t?text=$string&langpair=$from|$to&hl=fr&ie=UTF-8&oe=UTF-8",1).'</pre>','dbg');
        $source = implode ('', file ("http://translate.google.com/translate_t?text=$string&langpair=$from|$to&hl=fr&ie=UTF-8&oe=UTF-8"));
        ### decoupage de $source au debut
        $source = strstr($source, '<div id=result_box dir=ltr>');
        ### decoupage de $source à la fin
        $fin_source = strstr($source, '</div>');
        ### supprimer $fin_source de la chaine $source
        $proposition = str_replace("$fin_source","", $source);
        $proposition = str_replace("<div id=result_box dir=ltr>","", $proposition);
        ### affichage du resultat
        return $proposition;
    }
        
    



/**
 * Browse a dirname and returns all files and subdirectories
 *
 * @return - array('files'=>array(), 'directories=>array())
 *
 * @param  - string $dirname
 * @param  - boolean $recurse
 */

function scan_dir($dirname,$recurse=FALSE)
{
    static $file_array=array();
    static $dir_array=array();
    static $ret_array=array();

    if($dirname[strlen($dirname)-1]!='/')
    {
        $dirname.='/';
    }

    $handle=opendir($dirname);

    while (false !== ($element = readdir($handle)))
    {
        if ( is_scannable($dirname.$element, array('claroline/inc',
                                                   'claroline/wiki/lib') )
           )
        {
            if(is_dir($dirname.$element))
            {
                $dir_array[]=$dirname.$element;

                if($recurse)
                {
                    scan_dir($dirname.$element.'/',$recurse);
                }
            }
            else
            {
                $file_array[]=$dirname.$element;
            }
        }
    }

    closedir($handle);

    $ret_array['files']=$file_array;
    $ret_array['directories']=$dir_array;

    return $ret_array;

}

/**
 * Check if the file or directory is an element scannable
 *
 * @return - boolean
 * @param  - string
 * @param  - array
 * @param  - array
 */

function is_scannable($filePath,
                      $additionnalForbiddenDirNameList = array(),
                      $additionnalForbiddenFileSuffixList = array() )
{
    global $rootSys;

    $baseName    = basename($filePath);
    $parentPath  = str_replace('\\', '/', dirname($filePath));
    $parentPath  = str_replace($rootSys, '', $parentPath);

    $forbiddenDirNameList    = array_merge( array('claroline/lang',
                                                  'claroline/inc/conf',
                                                  'claroline/inc/lib/core',
                                                  'courses',
                                                  'platform',
                                                  'module',
                                                  'tmp',
                                                  'claroline/admin/devTools',
                                                  'claroline/claroline_garbage'),
                                            $additionnalForbiddenDirNameList);
    $forbiddenParentNameList = array('CVS');

    $forbiddenFileNameList   = array('.', '..','CVS');

    $forbiddenBaseNameList   = array_merge($forbiddenFileNameList,
                                           $forbiddenDirNameList);

    $forbiddenFileSuffixList = array_merge( array('.lang.php', '~'),
                                            $additionnalForbiddenFileSuffixList);

    $forbiddenFilePrefixList = array('~', '#', '\\.');

    // BASENAME CHECK

    if (is_file($filePath) && ! preg_match('/.php$/i',$baseName) ) return false;

    if (in_array($baseName, $forbiddenBaseNameList) )              return false;

    foreach($forbiddenFileSuffixList as $thisForbiddenSuffix)
    {
        if (preg_match('|'.$thisForbiddenSuffix.'^|', $baseName) ) return false;
    }

    foreach($forbiddenFilePrefixList as $thisForbiddenPrefix)
    {
        if (preg_match('|$'.$thisForbiddenPrefix.'|', $baseName) ) return false;
    }

    // DIRECTORY CHECK
    foreach($forbiddenDirNameList as $thisDirName)
    {
        if ( strpos($filePath, $rootSys.$thisDirName) !== FALSE )
        {
            return false;
        }
    }


    // PARENT PATH CHECK

    $pathComponentList = explode('/', $parentPath);

    foreach($pathComponentList as $thisPathComponent)
    {
        if (in_array($thisPathComponent, $forbiddenParentNameList) ) return false;
    }

    return true;
}
    
/**
 * Store the name and sourceFile of the language variable in mysql table
 *
 * @param - array $languageVarList
 * @param - string $sourcFileName
 */

function store_lang_used_in_script($languageVarList, $sourceFileName)
{

    global $problemMessage, $tbl_used_lang;

    $sourceFileName =  str_replace(get_path('rootSys'),'',$sourceFileName);
    $languageFileName = compose_language_production_filename($sourceFileName);

    foreach($languageVarList as $thisVar)
    {
        if ( trim($thisVar) != '' )
        {
            $sql = "INSERT INTO " . $tbl_used_lang . "
                       SET VarName    = '". claro_sql_escape($thisVar) . "',
                           langFile   = '" .$languageFileName."',
                           sourceFile = '" . $sourceFileName ."'";
            mysql_query($sql) or die($problemMessage);
        }
    }

}

/**
 *
 * Detect included files in the script
 *
 * @return - array $includeFileList list of included file
 * @param - array $tokenList list of token from a script
 */

function detect_included_files(&$tokenList)
{
    $includeFileList = array();

    for ($i = 0, $tokenCount =  count($tokenList); $i < $tokenCount ; $i++)
    {
        if (   $tokenList[$i][0] === T_INCLUDE
            || $tokenList[$i][0] === T_REQUIRE
            || $tokenList[$i][0] === T_INCLUDE_ONCE
            || $tokenList[$i][0] === T_REQUIRE_ONCE )
        {
            if ( $tokenList[$i][0] === T_INCLUDE || $tokenList[$i][0] === T_REQUIRE )
            {
                $include_type = 'normal';
            }
            else
            {
                $include_type = 'once';
            }

            $includeFile = '';
            $bracketPile = 0;
            $i++;

            while(       $tokenList[$i][0] != ';'
                  &&     $tokenList[$i][0] != T_LOGICAL_OR
                  && ! ( $tokenList[$i][0] == ')' && $bracketPile == 0) )
            {
                if ( is_int($tokenList[$i][0]) )
                {
                    $token =  $tokenList[$i][1];
                }
                else
                {
                    $token =  $tokenList[$i][0];
                    if     ( $token == '(' ) $bracketPile++;
                    elseif ( $token == ')' ) $bracketPile--;
                    else
                    {
                        $token =  $tokenList[$i][0];
                    }

                }
                $includeFile .= $token;
                $i++;
            }

            // replace dirname(__FILE__) by nothing
            $includeFile = preg_replace("/dirname\(__FILE__\) *\. *(['\"])/","\\1",$includeFile);
            // replace get_path('incRepositorySys') by get_path('incRepositorySys')
            $includeFile = preg_replace('/\$includePath *\. *([\'\"])/',"\\1" . get_path('incRepositorySys'), $includeFile);
            // replace $rootSys by $rootSys
            $includeFile = preg_replace('/\$rootSys *\. *([\'\"])/',"\\1" . $GLOBALS['rootSys'],$includeFile);
            // replace $rootAdminSys by $rootAdminSys
            $includeFile = preg_replace('/\$rootAdminSys *\. *([\'\"])/',"\\1" . get_path('rootAdminSys'),$includeFile);
            // replace $clarolineRepositorySys by $clarolineRepositorySys
            $includeFile = preg_replace('/\$clarolineRepositorySys  *\. *([\'\"])/',"\\1" . $GLOBALS['clarolineRepositorySys'],$includeFile);

            $includeFileList[] = $includeFile;
        }
    } // end loop for

    return $includeFileList;
}

/**
 * Get the list of language variables in a script and its included files
 *
 * @return - array $languageVarList or boolean FALSE
 * @param - string $file
 */

function get_lang_vars_from_file($file)
{
    $languageVarList = array();

    $fileContent = file_get_contents($file);
    
    // to speed up script to not try to detect all get_lang if there is none
    if( preg_match('/get_lang|get_block/',$fileContent) )
    {
        $languageVarList = detect_get_lang($fileContent);
        $languageVarList = array_unique($languageVarList);
    }

    return $languageVarList;

}

/**
 * Extract the parameter name of get_lang function from a script
 *
 * @return - array $languageVarList
 * @param  - array $tokenList
 */

function detect_get_lang($fileContent)
{
    $languageVarList = array();

    $tokenList  = token_get_all($fileContent);
    $total_token = count($tokenList);

    $i = 0;

    // Parse token list

    while ( $i < $total_token )
    {
        $thisToken = $tokenList[$i];

        if ( is_array($thisToken) && is_int($thisToken[0]) && $thisToken[0] == T_STRING )
        {

            // find function 'get_lang'

            if ( $thisToken[1] == 'get_lang' || $thisToken[1] == 'get_block' )
            {
                $varName = '';

                $i++;

                // Parse get_lang function parameters

                while ($i < $total_token)
                {
                    $thisToken = $tokenList[$i];
                    if ( is_string($thisToken) && $thisToken == '(')
                    {
                        // bracket open - begin parsong of parameters
                        $i++;
                        continue;
                    }
                    elseif ( is_string($thisToken) && $thisToken == ')')
                    {
                        // bracket close - end parsing of parameters
                        $i++;
                        break;
                    }
                    elseif ( is_string($thisToken) && $thisToken == ',')
                    {
                        // comma, end parsing of parameters
                        $i++;
                        break;
                    }
                    elseif ( is_int($thisToken[0]) && ( $thisToken[0] == T_VARIABLE ) )
                    {
                        // variable - end parsing
                        $i++;
                        break;
                    }
                    elseif ( is_array($thisToken) )
                    {
                        // get parameters name
                        if ( $thisToken[0] == T_CONSTANT_ENCAPSED_STRING )
                        {
                            $search = array ('/^[\'"]/','/[\'"]$/','/\134\047/');
                            $replace = array('','','\'');
                            $varName .= preg_replace($search,$replace,$thisToken[1]);
                        }

                    }
                    $i++;
                }
                $varName = trim($varName);
                if ( !empty($varName) )
                {
                    $languageVarList[]=$varName;
                }
            }
        }
        $i++;

    } // end token parsing

    return $languageVarList;
}

/**
 * Extract language variables from a script
 *
 * @return - array $languageVarList
 * @param  - array $tokenList
 */

function detect_lang_var($tokenList)
{
    $languageVarList = array();

    foreach($tokenList as $thisToken)
    {
        if (is_int($thisToken[0]))
        {
            if ( is_a_lang_var($thisToken) )
            {
                $varName = str_replace('$','',$thisToken[1]);
                $languageVarList[]=$varName;
            }
        }
    }

    return $languageVarList;
}

/**
 * Check if a token is a language variable
 *
 * @return - boolean
 * @param  - token $token
 */

function is_a_lang_var($token)
{

    // token is not a variable
    if ( $token[0] != T_VARIABLE )            return false;

    $varName = str_replace('$','',$token[1]);

    if ( ! is_a_lang_varname($varName) )      return false;

    // if all the condition has been successfully passed ...
    return true;

}

/**
 * Check if a token is a language variable
 *
 * @return - boolean
 * @param  - token $token
 */

function is_a_lang_varname($var)
{

    $pos1 = strpos( $var, 'lang' );
    $pos2 = strpos( $var, 'l_'   );

    // variable is not a lang variable
    if (   ( $pos1 === FALSE || $pos1 != 0 )
        && ( $pos2 === FALSE || $pos2 != 0 )
        || ( $var == 'lang' )
       )
    {
        return false;
    }

    // these variables are not language variables
    if ( $var == 'langFile')             return false;

    $pos3 = strpos( $var, 'language');
    if ( $pos3 !== FALSE && $pos3 == 0 ) return false;

    // if all the condition has been successfully passed ...
    return true;

}

/**
 * Build the real path of the script
 * @return - string $realPath
 * @param  - string $statementString
 * @param  - string $parsedFilePath
 */

function get_real_path_from_statement($statementString, $parsedFilePath)
{
    $evaluatedPath = eval("return ".$statementString.";");

    if ( ! strstr($evaluatedPath, get_path('rootSys')) )
    {
        $realPath = realpath( dirname($parsedFilePath) .'/'. $evaluatedPath);
    }
    else
    {
        $realPath = $evaluatedPath;
    }

    if ( file_exists($realPath) )  return $realPath;
    else                           return false;
}

/**
 *
 */

function compose_language_production_filename ($file)
{
    $pos = strpos($file,'claroline/');

    if ($pos === FALSE || $pos != 0)
    {
        // if the script isn't in the claroline folder the language file base name is index
        $languageFilename = 'index';
    }
    else
    {
        // else language file basename is like claroline_folder_subfolder_...
        $languageFilename = dirname($file);
        $languageFilename = str_replace('/','_',$languageFilename);
    }

    return $languageFilename;
}

/**
 *
 */

function get_lang_path_list($path_lang)
{
    $languagePathList = array();

    $handle = opendir($path_lang);

    while ($element = readdir($handle) )
    {
        if ( $element == "." || $element == ".." || $element == "CVS" || $element == ".svn"
            || strstr($element,"~") || strstr($element,"#")
           )
        {
            continue; // skip current and parent directories
        }
        if ( is_dir($path_lang . '/' . $element) )
        {
            $path = $path_lang . '/' . $element;
            $elements = explode (".", $element);
            $name = reset($elements);
            $languagePathList[$name] = $path;
        }
    }

    return $languagePathList;
}

function load_array_translation ($language)
{
    if ( file_exists(get_path('incRepositorySys') . '/../lang/' . $language . '/complete.lang.php') )
    {
        include(get_path('incRepositorySys') . '/../lang/' . $language . '/complete.lang.php');

        $localVar = get_defined_vars();

        if ( isset($localVar['_lang']) )
        {
            return $localVar['_lang'];
        }
        else
        {
            // retrieve old language var
            $translations = array();

            foreach($localVar as $thisVarKey => $thisVarContent)
            {
                if ( is_a_lang_varname($thisVarKey) )
                {
                    // modify var name
                    $thisVarKey = preg_replace('/(^lang|^l_)/','',$thisVarKey);
                    $translations[$thisVarKey] = $thisVarContent;
                }
            }

            return $translations;
        }
    }
    return array();
}

function build_translation_line_file($key,$value)
{
    $varName = preg_replace('/\'/', '\\\'', $key);
    //$varName = preg_replace('/\\\"/', '"', $key);
    $varContent = preg_replace('/\'/', '\\\'', $value);

    $string = '$_lang[\''. $varName .'\'] = \''. $varContent .'\';' . "\n";

    return $string;
}


/**
 * Get the list of language variables in a script and its included files
 *
 * @return - array $languageVarList or boolean FALSE
 * @param - string $file
 */

function get_lang_vars_from_deffile($file)
{

    $conf_def['section'] = array();
    $conf_def_property_list = array();

    include($file);

    if(array_key_exists('config_name',$conf_def))  $deflang[] = $conf_def['config_name'];

    if(is_array($conf_def['section']))
    foreach ($conf_def['section'] as $conf_def_section)
    {
        if(array_key_exists('label',$conf_def_section)) $deflang[] = $conf_def_section['label'];
        if(array_key_exists('description',$conf_def_section)) $deflang[] = $conf_def_section['description'];
    }

    if(is_array($conf_def_property_list))
    foreach ($conf_def_property_list as $conf_def_property)
    {
        if(array_key_exists('display',$conf_def_property) && $conf_def_property['display'] === false ) continue ;

        if(array_key_exists('label',$conf_def_property)) $deflang[] = $conf_def_property['label'];
        if(array_key_exists('description',$conf_def_property)) $deflang[] = $conf_def_property['description'];
        if(array_key_exists('type',$conf_def_property)) $deflang[] = $conf_def_property['type'];
        if(array_key_exists('acceptedValue',$conf_def_property))
        {
            foreach ($conf_def_property['acceptedValue'] as $key => $acceptedValue)
            {
                if ( $conf_def_property['type'] == 'integer' )
                {
                    continue ;
                }
                elseif ( $key == 'pattern' )
                {
                    continue ;
                }
                else
                {
                    $deflang[] = $acceptedValue;
                }
            }
        }
    }
    return $deflang;
}


/**
 * store the lang variables in a centralized repository
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - array $languageVarList - list of the language variable
 *           'key' is the variable name, 'content' is the variable content
 * @param  - string $sourceFileName - file name from where the variables
 *           are coming
 * @param  - string $languageName - name of the language translation
 */

function initialize_lang_info()
{
    global $problemMessage, $tbl_tr_lang_list;        
    $sql = "CREATE TABLE IF NOT EXIST ". $tbl_tr_lang_list ."_ (
     id INTEGER NOT NULL auto_increment,
     languageName VARCHAR(250) NOT NULL,
     languagePath VARCHAR(250) NOT NULL,
     claroVersion VARCHAR(50) BINARY NOT NULL,
     sourceFile VARCHAR(250) NOT NULL,
     scanned tinyint(4) default 0,
     INDEX index_language (language),
     PRIMARY KEY(id))";
    
    claro_sql_query($sql) or die($problemMessage);
 
}

/**
 * store the lang variables in a centralized repository
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - array $languageVarList - list of the language variable
 *           'key' is the variable name, 'content' is the variable content
 * @param  - string $sourceFileName - file name from where the variables
 *           are coming
 * @param  - string $languageName - name of the language translation
 */

function store_lang_info($languagePath, $languageName, $clarolineVersion, $scanned)
{
    
    global $problemMessage, $tbl_tr_lang_list;

    
    

$sql = "INSERT INTO ". $tbl_tr_lang_list ." SET
 
 languageName = \"". addslashes($languageName)."\",
 languagePath = \"". addslashes($languagePath)."\",
 claroVersion = \"". addslashes($clarolineVersion)."\",
 scanned = " . (int) $scanned . "";
    claro_sql_query($sql) or die($problemMessage);
 
 

    
}
/**
 * store the lang variables in a centralized repository
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - array $languageVarList - list of the language variable
 *           'key' is the variable name, 'content' is the variable content
 * @param  - string $sourceFileName - file name from where the variables
 *           are coming
 * @param  - string $languageName - name of the language translation
 */

function read_lang_info($languageId)
{
    
    global $problemMessage, $tbl_tr_lang_list;

    
    

$sql = "INSERT INTO ". $tbl_tr_lang_list ." SET
 
 languageName = \"". addslashes($languageName)."\",
 languagePath = \"". addslashes($languagePath)."\",
 claroVersion = \"". addslashes($clarolineVersion)."\",
 scanned = " . (int) $scanned . "";
    claro_sql_query($sql) or die($problemMessage);
 
 

    
}


?>