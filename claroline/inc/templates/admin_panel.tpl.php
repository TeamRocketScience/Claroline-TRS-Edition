<!-- $Id: admin_panel.tpl.php 13021 2011-03-31 09:37:59Z abourguignon $ -->

<?php echo claro_html_tool_title(get_lang('Administration')); ?>

<?php echo $this->dialogBox->render(); ?>

<ul class="adminPanel">
    <li>
        <h3><?php echo '<img src="' . get_icon_url('user') . '" alt="" />&nbsp;'.get_lang('Users'); ?></h3>
        <?php echo claro_html_list($this->menu['AdminUser'], array('class' => 'adminUser')); ?>
    </li>
    <li>
        <h3><?php echo '<img src="' . get_icon_url('course') . '" alt="" />&nbsp;'.get_lang('Courses'); ?></h3>
        <?php echo claro_html_list($this->menu['AdminCourse'], array('class' => 'adminCourse')); ?>
    </li>
    <li>
        <h3><?php echo '<img src="' . get_icon_url('settings') . '" alt="" />&nbsp;'.get_lang('Platform\' configuration'); ?></h3>
        <?php echo claro_html_list($this->menu['AdminPlatform'], array('class' => 'adminPlatform')); ?>
    </li>
    <li>
        <h3><?php echo '<img src="' . get_icon_url('exe') . '" alt="" />&nbsp;' . get_lang('Tools'); ?></h3>
        <?php echo claro_html_list($this->menu['AdminTechnical'], array('class' => 'adminTechnical')); ?>
    </li>
    <li>
        <h3><?php echo '<img src="' . get_icon_url('claroline') . '" alt="" />&nbsp;Claroline.net'; ?></h3>
        <?php echo claro_html_list($this->menu['AdminClaroline'], array('class' => 'adminClaroline')); ?>
    </li>
    <?php if (!empty($this->menu['ExtraTools'])) : ?>
    <li>
        <h3><?php echo '<img src="' . get_icon_url('exe') . '" alt="" />&nbsp;' . get_lang('Administration modules'); ?></h3>
        <?php echo claro_html_list($this->menu['ExtraTools'], array('class' => 'adminExtraTools')); ?>
    </li>
    <?php endif; ?>
    <li>
        <h3><?php echo '<img src="' . get_icon_url('mail_close') . '" alt="" />&nbsp;'.get_lang('Communication'); ?></h3>
        <?php echo claro_html_list($this->menu['Communication'], array('class' => 'adminCommunication')); ?>
    </li>
</ul>