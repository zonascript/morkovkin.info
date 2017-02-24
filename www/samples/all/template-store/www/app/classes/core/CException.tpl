<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo DEBUG ? 'Internal Server Error' : htmlspecialchars(get_class($e)); ?></title>
    
    <style type="text/css">
        /*<![CDATA[*/
        body {font-family: 'Verdana'; font-weight: normal; color: black; background-color: white;}
        h1 {font-family: 'Verdana'; font-weight: normal; font-size: 18pt; color: red;}
        h2 {font-family: 'Verdana'; font-weight: normal; font-size: 14pt; color: #800000;}
        h3 {font-family: 'Verdana'; font-weight: bold; font-size: 11pt}
        p {font-family: 'Verdana'; font-size: 9pt;}
        pre {font-family: 'Lucida Console'; font-size: 10pt;}
        .version {color: gray; font-size: 8pt; border-top: 1px solid #AAAAAA;}
        .source {margin-bottom: 1em; }
        .source .file {margin-bottom: 1em; font-weight: bold;}
        .error {background-color: #FFFFCC;}
        .message {color: #000; padding: 1em; font-size: 11pt; background: #f3f3f3; margin-bottom: 1em; line-height: 160%;}
        /*]]>*/
    </style>
</head>

<body>
    
    <?php if (DEBUG): ?>
    
    <h1><?php echo get_class($e); ?></h1>
    
    <div class="message">
        <?php echo nl2br(htmlspecialchars($e->getMessage())); ?>
    </div>
    
    <div class="source">
        <p class="file"><?php echo htmlspecialchars($e->getFile())."({$e->getLine()})"; ?></p>
        <pre><?php
            
            $file = @file($e->getFile());
            
            $showLines = 20;
            if (($startLine = $e->getLine() - 10) < 0)
                $startLine = 0;
            
            if (count($source = array_slice($file, $startLine, $showLines, true)) == 0)
                echo 'No source code available.';
            else
            {
                foreach ($source as $line => $code)
                {
                    if ($line + 1 !== $e->getLine())
                        echo htmlspecialchars(sprintf("%05d: %s", $line + 1, str_replace("\t", ' ', $code)));
                    else
                    {
                        echo "<div class=\"error\">";
                        echo htmlspecialchars(sprintf("%05d: %s", $line + 1, str_replace("\t", ' ', $code)));
                        echo "</div>";
                    }
                }
            }
        ?></pre>
    </div>
    
    <div class="callstack">
        <h2>Stack Trace</h2>
        <pre><?php echo htmlspecialchars($e->getTraceAsString()); ?></pre>
    </div>
    
    <div class="version">
        <?php echo date('Y-m-d H:i:s', time()) . ' ' . $_SERVER['SERVER_SOFTWARE']; ?>
    </div>
    
    <?php else: ?>
    
    <h1>Internal Server Error</h1>
    
    <div class="message">
        The above error occurred when the Web server was processing your request.<br />
        If you think this is a server error, please contact <a href="mailto: <?php echo $this->email; ?>"><?php echo $this->email; ?></a>.<br />
        Thank you.<br />
    </div>
    
    <div class="version">
        <?php echo date('Y-m-d H:i:s', time()); ?>
    </div>
    
    <?php endif; ?>
    

</body>
</html>