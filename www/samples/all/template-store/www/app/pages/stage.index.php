<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8" />
        <title>SkinShop STORE</title>
        <link rel="shortcut icon" href="img/favicon.ico" />
        <link rel="stylesheet" href="css/app.stage.css" />
    </head>
    <body>
        
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.mustache.min.js"></script>
        <script src="js/jquery.isotope.min.js"></script>
        <script src="js/jquery.arcticmodal.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <script src="js/app.stage.js"></script>
        
        <script>
            $.Mustache.load('js/app.stage.tpl', function() {
                $('body').app(<?php echo $data; ?>);
            });
        </script>
        
    </body>
</html>