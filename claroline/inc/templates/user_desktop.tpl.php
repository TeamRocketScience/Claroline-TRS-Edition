<!-- $Id: user_desktop.tpl.php 12924 2011-03-03 14:45:00Z abourguignon $ -->

<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<?php echo claro_html_tool_title(get_lang(get_lang('My desktop'))); ?>

<?php echo $this->dialogBox->render(); ?>

<div id="rightSidebar">
    <?php echo $this->userProfileBox->render(); ?>
    
    <?php include_textzone('textzone_right.inc.html'); ?>
</div>

<div id="leftContent">
    <div class="claroBlock portlet collapsible collapsed">
        <div class="claroBlockHeader">
            <?php echo get_lang('Presentation'); ?>
            <span class="separator">|</span>
            <a href="#" class="doCollapse"><?php echo get_lang('View all'); ?></a>
        </div>
        <div class="claroBlockContent collapsible-wrapper">
            <?php include_textzone('textzone_top.authenticated.inc.html'); ?>
        </div>
    </div>
    
    <?php echo $this->outPortlet; ?>
</div>