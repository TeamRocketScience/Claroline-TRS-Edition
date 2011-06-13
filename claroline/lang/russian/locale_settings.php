<?php // $Id: locale_settings.php 12923 2011-03-03 14:23:57Z abourguignon $
/**
 * CLAROLINE
 * Russian Translation
 * @version 1.8 $Revision: 12923 $
 *
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package LANG-RU
 *
 * @author Claro team <cvs@claroline.net>
 */
$englishLangName = "Russian";
$localLangName = "Русский";

$iso639_1_code = "ru";
$iso639_2_code = "rus";

$langNameOfLang['arabic']        = "Арабский";
$langNameOfLang['brazilian']    = "Бразильский";
$langNameOfLang['croatian']    = "Греческий";
$langNameOfLang['catalan']    = "catalan";
$langNameOfLang['dutch']        = "Датский";
$langNameOfLang['english']    = "Английский";
$langNameOfLang['finnish']    = "Финский";
$langNameOfLang['french']        = "Французский";
$langNameOfLang['german']        = "Немецкий";
$langNameOfLang['greek']        = "Греческий";
$langNameOfLang['italian']    = "Итальянский";
$langNameOfLang['japanese']    = "Японский";
$langNameOfLang['polish']        = "Польский";
$langNameOfLang['simpl_chinese']="Простой китайский";
$langNameOfLang['spanish']    = "Испанский";
$langNameOfLang['swedish']    = "Шведский";
$langNameOfLang['thai']        = "Тайский";
$langNameOfLang['turkish']    = "Турецкий";

$charset = 'UTF8';
$text_dir = 'ltr';
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ' ';
$number_decimal_separator = ',';
$byteUnits = array('ВБКФ', 'лВ', 'нВ', 'зВ');

$langDay_of_weekNames['init'] = array('п', 'в', 'с', 'ч', 'п', 'с', 'в');
$langDay_of_weekNames['short'] = array('Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс');
$langDay_of_weekNames['long'] = array('Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');

$langMonthNames['init']  = array('я', 'ф', 'м', 'а', 'М', 'и', 'И', 'А', 'с', 'о', 'н', 'д');
$langMonthNames['short'] = array('Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек');
$langMonthNames['long'] = array('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 
'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%a %d %b %y";
$dateFormatLong  = '%A %d %B %Y';
$dateTimeFormatLong  = '%A %d %B %Y Б %H:%M';
$timeNoSecFormat = '%H:%M';
?>