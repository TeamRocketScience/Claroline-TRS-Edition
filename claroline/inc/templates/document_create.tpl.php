<!-- $Id: document_create.tpl.php 12676 2010-10-20 14:59:33Z abourguignon $ -->

<html>
    <head>
    <meta http-equiv="Content-Type" content="text/HTML; charset='<?php echo get_locale('charset'); ?>'"  />
    <link rel="stylesheet" type="text/css" href="<?php echo get_path( 'rootWeb' ); ?>web/css/classic/main.css" media="screen, projection, tv" />
    <link rel="stylesheet" type="text/css" href="<?php echo get_path( 'rootWeb' ); ?>web/css/classic/rtl.css" media="screen, projection, tv" />
    <link rel="stylesheet" type="text/css" href="<?php echo get_path( 'rootWeb' ); ?>web/css/print.css" media="screen, projection, tv" />
    <script type="text/javascript" src="<?php echo get_path( 'rootWeb' ); ?>web/js/jquery.js"></script>
    <script type="text/javascript" src="<?php echo get_path( 'rootWeb' ); ?>web/js/claroline.js"></script>
    <script type="text/javascript" src="<?php echo get_path( 'rootWeb' ); ?>web/js/claroline.ui.js"></script>
    </head>
    <body>
    <?php echo $this->content; ?>
    </body>
</html>