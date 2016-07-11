<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="keyword" content="">

        <title><?=$this->e($title)?></title>

        <!-- Bootstrap core CSS -->
        <link href="assets/css/bootstrap.css" rel="stylesheet">
        <!--external css-->
        <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
        <link href="assets/lineicons/style.css" rel="stylesheet">    
        <link href="assets/js/jquery-ui/jquery-ui.css" rel="stylesheet">
    
        <!-- Custom styles for this template -->
        <link href="assets/css/style.css" rel="stylesheet">
        <link href="assets/css/style-responsive.css" rel="stylesheet">
        
        <link href="assets/css/c3.min.css" rel="stylesheet">
        <link href="assets/css/leaflet.css" rel="stylesheet" >
        
        <script src="assets/js/jquery-1.8.3.min.js"></script>
        <script src="assets/js/jquery-ui/jquery-ui.min.js"></script>
        <script src="assets/js/d3.min.js"></script>
        <script src="assets/js/c3.min.js"></script>
        
        <script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
        <script src="assets/js/build/heatmap.js"></script>
        <script src="assets/js/plugins/leaflet-heatmap.js"></script>
    </head>

    <body>

        <section id="container" >
            <!-- **********************************************************************************************************************************************************
            TOP BAR CONTENT & NOTIFICATIONS
            *********************************************************************************************************************************************************** -->
            <!--header start-->
            <header class="header black-bg">
                <div class="sidebar-toggle-box">
                    <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
                </div>
                <!--logo start-->
                <a href="index.php" class="logo"><b>Wildlife Reserves Singapore</b></a>
                <!--logo end-->
                
                <div class="top-menu">
                    <ul class="nav pull-right top-menu">
                        <!--<li><a class="logout" href="login.html">Logout</a></li>-->
                    </ul>
                </div>
            </header>
            <!--header end-->
      
            <!-- **********************************************************************************************************************************************************
            MAIN SIDEBAR MENU
            *********************************************************************************************************************************************************** -->
            <!--sidebar start-->
            <aside>
                <div id="sidebar"  class="nav-collapse ">
                    <!-- sidebar menu start-->
                    <ul class="sidebar-menu" id="nav-accordion">
              
                        <li>
                            <p class="centered"><img src="assets/img/logo-sgZoo.png" width="60"></p>
                        </li>

                        <li class="mt">
                        <li class="sub-menu">
                            <a class="active" href="#">
                                <i class="fa fa-dashboard"></i>
                                <span>Footfall Analytic</span>
                            </a>
                            <ul class="sub">
                                <li><a href="index.php">Chart</a></li>
                                <li><a href="index.php?page=heatmap">Heat Map</a></li>
                            </ul>
                        </li>
<!--
                        <li class="sub-menu">
                            <a href="javascript:;" >
                                <i class=" fa fa-bar-chart-o"></i>
                                <span>Social Media Analytic</span>
                            </a>
                        </li>
-->
                    </ul>
                    <!--sidebar menu end-->
                </div>
            </aside>
            <!--sidebar end-->
      
            <!-- **********************************************************************************************************************************************************
            MAIN CONTENT
            *********************************************************************************************************************************************************** -->
            <!--main content start-->
            <section id="main-content">
                <?=$this->section('content')?>
            </section>
            <!--main content end-->

            <!--footer start-->
            <footer class="site-footer">
                <div class="text-center">
                    <?php echo date("Y");?> - Visualizing Mandai
                    <a href="#" class="go-top">
                        <i class="fa fa-angle-up"></i>
                    </a>
                </div>
            </footer>
            <!--footer end-->
        </section>

        <!-- js placed at the end of the document so the pages load faster -->
        <script src="assets/js/bootstrap.min.js"></script>
        <script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
        <script src="assets/js/jquery.scrollTo.min.js"></script>
        <script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>
        <!--<script src="assets/js/jquery.sparkline.js"></script>-->
        
	<!--common script for all pages-->
        <script src="assets/js/common-scripts.js"></script>
    </body>
</html>