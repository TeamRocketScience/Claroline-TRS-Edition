<!-- $Id: banner.tpl.php 12921 2011-03-03 13:52:34Z abourguignon $ -->

<?php  if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<!-- claroPage -->
<div id="claroPage">

<!-- Banner -->
<div id="topBanner">

<!-- Platform Banner -->
<div id="platformBanner">
    <div id="campusBannerLeft">
        <span id="siteName">
        <?php $bannerSiteName =  get_conf('siteLogo') != ''
                ? '<img src="' . get_conf('siteLogo') . '" alt="'.get_conf('siteName').'" />'
                : get_conf('siteName');
        ?>
        <a href="<?php echo get_path( 'url' ); ?>/index.php" target="_top"><?php echo $bannerSiteName; ?></a>
        </span>
        <?php include_dock('campusBannerLeft'); ?>
    </div>
    <div id="campusBannerRight">
        <span id="institution">
        <?php $bannerInstitutionName =  get_conf('institutionLogo') != ''
                ? '<img src="' . get_conf('institutionLogo') . '" alt="'.get_conf('institution_name').'" >'
                : get_conf('institution_name');
            if ( get_conf( 'institution_url' ) ) :
        ?>
        <a href="<?php echo get_conf( 'institution_url' ); ?>" target="_top"><?php echo $bannerInstitutionName; ?></a>
        <?php else: ?>
        <?php echo $bannerInstitutionName; ?>
        <?php endif; ?>
        </span>
        <?php include_dock('campusBannerRight'); ?>
    </div>
    <div class="spacer"></div>
</div>
<!-- End of Platform Banner -->

<?php if ( $this->userBanner && property_exists($this, 'user') ): ?>
<!-- User Banner -->
<div id="userBanner">
    <div id="userBannerLeft">
        <span id="userName">
        <?php echo get_lang( '%firstName %lastName'
            , array(  '%firstName' => $this->user['firstName']
                    , '%lastName' => $this->user['lastName'] ) ) ?> :
        </span>
        <?php echo $this->userToolListLeft; ?>
        
        <?php include_dock('userBannerLeft'); ?>
    </div>
    <div id="userBannerRight">
        <?php echo $this->userToolListRight; ?>
        <?php include_dock('userBannerRight'); ?>
    </div>

    <div class="spacer"></div>
</div>
<!-- End of User Banner -->
<?php endif; ?>

<?php if ( $this->courseBanner ): ?>
<!-- Course Banner -->
<div id="courseBanner">
    <div id="courseBannerLeft">
        <div id="course">
            <h2 id="courseName">
            <?php echo link_to_course($this->course['name']
                , $this->course['sysCode'], array('target' => '_top')); ?>
            </h2>
            <span id="courseCode">
            <?php echo "{$this->course['officialCode']} - {$this->course['titular']}"; ?>
            </span>
        </div>
        <?php include_dock('courseBannerLeft'); ?>
    </div>
    <div id="courseBannerRight">
        <?php echo claro_is_course_allowed() ? $this->courseToolSelector : ''; ?>
        <?php include_dock('courseBannerRight'); ?>
    </div>

    <div class="spacer"></div>
</div>
<!-- End of Course Banner -->
<?php endif; ?>

<?php if ( $this->breadcrumbLine ): ?>
<!-- BreadcrumbLine  -->
<div id="breadcrumbLine">
<hr />
<div class="breadcrumbTrails">
<?php echo $this->breadcrumbs->render(); ?>
</div>
<div id="toolViewOption">
<?php echo $this->viewmode->render(); ?>
</div>
<div class="spacer"></div>
<hr />
</div>
<!-- End of BreadcrumbLine  -->
<?php endif; ?>

</div>
<!-- End of Banner -->