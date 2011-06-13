<?php // $Id: forum_editcat.tpl.php 12442 2010-06-15 08:10:56Z jrm_ $ 
?>
<strong><?php echo $this->header ?></strong>
<form action="<?php echo htmlspecialchars( $_SERVER['PHP_SELF'] )?>" method="post">
    <input type="hidden" name="claroFormId" value="<?php echo uniqid( '' ) ?>" />
    <input type="hidden" name="cmd" value="<?php echo $this->nextCommand ?>" />
    <input type="hidden" name="catId" value="<?php echo $this->catId ?>" />
    <label for="catName"><?php echo get_lang( 'Name' ) ?> : </label><br />
    <input type="text" name="catName" id="catName" value="<?php echo $this->catName ?>" /><br /><br />
    <input type="submit" value="<?php echo get_lang( 'Ok' ) ?>" />&nbsp; 
    <?php echo claro_html_button( htmlspecialchars( Url::Contextualize( $_SERVER['PHP_SELF'] ) ), get_lang( 'Cancel' ) )?>
</form>
