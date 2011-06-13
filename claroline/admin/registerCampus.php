<?php // $Id: registerCampus.php 12941 2011-03-10 15:25:18Z abourguignon $

/**
 * CLAROLINE
 *
 * Gives the possibility to an administrator to register
 * his Claroline platform on claroline.net's worldwild list.
 *
 * @version     $Revision: 12941 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/index.php/ADMIN
 * @author Claro Team <cvs@claroline.net>
 * @author Sébastien Piraux <pir@claroline.net>
 */

$cidReset = true;
$gidReset = true;

require '../inc/claro_init_global.inc.php';

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

/*--------------------------------------------------------------------
               LIST OF COUNTRY ISO CODES AND COUNTRY NAMES
  --------------------------------------------------------------------*/
$isoCode = array();

$isoCode['Z1'] = "Other";
$isoCode['AF'] = "Afghanistan";
$isoCode['AL'] = "Albania";
$isoCode['DZ'] = "Algeria";
$isoCode['AS'] = "American Samoa";
$isoCode['AD'] = "Andorra";
$isoCode['AO'] = "Angola";
$isoCode['AI'] = "Anguilla";
$isoCode['AQ'] = "Antarctica";
$isoCode['AG'] = "Antigua and Barbuda";
$isoCode['AR'] = "Argentina";
$isoCode['AM'] = "Armenia";
$isoCode['AW'] = "Aruba";
$isoCode['AP'] = "Asia/Pacific Region";
$isoCode['AU'] = "Australia";
$isoCode['AT'] = "Austria";
$isoCode['AZ'] = "Azerbaijan";
$isoCode['BS'] = "Bahamas";
$isoCode['BH'] = "Bahrain";
$isoCode['BD'] = "Bangladesh";
$isoCode['BB'] = "Barbados";
$isoCode['BY'] = "Belarus";
$isoCode['BE'] = "Belgium";
$isoCode['BZ'] = "Belize";
$isoCode['BJ'] = "Benin";
$isoCode['BM'] = "Bermuda";
$isoCode['BT'] = "Bhutan";
$isoCode['BO'] = "Bolivia";
$isoCode['BA'] = "Bosnia and Herzegovina";
$isoCode['BW'] = "Botswana";
$isoCode['BV'] = "Bouvet Island";
$isoCode['BR'] = "Brazil";
$isoCode['IO'] = "British Indian Ocean Territory";
$isoCode['BN'] = "Brunei Darussalam";
$isoCode['BG'] = "Bulgaria";
$isoCode['BF'] = "Burkina Faso";
$isoCode['BI'] = "Burundi";
$isoCode['KH'] = "Cambodia";
$isoCode['CM'] = "Cameroon";
$isoCode['CA'] = "Canada";
$isoCode['CV'] = "Cape Verde";
$isoCode['KY'] = "Cayman Islands";
$isoCode['CF'] = "Central African Republic";
$isoCode['TD'] = "Chad";
$isoCode['CL'] = "Chile";
$isoCode['CN'] = "China";
$isoCode['CX'] = "Christmas Island";
$isoCode['CC'] = "Cocos (Keeling) Islands";
$isoCode['CO'] = "Colombia";
$isoCode['KM'] = "Comoros";
$isoCode['CG'] = "Congo";
$isoCode['CD'] = "Congo, The Democratic Republic of the";
$isoCode['CK'] = "Cook Islands";
$isoCode['CR'] = "Costa Rica";
$isoCode['CI'] = "Cote D'Ivoire";
$isoCode['HR'] = "Croatia";
$isoCode['CU'] = "Cuba";
$isoCode['CY'] = "Cyprus";
$isoCode['CZ'] = "Czech Republic";
$isoCode['DK'] = "Denmark";
$isoCode['DJ'] = "Djibouti";
$isoCode['DM'] = "Dominica";
$isoCode['DO'] = "Dominican Republic";
$isoCode['TL'] = "East Timor";
$isoCode['EC'] = "Ecuador";
$isoCode['EG'] = "Egypt";
$isoCode['SV'] = "El Salvador";
$isoCode['GQ'] = "Equatorial Guinea";
$isoCode['ER'] = "Eritrea";
$isoCode['EE'] = "Estonia";
$isoCode['ET'] = "Ethiopia";
$isoCode['EU'] = "Europe";
$isoCode['FK'] = "Falkland Islands (Malvinas)";
$isoCode['FO'] = "Faroe Islands";
$isoCode['FJ'] = "Fiji";
$isoCode['FI'] = "Finland";
$isoCode['FR'] = "France";
$isoCode['FX'] = "France, Metropolitan";
$isoCode['GF'] = "French Guiana";
$isoCode['PF'] = "French Polynesia";
$isoCode['TF'] = "French Southern Territories";
$isoCode['GA'] = "Gabon";
$isoCode['GM'] = "Gambia";
$isoCode['GE'] = "Georgia";
$isoCode['DE'] = "Germany";
$isoCode['GH'] = "Ghana";
$isoCode['GI'] = "Gibraltar";
$isoCode['GR'] = "Greece";
$isoCode['GL'] = "Greenland";
$isoCode['GD'] = "Grenada";
$isoCode['GP'] = "Guadeloupe";
$isoCode['GU'] = "Guam";
$isoCode['GT'] = "Guatemala";
$isoCode['GN'] = "Guinea";
$isoCode['GW'] = "Guinea-Bissau";
$isoCode['GY'] = "Guyana";
$isoCode['HT'] = "Haiti";
$isoCode['HM'] = "Heard Island and McDonald Islands";
$isoCode['VA'] = "Holy See (Vatican City State)";
$isoCode['HN'] = "Honduras";
$isoCode['HK'] = "Hong Kong";
$isoCode['HU'] = "Hungary";
$isoCode['IS'] = "Iceland";
$isoCode['IN'] = "India";
$isoCode['ID'] = "Indonesia";
$isoCode['IR'] = "Iran, Islamic Republic of";
$isoCode['IQ'] = "Iraq";
$isoCode['IE'] = "Ireland";
$isoCode['IL'] = "Israel";
$isoCode['IT'] = "Italy";
$isoCode['JM'] = "Jamaica";
$isoCode['JP'] = "Japan";
$isoCode['JO'] = "Jordan";
$isoCode['KZ'] = "Kazakhstan";
$isoCode['KE'] = "Kenya";
$isoCode['KI'] = "Kiribati";
$isoCode['KP'] = "Korea, Democratic People's Republic of";
$isoCode['KR'] = "Korea, Republic of";
$isoCode['KW'] = "Kuwait";
$isoCode['KG'] = "Kyrgyzstan";
$isoCode['LA'] = "Lao People's Democratic Republic";
$isoCode['LV'] = "Latvia";
$isoCode['LB'] = "Lebanon";
$isoCode['LS'] = "Lesotho";
$isoCode['LR'] = "Liberia";
$isoCode['LY'] = "Libyan Arab Jamahiriya";
$isoCode['LI'] = "Liechtenstein";
$isoCode['LT'] = "Lithuania";
$isoCode['LU'] = "Luxembourg";
$isoCode['MO'] = "Macau";
$isoCode['MK'] = "Macedonia";
$isoCode['MG'] = "Madagascar";
$isoCode['MW'] = "Malawi";
$isoCode['MY'] = "Malaysia";
$isoCode['MV'] = "Maldives";
$isoCode['ML'] = "Mali";
$isoCode['MT'] = "Malta";
$isoCode['MH'] = "Marshall Islands";
$isoCode['MQ'] = "Martinique";
$isoCode['MR'] = "Mauritania";
$isoCode['MU'] = "Mauritius";
$isoCode['YT'] = "Mayotte";
$isoCode['MX'] = "Mexico";
$isoCode['FM'] = "Micronesia, Federated States of";
$isoCode['MD'] = "Moldova, Republic of";
$isoCode['MC'] = "Monaco";
$isoCode['MN'] = "Mongolia";
$isoCode['MS'] = "Montserrat";
$isoCode['MA'] = "Morocco";
$isoCode['MZ'] = "Mozambique";
$isoCode['MM'] = "Myanmar";
$isoCode['NA'] = "Namibia";
$isoCode['NR'] = "Nauru";
$isoCode['NP'] = "Nepal";
$isoCode['NL'] = "Netherlands";
$isoCode['AN'] = "Netherlands Antilles";
$isoCode['NC'] = "New Caledonia";
$isoCode['NZ'] = "New Zealand";
$isoCode['NI'] = "Nicaragua";
$isoCode['NE'] = "Niger";
$isoCode['NG'] = "Nigeria";
$isoCode['NU'] = "Niue";
$isoCode['NF'] = "Norfolk Island";
$isoCode['MP'] = "Northern Mariana Islands";
$isoCode['NO'] = "Norway";
$isoCode['OM'] = "Oman";
$isoCode['PK'] = "Pakistan";
$isoCode['PW'] = "Palau";
$isoCode['PS'] = "Palestinian Territory";
$isoCode['PA'] = "Panama";
$isoCode['PG'] = "Papua New Guinea";
$isoCode['PY'] = "Paraguay";
$isoCode['PE'] = "Peru";
$isoCode['PH'] = "Philippines";
$isoCode['PN'] = "Pitcairn";
$isoCode['PL'] = "Poland";
$isoCode['PT'] = "Portugal";
$isoCode['PR'] = "Puerto Rico";
$isoCode['QA'] = "Qatar";
$isoCode['RE'] = "Reunion";
$isoCode['RO'] = "Romania";
$isoCode['RU'] = "Russian Federation";
$isoCode['RW'] = "Rwanda";
$isoCode['SH'] = "Saint Helena";
$isoCode['KN'] = "Saint Kitts and Nevis";
$isoCode['LC'] = "Saint Lucia";
$isoCode['PM'] = "Saint Pierre and Miquelon";
$isoCode['VC'] = "Saint Vincent and the Grenadines";
$isoCode['WS'] = "Samoa";
$isoCode['SM'] = "San Marino";
$isoCode['ST'] = "Sao Tome and Principe";
$isoCode['SA'] = "Saudi Arabia";
$isoCode['SN'] = "Senegal";
$isoCode['SC'] = "Seychelles";
$isoCode['SL'] = "Sierra Leone";
$isoCode['SG'] = "Singapore";
$isoCode['SK'] = "Slovakia";
$isoCode['SI'] = "Slovenia";
$isoCode['SB'] = "Solomon Islands";
$isoCode['SO'] = "Somalia";
$isoCode['ZA'] = "South Africa";
$isoCode['GS'] = "South Georgia and the South Sandwich Islands";
$isoCode['ES'] = "Spain";
$isoCode['LK'] = "Sri Lanka";
$isoCode['SD'] = "Sudan";
$isoCode['SR'] = "Suriname";
$isoCode['SJ'] = "Svalbard and Jan Mayen";
$isoCode['SZ'] = "Swaziland";
$isoCode['SE'] = "Sweden";
$isoCode['CH'] = "Switzerland";
$isoCode['SY'] = "Syrian Arab Republic";
$isoCode['TW'] = "Taiwan";
$isoCode['TJ'] = "Tajikistan";
$isoCode['TZ'] = "Tanzania, United Republic of";
$isoCode['TH'] = "Thailand";
$isoCode['TG'] = "Togo";
$isoCode['TK'] = "Tokelau";
$isoCode['TO'] = "Tonga";
$isoCode['TT'] = "Trinidad and Tobago";
$isoCode['TN'] = "Tunisia";
$isoCode['TR'] = "Turkey";
$isoCode['TM'] = "Turkmenistan";
$isoCode['TC'] = "Turks and Caicos Islands";
$isoCode['TV'] = "Tuvalu";
$isoCode['UG'] = "Uganda";
$isoCode['UA'] = "Ukraine";
$isoCode['AE'] = "United Arab Emirates";
$isoCode['UK'] = "United Kingdom";
$isoCode['US'] = "United States";
$isoCode['UM'] = "United States Minor Outlying Islands";
$isoCode['UY'] = "Uruguay";
$isoCode['UZ'] = "Uzbekistan";
$isoCode['VU'] = "Vanuatu";
$isoCode['VE'] = "Venezuela";
$isoCode['VN'] = "Vietnam";
$isoCode['VG'] = "Virgin Islands, British";
$isoCode['VI'] = "Virgin Islands, U.S.";
$isoCode['WF'] = "Wallis and Futuna";
$isoCode['EH'] = "Western Sahara";
$isoCode['YE'] = "Yemen";
$isoCode['YU'] = "Yugoslavia";
$isoCode['ZR'] = "Zaire";
$isoCode['ZM'] = "Zambia";
$isoCode['ZW'] = "Zimbabwe";

