<?php // $Id: forum_edittopic.tpl.php 12442 2010-06-15 08:10:56Z jrm_ $ ?>
<form action="<?php echo htmlspecialchars( $_SERVER['PHP_SELF'] )?>" method="post">
    <input type="hidden" name="claroFormId" value="<?php echo uniqid( '' ) ?>" />
    <input type="hidden" name="cmd" value="<?php echo $this->nextCommand ?>" />
    <input type="hidden" name="topic" value="<?php echo $this->topicId ?>" />
    <input type="hidden" name="forum" value="<?php echo $this->forumId ?>" />
    <label for="title"><strong><?php echo get_lang( 'New topic title' ) ?> : </strong></label><br />
    <input type="text" name="title" id="title" value="<?php echo $this->topicTitle ?>" /><br /><br />
    <input type="submit" value="<?php echo get_lang( 'Ok' ) ?>" />&nbsp; 
    <?php echo claro_html_button( htmlspecialchars( Url::Contextualize( $_SERVER['PHP_SELF'] ) ), get_lang( 'Cancel' ) )?>
</form>