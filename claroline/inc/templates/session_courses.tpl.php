<!-- $Id$ -->

<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<?php
echo '<h3>' . get_lang('Session courses list') . '</h3>';
echo '<a class="claroCmd" href="' . htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb')
    . 'course/create.php', array('course_sourceCourseId'=>$this->courseId) )) . '">'
    . '<img src="' . get_icon_url('duplicate') . '" alt="" /> '
    . get_lang("Create a session course")
    . '</a>';

if (!empty($this->sessionCourses)) :
    
    echo '<ul>';
    
    foreach($this->sessionCourses as $course) :
        echo '<li><a href="'.htmlspecialchars(Url::Contextualize( get_path('clarolineRepositoryWeb')
            . 'course/index.php?cid=' . $course['sysCode'])) . '">'.$course['title'].'</a></li>';
    endforeach;
    
    echo '</ul>';
else :
    echo '<p>'.get_lang("No session course").'</p>';
endif;
?>