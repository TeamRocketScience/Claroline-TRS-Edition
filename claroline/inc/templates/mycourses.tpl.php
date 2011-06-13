<!-- $Id: mycourses.tpl.php 12916 2011-03-03 10:43:35Z abourguignon $ -->

<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<?php
//Display activated courses list

if( !empty( $this->userCourseList ) ) :
    echo $this->userCourseList; // Comes from render_user_course_list();

elseif( empty( $this->userCourseListDesactivated ) ) :
    echo get_lang('You are not enrolled to any course on this platform or all your courses are deactivated');

else :
    echo get_lang( 'All your courses are deactivated (see list below)' );

endif;

//Display deactivated courses list
if ( !empty( $this->userCourseListDesactivated ) ) :
    echo claro_html_tool_title(get_lang('Deactivated course list'));
    echo $this->userCourseListDesactivated; // Comes from render_user_course_list_desactivated();
endif;
?>