/*---------------------------------------------------------------------*/

if(file_exists( get_path('rootSys') . 'platform/currentVersion.inc.php')) include (get_path('rootSys') . 'platform/currentVersion.inc.php');

FromKernel::uses('thirdparty/nusoap/nusoap.lib');

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

// status codes
// keep in mind that these code must be the same than those in the
// soap server file that is on claroline.net
define("CAMPUS_ADDED", 1);
define("LOCAL_URL_ERROR", 2);
define("CAMPUS_ALREADY_IN_LIST", 3);
define("SQL_ERROR", 4);
define("COUNTRY_CODE_ERROR", 5);

/*============================================================================
                        INIT SOAP CLIENT
  ============================================================================*/
$soapclient = new nusoap_client('http://www.claroline.net/worldwide/worldwide_soap.php');

/*============================================================================
                        COMMANDS
  ============================================================================*/

$dialogBox = new DialogBox();

// -- register campus
if( isset($_REQUEST['register']) )
{
    $country = ( isset($_REQUEST['country']) ) ? $_REQUEST['country']: '' ;
    $parameters = array('campusName' => addslashes(get_conf('siteName'))
                      , 'campusUrl' => get_path('rootWeb')
                      , 'institutionName' => addslashes(get_conf('institution_name'))
                      , 'institutionUrl' => get_conf('institution_url')
                      , 'country' => $country
                      , 'adminEmail' => get_conf('administrator_email')
                        );

    // make the soap call to register the campus
    $soapResponse = $soapclient->call('registerCampus', $parameters);

    if( $soapResponse == CAMPUS_ADDED )
    {
        $dialogBox->success( get_lang('Your campus has been submitted and is waiting to be validate by Claroline.net team') );
    }
    elseif( $soapResponse == LOCAL_URL_ERROR )
    {
        $dialogBox->error( get_block('blockRegisterLocalUrl') );
    }
    elseif( $soapResponse == CAMPUS_ALREADY_IN_LIST )
    {
        $dialogBox->warning( get_lang('It seems that you already have registered your campus.') );
    }
    elseif( $soapResponse == COUNTRY_CODE_ERROR )
    {
        $dialogBox->error( get_lang('Country code seems to be incorrect.') );
    }
    else
    {
           // unknown soap error
        $dialogBox->error( get_lang('An error occurred while contacting Claroline.net') );
    }
}
else
{
    $parameters = array('campusUrl' => get_path('rootWeb'));
    $soapResponse = $soapclient->call('getCampusRegistrationStatus', $parameters);

    if( $soapResponse )
    {
        $dialogBoxContent = get_lang('Current registration status : ').'<br /><br />'."\n";

        switch($soapResponse)
        {
            case 'SUBMITTED' :
                $dialogBoxContent .= get_lang('<strong>Submitted</strong><p>Waiting for validation by Claroline.net team.</p>');
                break;
            case 'REGISTERED' :
                $dialogBoxContent .= get_lang('<strong>Approved</strong><p>Your campus registration has been approved by the Claroline.net team.</p>');
                break;
            case 'UNREGISTERED' :
                $dialogBoxContent .= get_lang('<strong>Removed</strong><p>Your campus has been removed from the worldwide page.</p>');
                break;
            case 'HIDDEN' :
                $dialogBoxContent .= get_lang('<strong>Deleted</strong><p>Your campus registration has been desactivated, contact us (see our website) if you think this is an error.</p>');
                break;
            default :
                // unknown status ?
                break;
        }
        
        $dialogBox->success( $dialogBoxContent );
        $alreadyRegistered = TRUE;
    }
    // else : there is no current status or an error occurred so don't show current status
}

