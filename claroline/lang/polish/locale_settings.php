<?php // $Id: locale_settings.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @author: claro team <cvs@claroline.net>
 *
 * @package LANG-PL
*/
$englishLangName = "Polish";

$iso639_1_code = "pl";
$iso639_2_code = "pol";

$langNameOfLang['english']         = 'Angielski';
$langNameOfLang['arabic']          = 'Arabski';
$langNameOfLang['brazilian']       = 'Brazylijski';
$langNameOfLang['bulgarian']       = 'Bu³garski';
$langNameOfLang['zh_tw']           = 'Chiñski tradycyjny';
$langNameOfLang['simpl_chinese']   = 'Chiñski uproszczony';
$langNameOfLang['croatian']        = 'Chorwacki';
$langNameOfLang['czech']           = 'Czeski';
$langNameOfLang['czechSlovak']     = 'Czesko-s³owacki';
$langNameOfLang['danish']          = 'Duñski';
$langNameOfLang['esperanto']       = 'Esperanto';
$langNameOfLang['estonian']        = 'Estoñski';
$langNameOfLang['finnish']         = 'Fiñski';
$langNameOfLang['french']          = 'Francuski';
$langNameOfLang['french_corp']     = 'Francuski Korp.';
$langNameOfLang['galician']        = 'Galicyjski';
$langNameOfLang['greek']           = 'Grecki';
$langNameOfLang['georgian']        = 'Gruziñski';
$langNameOfLang['guarani']         = 'Guarani';
$langNameOfLang['spanish']         = 'Hiszpañski';
$langNameOfLang['spanish_latin']   = 'Hiszpañski (Amer.£aciñska)';
$langNameOfLang['dutch']           = 'Holenderski';
$langNameOfLang['indonesian']      = 'Indonezyjski';
$langNameOfLang['japanese']        = 'Japoñski';
$langNameOfLang['catalan']         = 'Kataloñski';
$langNameOfLang['lao']             = 'Laotañski';
$langNameOfLang['malay']           = 'Malajski';
$langNameOfLang['german']          = 'Niemiecki';
$langNameOfLang['armenian']        = 'Ormiañski';
$langNameOfLang['persian']         = 'Perski';
$langNameOfLang['polish']          = 'Polski';
$langNameOfLang['portuguese']      = 'Portugalski';
$langNameOfLang['russian']         = 'Rosyjski';
$langNameOfLang['romanian']        = 'Rumuñski';
$langNameOfLang['slovenian']       = 'S³oweñski';
$langNameOfLang['swedish']         = 'Szwedzki';
$langNameOfLang['thai']            = 'Tajski';
$langNameOfLang['turkish']         = 'Turecki';
$langNameOfLang['turkce']          = 'Turecki';
$langNameOfLang['ukrainian']       = 'Ukraiñski';
$langNameOfLang['vietnamese']      = 'Wietnamski';
$langNameOfLang['hungarian']       = 'Wêgierski';
$langNameOfLang['italian']         = 'W³oski';;

$charset = 'iso-8859-2';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ' ';
$number_decimal_separator = ',';
// shortcuts for Byte, Kilo, Mega, Giga, Tera, Peta, Exa
$byteUnits = array('bajtów', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');

$langDay_of_weekNames['init'] = array('N', 'P', 'W', '¦', 'C', 'Pt', 'S');
$langDay_of_weekNames['short'] = array('Nie', 'Pon', 'Wt', '¦r', 'Czw', 'Pt', 'Sob');
$langDay_of_weekNames['long'] = array('Niedziela', 'Poniedzia³ek', 'Wtorek', '¦roda', 'Czwartek', 'Pi±tek', 'Sobota');

$langMonthNames['init']  = array('S', 'L', 'M', 'K', 'M', 'C', 'L', 'S', 'W', 'P', 'L', 'G');
$langMonthNames['short'] = array('Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Pa¼', 'Lis', 'Gru');
$langMonthNames['long'] = array('Styczeñ', 'Luty', 'Marzec', 'Kwiecieñ', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpieñ', 'Wrzesieñ', 'Pa¼dziernik', 'Listopad', 'Grudzieñ');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%d %B %Y";
$dateFormatLong  = '%A, %d %B %Y';
$dateTimeFormatLong  = '%d %B %Y, %H:%M';
$timeNoSecFormat = '%H:%M';
$timespanfmt = '%s dni, %s godzin, %s minut i %s sekund';

?>