<?php 
require('includes/config.php');
require('includes/functions.php');
require('includes/language.php'); // I will regret this
require ('includes/head_include.php'); ?>
<title>Mini Admin</title>


<!-- scripts -->
<script type = "text/javascript" src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>


<!-- CSS -->
<link rel = "stylesheet" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">

</head>

<style type="text/css">

body { text-align: center; }

table { margin: 300px auto; }

#admin_nav { font-size: 18px; font-weight: bold; margin-top: 3em; }

#admin_nav .divider_pipe { margin: 14px; }

td.what_tr_is_for { font-size: 14px; font-weight: bold; }

</style>

<body>
<div id="mini_config">

</div>

</body>
<script>
var jsConfigs = {
    rpc: 'includes/rpc.php'
}

$(document).ready(function() {

    config_edit.get_config_tables();
    

});
</script>
</html>
