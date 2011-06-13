<!-- $Id: header.tpl.php 12676 2010-10-20 14:59:33Z abourguignon $ -->

<?php  if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $this->pageTitle; ?></title>
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Type" content="text/HTML; charset=<?php echo get_locale('charset');?>"  />
<?php echo link_to_css( get_conf('claro_stylesheet') . '/main.css', 'screen, projection, tv' );?>
<?php
if ( get_locale('text_dir') == 'rtl' ):
    echo link_to_css( get_conf('claro_stylesheet') . '/rtl.css', 'screen, projection, tv' );
endif;
?>
<?php echo link_to_css( 'print.css', 'print' );?>
<link rel="top" href="<?php get_path('url'); ?>/index.php" title="" />
<link href="http://www.claroline.net/documentation.htm" rel="Help" />
<link href="http://www.claroline.net/credits.htm" rel="Author" />
<link href="http://www.claroline.net" rel="Copyright" />
<?php if (file_exists(get_path('rootSys').'favicon.ico')): ?>
<link href="<?php echo rtrim( get_path('clarolineRepositoryWeb'), '/' ).'/../favicon.ico'; ?>" rel="shortcut icon" />
<?php endif; ?>
<script type="text/javascript">
    document.cookie="javascriptEnabled=true; path=<?php echo get_path('url');?>";
    <?php echo $this->warnSessionLost;?>
</script>
<?php echo $this->htmlScriptDefinedHeaders;?>
</head>