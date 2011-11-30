<?php

@session_start();

require_once ('jpgraph/src/jpgraph.php');
require_once ('jpgraph/src/jpgraph_line.php');

if (isset($_SESSION['plot_centa'][$_GET['file']]))
{
	$datay = $_SESSION['plot_centa'][$_GET['file']];
}
else
{
	$datay = array(0);
}

// Setup the graph
$graph = new Graph(960,480);
//$graph->SetScale("intlin",0,$aYMax=50);
$graph->SetScale("intlin");
$theme_class=new UniversalTheme;
$graph->SetTheme($theme_class);

$graph->SetBox(false);

$graph->title->Set('Memory consumition MB per centesims');
$graph->ygrid->Show(true);
$graph->xgrid->Show(false);
$graph->yaxis->HideZeroLabel();
$graph->ygrid->SetFill(true,'#FFFFFF@0.5','#FFFFFF@0.5');
$graph->SetBackgroundGradient('blue', '#55eeff', GRAD_HOR, BGRAD_PLOT);
//$graph->xaxis->SetTickLabels(array('A','B','C','D','E','F','G'));

// Create the line
$p1 = new LinePlot($datay);
$graph->Add($p1);

$p1->SetFillGradient('red',  'yellow');
$p1->SetStepStyle();
$p1->SetColor('#808000');

// Output line
$graph->Stroke();

?>