<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Xdebug Trace File Parser<?php if(isset($_GET['file'])) echo ' - ' . htmlentities ($_GET['file']); ?></title>
    <LINK href="trace.css" rel="stylesheet" type="text/css">
    <!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script> -->
  </head>
  <body>

    <?
    require 'noutrace.class.php';
    $xdb = new noutrace();
    $xdb->setParams();
    ?>

    <h1>Xdebug Trace File Parser</h1>
    <h2>Settings <?= $xdb->logDirectory ?> (<?= $xdb->traceFormat ?>)</h2>
    <form method="get" action="noutrace.php">
      <label>File
        <select name="file">
          <option value=""> -- Select -- </option>
          <?php echo $xdb->rtvFiles(); ?>
        </select>
      </label>
      <label>Filter only one instruction <input type="text" name="onlyOneInstruction" value="<?= $xdb->onlyOneInstruction ?>"  size="40"/> </label>
      <label>Filter by script file <input type="text" name="onlyOneScript" value="<?= $xdb->onlyOneScript ?>"  size="40"/> </label>
<!--      <label>If the memory jumps <input type="text" name="memory" value="<?= $xdb->memoryAlarm ?>" style="text-align:right" size="5"/> MB, provide an alert</label>
      <label>If the execution time jumps <input type="text" name="time" value="<?= $xdb->timeAlarm ?>" style="text-align:right" size="5"/> seconds, provide an alert</label>-->

      <input type="submit" value="parse" />

    </form>

    <?php
    if (empty($_GET['file']))
    {
      exit;
    }

    echo "<h2>Output {$xdb->file}</h2>";
    echo number_format($xdb->filesize, 0) . " bytes <br />";


    //$xdb->debugMem(__LINE__);
    $xdb->trace();
    //$xdb->debugMem(__LINE__);
    ?>


  </body>
</html>
