<?php // $Id: fill_database.php 13021 2011-03-31 09:37:59Z abourguignon $

/**
 * CLAROLINE
 *
 * Insert dummy datas in database, for testing purpose.
 * Better to use it on a fresh database.
 *
 * @version     1.10 $Revision: 13021 $
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since       1.10
 */

// Load Claroline kernel
require_once dirname(__FILE__) . '/../../inc/claro_init_global.inc.php';

if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

// Get table name
$tbl_mdb_names              = claro_sql_get_main_tbl();
$tbl_user                   = $tbl_mdb_names['user'];
$tbl_category               = $tbl_mdb_names['category'];
$tbl_course                 = $tbl_mdb_names['course'];
$tbl_rel_course_category    = $tbl_mdb_names['rel_course_category'];


// Parameters
//================================================================
$generatedPrefix = 'Gen';   //Prefix the auto generated elements
$removeGenerated = 1;       //Remove previously generated elements
$nbUsers = 0;               //Nb of users to generate
$nbCategories = 0;          //Nb of categories to generate
$nbCourses = 0;             //Nb of courses to generate
//================================================================


$out = '';

$out .= get_lang(
    '<p>We\'re about to insert %nbUsers user(s), %nbCourses '
    . 'course(s), %nbCategories category(ies).</p>',
    array(
        '%nbUsers' => $nbUsers,
        '%nbCourses' => $nbCourses,
        '%nbCategories' => $nbCategories
    )
);

$nbRequestsPerformed = 0;
$inserted = array(
    'users' => array(),
    'courses' => array(),
    'categories' => array()
);

//Remove generated content
if ($removeGenerated)
{
    //Remove users
    $sql = "DELETE FROM `{$tbl_user}` "
         . "WHERE nom LIKE '{$generatedPrefix}User %'"
         . "AND prenom LIKE '{$generatedPrefix}User %'"
         . "AND username LIKE 'user%' "
         . "AND password LIKE 'user%'";
    
    Claroline::getDatabase()->exec($sql);
    
    //Remove courses
    $sql = "DELETE FROM `{$tbl_course}` "
         . "WHERE code LIKE '{$generatedPrefix}COURSE_%'"
         . "AND administrativeNumber LIKE '{$generatedPrefix}COURSE_%'";
    
    Claroline::getDatabase()->exec($sql);
    
    //Remove courses-categories links
    $sql = "SELECT id FROM `{$tbl_category}` "
         . "WHERE name LIKE '{$generatedPrefix}CATEGORY_%'"
         . "AND code LIKE '{$generatedPrefix}CAT_%'";
    
    $result = Claroline::getDatabase()->query($sql);
    while ($category = $result->fetch(Database_ResultSet::FETCH_ASSOC))
    {
        $sql = "DELETE FROM `{$tbl_rel_course_category}` "
             . "WHERE categoryId = {$category['id']}";
        
        Claroline::getDatabase()->exec($sql);
    }
    
    //Remove categories
    $sql = "DELETE FROM `{$tbl_category}` "
         . "WHERE name LIKE '{$generatedPrefix}CATEGORY_%'"
         . "AND code LIKE '{$generatedPrefix}CAT_%'";
    
    Claroline::getDatabase()->exec($sql);
}

//Insert users
for ($i=0; $i < $nbUsers; $i++)
{
    $sql = "INSERT INTO `{$tbl_user}` SET " . "\n"
         . "user_id = '', " . "\n"
         . "nom = '{$generatedPrefix}User {$i}', " . "\n"
         . "prenom = '{$generatedPrefix}User {$i}', " . "\n"
         . "username ='user{$i}', " . "\n"
         . "password = 'user{$i}', " . "\n"
         . "language = '', " . "\n"
         . "authSource = 'claroline', " . "\n"
         . "email = 'user{$i}@mail.com', " . "\n"
         . "officialCode = 'USR-{$i}', " . "\n"
         . "officialEmail = '', " . "\n"
         . "phoneNumber = '', " . "\n"
         . "pictureUri = '', " . "\n"
         . "creatorId = NULL, " . "\n"
         . "isPlatformAdmin = 0, " . "\n"
         . "isCourseCreator = 0";
    
    if (Claroline::getDatabase()->exec($sql))
    {
        $nbRequestsPerformed++;
        $inserted['users'][] = Claroline::getDatabase()->insertId();
    }
}

//Insert categories
for ($i=0; $i < $nbCategories; $i++)
{
    $sql = "INSERT INTO `{$tbl_category}` SET " . "\n"
         . "id = '', " . "\n"
         . "name = '{$generatedPrefix}CATEGORY_{$i}', " . "\n"
         . "code = '{$generatedPrefix}CAT{$i}', " . "\n"
         . "idParent = 0, " . "\n"
         . "rank = {$i}, " . "\n"
         . "visible = 1, " . "\n"
         . "canHaveCoursesChild = 1";

    if (Claroline::getDatabase()->exec($sql))
    {
        $nbRequestsPerformed++;
        $inserted['categories'][] = Claroline::getDatabase()->insertId();
    }
}

//Insert courses
for ($i=0; $i < $nbCourses; $i++)
{
    $sql = "INSERT INTO `{$tbl_course}` SET " . "\n"
         . "cours_id = '', " . "\n"
         . "code = '{$generatedPrefix}COURSE_{$i}', " . "\n"
         . "isSourceCourse = 0, " . "\n"
         . "sourceCourseId = NULL, " . "\n"
         . "administrativeNumber = '{$generatedPrefix}COURSE_{$i}', " . "\n"
         . "intitule = 'Course number {$i}', " . "\n"
         . "titulaires = 'Titular', " . "\n"
         . "email = 'titular_course_{$i}@mail.com', " . "\n"
         . "extLinkName = '', " . "\n"
         . "extLinkUrl = '', " . "\n"
         . "visibility = 'visible', " . "\n"
         . "access = 'public', " . "\n"
         . "registration = 'open', " . "\n"
         . "registrationKey = '', " . "\n"
         . "diskQuota = NULL, " . "\n"
         . "versionDb = '', " . "\n"
         . "versionClaro = '', " . "\n"
         . "lastVisit = NULL, " . "\n"
         . "lastEdit = NULL, " . "\n"
         . "creationDate = NOW(), " . "\n"
         . "expirationDate = NULL, " . "\n"
         . "defaultProfileId = 3, " . "\n"
         . "status = 'enable'";
    
    if (Claroline::getDatabase()->exec($sql))
    {
        $nbRequestsPerformed++;
        $courseId = Claroline::getDatabase()->insertId();
        $inserted['courses'][] = $courseId;
        
        //Link the course to a category
        $categoryId = $inserted['categories'][rand(0,$nbCategories-1)];
        $sql = "INSERT INTO `{$tbl_rel_course_category}` SET "
             . "courseId = {$courseId}, "
             . "categoryId = {$categoryId}, "
             . "rootCourse = 0";
        
        Claroline::getDatabase()->exec($sql);
    }
}


$out .= get_lang(
    '<p>Over the %nbRequests requests, %nbRequestsPerformed were performed.</p>',
    array(
        '%nbRequests' => ($nbUsers+$nbCourses+$nbCategories),
        '%nbRequestsPerformed' => $nbRequestsPerformed
    )
);


// Append output
$claroline->display->body->appendContent($out);

// Generate output
echo $claroline->display->render();