/*============================================================================
                        DISPLAY
  ============================================================================*/
$nameTools = get_lang('Register my campus');
// bread crumb à ajouter

$out = '';

$title['mainTitle'] = $nameTools;
$title['subTitle'] = get_lang('Add my campus on Claroline.net website');
$out .= claro_html_tool_title($title);

$out .= $dialogBox->render();

if( !isset($_REQUEST['register']) && ! ( isset($alreadyRegistered) && $alreadyRegistered ) )
{
    $out .= '<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n"
        .'<ul>'."\n"
        .'<li>'.get_lang('Campus name').' : '.stripslashes( get_conf('siteName')) . '</li>'."\n"
        .'<li>'.get_lang('URL').' : <a href="' . get_path('rootWeb') . '">' . get_path('rootWeb') . '</a></li>'."\n"
        .'<li>'.get_lang('Institution').' : '.stripslashes(get_conf('institution_name')).'</li>'."\n"
        .'<li>'.get_lang('Institution URL') . ' : <a href="' . get_conf('institution_url') . '">' . get_conf('institution_url') . '</a></li>'."\n"
        .'<li>'.get_lang('Email').' : ' . get_conf('administrator_email') .'</li>'."\n"
        .'<li>'
        .'<label for="country">'.get_lang('Country').' : </label>'."\n"
        .'<select name="country" id="country">'."\n";

    $optionString = "";
    foreach( $isoCode as $code => $country)
    {
        $optionString .= '<option value="'.$code.'">'.$country.'</option>'."\n";
    }

    $out .= $optionString
        .'</select>'."\n"
        .'</li>'."\n"
        .'</ul>'."\n"
        .'<br />'."\n"
        .'<input type="submit" name="register" value="'.get_lang('Register my campus').'" />'."\n"
        .'<p>'
        .'<small>'.get_lang('Please check that your campus URL is reachable from the internet.').'</small>'
        .'</p>'."\n"
        .'</form>'."\n"
        ;
}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();