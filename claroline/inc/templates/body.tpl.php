<!-- $Id: body.tpl.php 12676 2010-10-20 14:59:33Z abourguignon $ -->

<?php  if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<?php if ( $this->claroBodyStart ): ?>
<!-- - - - - - - - - - - Claroline Body - - - - - - - - - -->
<div id="claroBody">
<?php endif;?>

<!-- Page content -->
<?php echo $this->content;?>
<!-- End of Page Content -->

<?php if ( $this->claroBodyEnd ): ?>
<div class="spacer"></div>
</div>
<!-- - - - - - - - - - - End of Claroline Body  - - - - - - - - - - -->
<?php endif;?>