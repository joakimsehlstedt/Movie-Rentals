<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6 no-js" lang='<?= $lang ?>'> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7 no-js" lang='<?= $lang ?>'> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8 no-js" lang='<?= $lang ?>'> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html class='no-js' lang='<?= $lang ?>'> <!--<![endif]-->
    <head>
        <!-- Basic Page Needs
================================================== -->
        <meta charset="utf-8">
        <title><?= get_title($title) ?></title>
        <meta name="description" content="Coursework">
        <meta name="author" content="Joakim Sehlstedt"> 

        <!-- Mobile Specific Metas 
================================================= -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

        <!-- CSS
================================================== -->
        <?php foreach ($stylesheets as $val): ?>
            <link rel='stylesheet' type='text/css' href='<?= $val ?>'/>
        <?php endforeach; ?>

        <!--[if lt IE 9]>
                <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Favicons
================================================== -->
        <?php if (isset($favicon)): ?><link rel='shortcut icon' href='<?= $favicon ?>'/>
        <?php endif; ?>
        <script src='<?= $modernizr ?>'></script>
    </head>
    <body>
        
        <!-- Background slideshow 
================================================== -->
        <ul class="cb-slideshow">
            <li><span>Image 01</span></li>
            <li><span>Image 02</span></li>
            <li><span>Image 03</span></li>
            <li><span>Image 04</span></li>
            <li><span>Image 05</span></li>
            <li><span>Image 06</span></li>
        </ul>


        <!-- Primary Page Layout
================================================== -->
        <header class="container">
            <?= $header ?>
        </header>

        <nav class="container">
            <div id="navmain" class="sixteen columns">
                <?= CNavigation::GenerateMenu($menu, $pageID) ?>
            </div> 
        </nav>

    <main class="container content">
        <?= $main ?>
    </main>

    <footer class="container">
        <?= $footer ?>
    </footer>   

    <!-- JQuery and JavaScript inclusion (last in file for shorter pageloading)
================================================== -->
    <?php if (isset($jquery)): ?>
        <script src='<?= $jquery ?>'></script>
    <?php endif; ?>

    <?php if (isset($javascript_include)): foreach ($javascript_include as $val): ?>
            <script src='<?= $val ?>'></script>
            <?php
        endforeach;
    endif;
    ?>

    <!-- Google analytics
================================================== -->
    <?php if (isset($google_analytics)): ?>
        <script>
            var _gaq = [['_setAccount', '<?= $google_analytics ?>'], ['_trackPageview']];
            (function(d, t) {
                var g = d.createElement(t), s = d.getElementsByTagName(t)[0];
                g.src = ('https:' == location.protocol ? '//ssl' : '//www') + '.google-analytics.com/ga.js';
                s.parentNode.insertBefore(g, s)
            }(document, 'script'));
        </script>
    <?php endif; ?>

    <!-- End Document
================================================== -->
</body>
</html>

