<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Xdebug Trace File Statistics</title>
    <LINK href="trace.css" rel="stylesheet" type="text/css">
    <!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script> -->
  </head>
  <body>

    <?
		@session_start();
    require 'graph.class.php';
    $xdb = new graph();
    ?>

    <h1>Xdebug Trace File Statistics</h1>
    <h2>Settings <?= $xdb->logDirectory ?> (<?= $xdb->traceFormat ?>)</h2>
    <form method="get" action="graph.php">
      <label>File
        <select name="file">
          <option value="" selected="selected"> -- Select -- </option>
          <?php echo $xdb->rtvFiles(); ?>
        </select>
      </label>

      <input type="submit" value="parse" />

    </form>

    <?php
    if (empty($_GET['file']))
    {
      exit;
    }
    else
    {
      $xdb->debugMem(__LINE__);
      $xdb->setParams();
    }
    
    echo "<h2>Statistics {$xdb->file}</h2>";
    echo number_format($xdb->filesize, 0) . " bytes <br />";
    
    
    $xdb->trace();
    
    ?>
    
    
  </body>
</html>