<!-- $Id: course_index.tpl.php 12901 2011-02-23 16:21:12Z abourguignon $ -->

<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<table class="courseTable">
<tr>
<td class="toolList">
    <?php
    if (is_array($this->toolLinkListSource)
        && !empty($this->toolLinkListSource)
        && is_array($this->toolLinkListSession)
        && !empty($this->toolLinkListSession) )
    {
        echo '<div class="sourceToolPanel"><h3>' . get_lang('Course') . '</h3>';
        echo claro_html_list($this->toolLinkListSource, array('id'=>'commonToolListSource'));
        echo '</div>';
        echo '<div class="sessionToolPanel"><h3>' . get_lang('Session') . '</h3>';
        echo claro_html_list($this->toolLinkListSession, array('id'=>'commonToolListSession'));
        echo '</div>';
    }
    
    if (is_array($this->toolLinkListStandAlone))
    {
        echo claro_html_list($this->toolLinkListStandAlone, array('id'=>'commonToolListStandAlone'));
    }
    ?>
    
    <br />
    
    <?php
    if ( claro_is_allowed_to_edit() ) :
        echo claro_html_list($this->courseManageToolLinkList,  array('id'=>'courseManageToolList'));
    endif;
    ?>
    
    <?php if ( claro_is_user_authenticated() ) : ?>
    <br />
    <span style="font-size:8pt;">
    
    <?php
        echo '<img class="iconDefinitionList" src="' . get_icon_url( 'hot' ) . '" alt="New items" />'
            . get_lang('New items'). ' ('
            . '<a href="' . get_path('clarolineRepositoryWeb') . 'notification_date.php' . '" >'
            . get_lang('to another date') . '</a>';

        if ($_SESSION['last_action'] != '1970-01-01 00:00:00')
        {
           $last_action =  $_SESSION['last_action'];
        }
        else
        {
            $last_action = date('Y-m-d H:i:s');
        }
        
        $nbChar = strlen($last_action);
        if (substr($last_action,$nbChar - 8) == '00:00:00' )
        {
            echo ' [' . claro_html_localised_date( get_locale('dateFormatNumeric'),
                strtotime($last_action)) . ']';
        }
        
        echo ')' ;
    ?>
    </span>
    
    <?php endif; ?>
</td>

<td class="coursePortletList">
    <?php
        echo $this->dialogBox->render();
    ?>
    
    <?php
        if ( claro_is_allowed_to_edit() ) :
            echo '<div class="claroBlock">'."\n"
               . '<a href="'
               . htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF']
               . '?portletCmd=rqAdd')).'">'
               . '<img src="'.get_icon_url('default_new').'" alt="'.get_lang('Add a new portlet').'" /> '
               . get_lang('Add a portlet to your course homepage').'</a>'."\n"
               . '</div>';
        endif;
        
        if ($this->portletIterator->count() > 0)
        {
            foreach ($this->portletIterator as $portlet)
            {
                if ($portlet->getVisible() || !$portlet->getVisible() && claro_is_allowed_to_edit())
                {
                    echo $portlet->render();
                }
            }
        }
        elseif ($this->portletIterator->count() == 0 && claro_is_allowed_to_edit())
        {
            echo get_block('blockIntroCourse');
        }
    ?>
</td>
</tr>
</table>