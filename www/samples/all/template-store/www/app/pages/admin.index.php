<!DOCTYPE html>
<html>
    <head>
        <title>SkinShop ADMIN</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='http://fonts.googleapis.com/css?family=Cuprum:400,400italic&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
        <link href="css/app.admin.css" rel="stylesheet" />
    </head>
    <body>
        
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.alerts.min.js"></script>
        <script src="js/jquery.simplemodal.min.js"></script>
        <script src="js/jquery.cookie.min.js"></script>
        <script src="js/jquery.mustache.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <script src="js/jquery.moment.min.js"></script>
        <script src="js/jquery.datatables.min.js"></script>
        <script src="js/jquery.uniform.min.js"></script>
        <script src="js/jquery.dropkick.min.js"></script>
        <script src="js/jquery.spinner.min.js"></script>
        <script src="js/jquery.autocomplete.min.js"></script>
        <script src="js/jquery.tipsy.min.js"></script>
        <script src="js/app.admin.js"></script>
        
        <script>
            $.Mustache.load('js/app.admin.tpl', function() {
                $('body').app(<?php echo $data; ?>);
            });
        </script>
        
    </body>
</html>