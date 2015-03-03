<?php
use jensschulze3000\Xdebug\Jstrace;
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Xdebug Trace File Parser<?php if (isset($_GET['file'])) {
            echo ' - ' . htmlentities($_GET['file']);
        } ?></title>
    <link rel="stylesheet" type="text/css" href="/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/css/xdebugTrace.css">
</head>
<body>

<?php
require_once(__DIR__ . '/vendor/autoload.php');
//require_once('./jstrace.class.php');
$xdb = new Jstrace(__DIR__ . '/traces', '1');
$xdb->setParams();

$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig   = new Twig_Environment(
    $loader, array(
        'cache'       => __DIR__ . '/templatecache',
        'auto_reload' => true
    )
);
?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Xdebug Trace File Parser</h1>
            <h2>Settings <?= $xdb->logDirectory ?> (<?= $xdb->traceFormat ?>)</h2>

            <form class="form-horizontal" method="get" action="jstrace.php">
                <div class="form-group">
                    <label for="file" class="control-label col-xs-3">File</label>

                    <div class="col-xs-9">
                        <select class="form-control" name="file" id="file">
                            <option value=""> -- Select --</option>
                            <?php echo $xdb->rtvFiles(); ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="onlyOneInstruction" class="control-label col-xs-3">Filter only one instruction</label>

                    <div class="col-xs-9">
                        <input type="text" class="form-control" name="onlyOneInstruction" id="onlyOneInstruction" value="<?= $xdb->onlyOneInstruction ?>" size="40"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="onlyOneScript" class="control-label col-xs-3">Filter by script file</label>

                    <div class="col-xs-9">
                        <input type="text" class="form-control" name="onlyOneScript" id="onlyOneScript" value="<?= $xdb->onlyOneScript ?>" size="40"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="memory" class="control-label col-xs-3">Memory</label>

                    <div class="col-xs-9">
                        <div class="input-group">
                            <span class="input-group-addon">If the memory jumps</span>
                            <input type="text" class="form-control" name="memory" id="memory" value="<?= ((isset($_GET['memory']) && is_numeric($_GET['memory']) ) ? $_GET['memory'] : $xdb->memoryAlarm) ?>" style="text-align:right" size="5"/>
                            <span class="input-group-addon">MB, provide an alert</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="time" class="control-label col-xs-3">Execution time</label>

                    <div class="col-xs-9">
                        <div class="input-group">
                            <span class="input-group-addon">If the execution time jumps</span>
                            <input type="text" class="form-control" name="time" id="time" value="<?= ((isset($_GET['time']) && is_numeric($_GET['time']) ) ? $_GET['time'] : $xdb->timeAlarm) ?>" style="text-align:right" size="5"/>
                            <span class="input-group-addon">seconds, provide an alert</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-offset-3 col-xs-9">
                        <button class="btn btn-primary" type="submit" value="parse">Parse</button>
                    </div>
                </div>

            </form>

            <?php
            if (empty($_GET['file'])) {
                exit;
            }

            echo "<h2>Output {$xdb->file}</h2>";
            echo number_format($xdb->filesize, 0) . " bytes <br />";
            echo $twig->render('trace.twig', array('data' => $xdb->traceNEW()));
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">

        </div>
    </div>
</div>
<script type="text/javascript" src="/js/jquery/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
