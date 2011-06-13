<!-- $Id: platform_index.tpl.php 13030 2011-04-01 11:26:41Z abourguignon $ -->

<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<div id="rightSidebar">
    
    <?php if ( claro_is_user_authenticated() ) : ?>
        <?php echo $this->userProfileBox->render(); ?>
        
    <?php else : ?>
        <?php if (!empty($this->languages) && count($this->languages) > 1) : ?>
        
        <div id="languageBox">
            <div class="header"><?php echo get_lang('Language'); ?></div>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" name="language_selector" method="post">
            <fieldset style="border: 0; margin: 10px 0 15px 0; padding: 5px;">
                <select onchange="top.location=this.options[selectedIndex].value" id="langSelector" name="language">
                <?php foreach ($this->languages as $key => $elmt) : ?>
                    <option value="<?php echo $_SERVER['PHP_SELF']; ?>/index.php?language=<?php echo $elmt; ?>"<?php if (!empty($this->currentLanguage) && $elmt == $this->currentLanguage) : ?> selected="selected"<?php endif; ?>>
                        <?php echo $key; ?>
                    </option>
                <?php endforeach; ?>
                </select>
            <noscript><input type="submit" value="<?php echo get_lang('Ok'); ?>" /></noscript>
            </fieldset>
            </form>
        </div>
        <?php endif; ?>
        
        <?php include_template('loginzone.tpl.php'); ?>
    <?php endif; ?>
    
    <?php include_dock('campusHomePageRightMenu'); ?>
    
    <?php include_textzone('textzone_right.inc.html'); ?>
    
</div>

<div id="leftContent">
    
    <?php
    // Home page presentation texts
    include_textzone( 'textzone_top.inc.html', '<div style="text-align: center">
    <img src="'.get_icon_url('logo').'" border="0" alt="Claroline logo" />
    <p><strong>Claroline Open Source e-Learning</strong></p>
    </div>' );
    
    include_dock('campusHomePageTop');
    
    if( claro_is_user_authenticated() ) :
        include_textzone('textzone_top.authenticated.inc.html');
    else :
        include_textzone('textzone_top.anonymous.inc.html');
    endif;
    
    
    if (claro_is_user_authenticated()) :
        ?>
        
        <table class="homepageTable">
          <tr>
          
            <td class="userCommands">
                <?php echo claro_html_tool_title(get_lang('Manage my courses')); ?>
                <?php echo claro_html_list( $this->userCommands ); ?>
            </td>
            <td class="myCourseList">
                <?php echo claro_html_tool_title(get_lang('My course list')); ?>
                <?php echo $this->templateMyCourses->render(); ?>
            </td>
          
          </tr>
        </table>
        
        <?php
    
    else :
        if (!get_conf('course_categories_hidden_to_anonymous',false)) :
            echo $this->templateCategoryBrowser->render();
        endif;
    endif;
    
    include_dock('campusHomePageBottom');
    ?>
    
</div>