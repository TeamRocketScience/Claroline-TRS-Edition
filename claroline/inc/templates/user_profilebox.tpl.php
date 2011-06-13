<!-- $Id: user_profilebox.tpl.php 13027 2011-03-31 16:40:32Z abourguignon $ -->

<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<div id="userProfileBox">
    <div class="header" id="userProfileTitle">
        <?php
        if ($this->condensedMode && $this->userData['user_id'] == claro_get_current_user_id()) :
            echo '<a href="'.get_path('clarolineRepositoryWeb').'desktop/index.php">'
               . $this->userFullName
               . '</a>';
        
        else :
            echo $this->userFullName;
        
        endif;
        ?>
    </div>
    <div id="userProfile">
        <?php
        if ( get_conf('allow_profile_picture') ) :
            echo '<div id="userPicture"><img class="userPicture" src="' . $this->pictureUrl . '" alt="' . get_lang('User picture') . '" /></div>';
        
        endif;
        ?>
        
        <div id="userDetails">
            <p>
                <span><?php echo get_lang('User'); ?></span><br />
                <?php echo $this->userFullName; ?>
            </p>
            <p>
                <span><?php echo get_lang('Email'); ?></span><br />
                <?php echo (!empty($this->userData['email']) ? htmlspecialchars($this->userData['email']) : '-' ); ?>
            </p>
            
            <?php
            if (!$this->condensedMode) :
            ?>
                <p>
                    <span><?php echo get_lang('Phone'); ?></span><br />
                    <?php echo (!empty($this->userData['phone']) ? htmlspecialchars($this->userData['phone']) : '-' ); ?>
                </p>
                <p>
                    <span><?php echo get_lang('Administrative code'); ?></span><br />
                    <?php echo (!empty($this->userData['officialCode']) ? htmlspecialchars($this->userData['officialCode']) : '-' ); ?>
                </p>
                <?php
                if (get_conf('is_trackingEnabled')) :
                ?>
                    <p>
                        <a class="claroCmd" href="<?php echo get_path('clarolineRepositoryWeb')
                        .'tracking/userReport.php?userId='.claro_get_current_user_id()
                        . claro_url_relay_context('&amp;'); ?>">
                        <img src="<?php echo get_icon_url('statistics'); ?>" alt="<?php echo get_lang('Statistics'); ?>" />
                        <?php echo get_lang('View my statistics'); ?>
                        </a>
                    </p>
                <?php
                endif;
            
            endif;
            ?>
            
            <p>
                <a class="claroCmd" href="<?php  echo get_path('clarolineRepositoryWeb'); ?>auth/profile.php">
                <img src="<?php echo get_icon_url('edit'); ?>" alt="<?php echo get_lang('Edit'); ?>" />
                <?php echo get_lang('Edit'); ?>
                </a>
            </p>
        </div>
    </div>
    <?php
    if (!$this->condensedMode) :
    ?>
        <div id="userProfileBoxDock"><?php echo $this->dock->render(); ?></div>
    <?php
    endif;
    ?>
</div>