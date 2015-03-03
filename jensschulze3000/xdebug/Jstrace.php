<?php
namespace jensschulze3000\Xdebug;

class Jstrace
{

    public $logDirectory;

    public $traceFormat;

    public $file;

    public $memoryAlarm = 0.3;

    public $timeAlarm = 0.03;

    public $onlyOneInstruction = '';

    public $onlyOneScript = '';

    public $customNamespace = 'Corretge\\';

    public $filesize;

    protected $defFN;


    public function __construct($logDirectory = '', $traceFormat = '')
    {
        if ('' == $logDirectory) {
            $this->logDirectory = ini_get('xdebug.trace_output_dir');
        } else {
            $this->logDirectory = $logDirectory;
        }

        if ('' == $traceFormat) {
            $this->traceFormat = ini_get('xdebug.trace_format');
        } else {
            $this->traceFormat = $traceFormat;
        }

        ini_set('xdebug.auto_trace', 'Off');
    }


    public function rtvFiles()
    {
        $ret    = '';
        $aFiles = array();
        $files  = new \DirectoryIterator($this->logDirectory);
        foreach ($files as $file) {
            if (substr_count($file->getFilename(), '.xt') == 0) {
                continue;
            }


            $date = explode('.', $file->getFilename());
            $date = date('Y-m-d H:i:s', $file->getCTime());

            if ($file->getFilename() == $this->file) {
                $jSel = ' selected="selected"';
            } else {
                $jSel = '';
            }

            $aFiles[$date . uniqid()] = '<option value="' . $file->getFilename() . '" ' . $jSel . '> ' . $date . ' - ' . str_replace('_',
                    '-', $file->getFilename()) . '-' . number_format($file->getSize() / 1024, 0, ',',
                    '.') . '-KB</option>';
        }

        ksort($aFiles);

        return implode("\n", $aFiles);
    }


    /**
     * herzustellen Parameter, die in der Form kommen.
     */
    public function setParams()
    {
        if (isset($_GET['file'])) {
            $this->file = basename($_GET['file']);

            /**
             * mirem que sigui un arxiu vàlid
             */
            if (!file_exists($this->logDirectory . '/' . $this->file)) {
                throw new \Exception("Can't access to file " . $this->logDirectory . '/' . $this->file);
            }

            $this->filesize = filesize($this->logDirectory . '/' . $this->file);
        }

        if (isset($_GET['onlyOneInstruction'])) {
            $this->onlyOneInstruction = ($_GET['onlyOneInstruction']);
        }
        if (isset($_GET['onlyOneScript'])) {
            $this->onlyOneScript = ($_GET['onlyOneScript']);
        }

        //$this->memoryAlarm = (float) $_GET['memory'];
        //$this->timeAlarm = (float) $_GET['time'];
    }


    /**
     * der Kern der Spur
     *
     * Ohne einen Präzedenzfall und ein Problem der Leistung, wird diese Methode auch direkt schreiben zu stdoutput.
     */
    public function trace()
    {
        /**
         * Abrufen der Liste der Funktionen, wird es speziell für unter PHP sein ["interne"]
         */
        //$this->defFN = get_defined_functions();

        /**
         * counter
         */
        $jCnt = 0;


        /**
         * Sumary
         */
        $aSumary  = array();
        $aSumaryS = array();


        /**
         * inicialitzem alguns camps
         */
        $prevLvl = 0;
        $prevTim = 0;
        $prevMem = 0;
        $class   = 'odd';

        /**
         * mirem si ens demanen iniLin
         */
        if (!isset($_GET['iniLin'])) {
            $_GET['iniLin'] = 0;
            $ctrlPrimeraLin = false;
        } else {
            $ctrlPrimeraLin = true;
        }
        $iniLin = (double) $_GET['iniLin'];
        $maxLin = $iniLin + 1024;

        /**
         * aussehen, wenn Sie eine bestimmte Erklärung verlangen, dann gibt es keine Begrenzung
         */
        // true, wenn mindestens onlyOneInstruction oder onlyOneScript
        $controlDeLinies = (!empty($this->onlyOneInstruction) or !empty($this->onlyOneScript));
        // true, wenn onlyOneInstruction
        $controlInstruction = !empty($this->onlyOneInstruction);
        // true, wenn onlyOneScript
        $controlScript = !empty($this->onlyOneScript);


        /**
         * nur ein Typ von Aufdeck akzeptieren
         */
        if ($this->traceFormat != 1) {
            throw new Exception("xdebug.trace_format in /etc/php5/conf.d/xdebug.ini must be 1");
        }

        $aSteps = array();

        /**
         * Process all lines
         */
        $fh   = fopen($this->logDirectory . '/' . $this->file, 'r');
        $nRow = 0;
        $eof  = true;

        while ($jReadedLine = fgets($fh)) {
            $nRow++;

            if (!$controlDeLinies and $nRow < $iniLin) {
                continue;
            }

            $jData    = explode("\t", $jReadedLine);
            $jDataCnt = count($jData);

            /**
             * wenn es der Header-Datei, wie in Eintrag gezeigt
             */
            if ($jDataCnt == 1) {
                //            if (true) {
                echo "<pre>$jReadedLine</pre>";
                //                echo '<pre>';
                //                print_r($jData);
                //                echo '</pre>';
                continue;
            } elseif ($jDataCnt == 5) {
                /**
                 * wenn der Abschluss einer Registrierungserklärung, der Prozess
                 */
                //        list($jFLevel, $jFId, $jFPoint, $jFTime, $jFMemory) = $jData;


                /**
                 * Wenn es das Ende von allem, werden wir nicht, weil wir keine id incic der Show direkt
                 */
                if ($jData[0] == '') {
                    echo "<h3>TOTAL " . number_format(count($aSteps),
                            0) . " function/method calls in " . number_format($jData[3],
                            6) . " ms with " . number_format(((int) $jData[4]) / 1024, 3) . " KB's </h3>";
                } else {

                    continue;

                    /**
                     * wir die Zeit und Speicher zu subtrahieren
                     */
                    $aSteps[$jData[1]][3] = number_format((float) $jData[3] - (float) $aSteps[$jData[1]][3], 6);
                    $aSteps[$jData[1]][4] = number_format((float) $jData[4] - (float) $aSteps[$jData[1]][4], 0);
                }
            } else {
                /**
                 * Ansonsten ist es ein Rekord-Startbefehl
                 */
                //        list($jILevel, $jIId, $jIPoint, $jITime, $jIMemory, $jIFunction,
                //          $jIType, $jIFile, $jIFilename, $jILine, $jINumParms) = $jData;

                if ($prevTim == 0) {
                    $prevTim = (float) $jData[3];
                    $prevMem = (float) $jData[4];

                    if ($iniLin == 0) {
                        continue;
                    }
                }


                /**
                 * gehen Sie zum Ausgang
                 */
                /**
                 * wenn es eine Pegeländerung, je nachdem, ob sie kleiner oder größer ist,
                 */
                if ($prevLvl < $jData[0]) {
                    if ($ctrlPrimeraLin) {
                        echo str_repeat("<ul>", $jData[0]);
                        $ctrlPrimeraLin = false;
                    } else {
                        echo "<ul>";
                    }
                } elseif ($prevLvl > $jData[0]) {
                    echo str_repeat("</ul>", $prevLvl - $jData[0]);
                }


                $prevLvl = $jData[0];


                /**
                 * drucken nur, wenn es auf die Anweisung sie angefordert haben, entspricht.
                 */
                if (!$controlDeLinies or ($controlInstruction and strpos($this->onlyOneInstruction,
                            $jData[5]) === 0) or ($controlScript and strpos($jData[8], $this->onlyOneScript) !== false)

                ) {
                    echo "<li title=\"{$nRow}\" class=\"{$class}\">";


                    /**
                     * @todo tun Sie es via CSS
                     */
                    if ($class == 'odd') {
                        $class = 'even';
                    } else {
                        $class = 'odd';
                    }


                    echo '<span class="line">';
                    echo "<a href='../../trace-code.php?file={$jData[8]}&line={$jData[9]}' target='trace-code'>$jData[9]</a>";
                    echo "</span>";

                    echo '<span class="time">';

                    //        echo "ini"  . number_format($prevTim, 6) . "<br />";
                    //        echo "end"  . number_format((float) $jData[3], 6) . "<br />";
                    $jSeconds = (float) $jData[3] - $prevTim;
                    echo number_format($jSeconds * 1000000, 0) . ' µs';

                    echo "</span>";

                    echo '<span class="mem">';
                    //        echo "ini"  . number_format($prevMem, 0) . "<br />";
                    //        echo "end"  . number_format((float) $jData[4], 0) . "<br />";
                    echo number_format((float) $jData[4] - $prevMem, 0);
                    echo "</span>";


                    //        list($jILevel, $jIId, $jIPoint, $jITime, $jIMemory, $jIFunction,
                    //          $jIType, $jIFile, $jIFilename, $jILine, $jINumParms) = $jData;

                    echo '<span class="func">';
                    echo "<b>{$jData[5]}</b></span>";

                    if ($jData[10] > 0) {
                        echo "<ul>";

                        for ($jI = 11; $jI <= 10 + $jData[10]; $jI++) {
                            echo "<li class=\"parm\">{$jData[$jI]}</li>";
                        }
                        echo "</ul>";
                    } elseif (!empty($jData[7])) {
                        echo "<ul><li class=\"parm\">{$jData[7]}</li></ul>";
                    }

                    echo "<br/>";

                    echo "<span class=\"pgm\"><i>{$jData[8]}</i>";


                    echo '</span>';


                    echo "</li>";

                    ob_flush();
                }

                /**
                 * Wenn Sie die maximale Anzahl der Zeilen übersteigen, lassen
                 */
                if (!$controlDeLinies and $nRow > $maxLin) {
                    $eof = false;
                    break;
                }

                $prevTim = (float) $jData[3];
                $prevMem = (float) $jData[4];

                $lastLine = $nRow;
            }
        }

        if (!$eof) {
            $_GET['iniLin'] = $lastLine + 1;
            echo "<br /><br><a href=\"{$_SERVER['SCRIPT_NAME']}?";
            foreach ($_GET as $parm => $val) {
                echo "{$parm}={$val}&";
            }
            echo "\">next 1024 lines</a>";
        }
    }


    public function debugMem($line, $method = null)
    {
        echo "<!-- line {$line} memory " . number_format(memory_get_usage(true), 0);

        if (isset($method)) {
            echo ' method ' . $method;
        }

        echo " -->";
    }


    /**
     * der Kern der Spur
     *
     * Ohne einen Präzedenzfall und ein Problem der Leistung, wird diese Methode auch direkt schreiben zu stdoutput.
     */
    public function traceNEW()
    {
        $retval = array();
        /**
         * Abrufen der Liste der Funktionen, wird es speziell für unter PHP sein ["interne"]
         */
        //$this->defFN = get_defined_functions();

        /**
         * counter
         */
        $jCnt = 0;


        /**
         * Sumary
         */
        $aSumary  = array();
        $aSumaryS = array();


        /**
         * inicialitzem alguns camps
         */
        $prevLvl = 0;
        $prevTim = 0;
        $prevMem = 0;
        $class   = 'odd';

        /**
         * mirem si ens demanen iniLin
         */
        if (!isset($_GET['iniLin'])) {
            $_GET['iniLin'] = 0;
            $ctrlPrimeraLin = false;
        } else {
            $ctrlPrimeraLin = true;
        }
        $iniLin = (double) $_GET['iniLin'];
        $maxLin = $iniLin + 1024;

        /**
         * aussehen, wenn Sie eine bestimmte Erklärung verlangen, dann gibt es keine Begrenzung
         */
        // true, wenn mindestens onlyOneInstruction oder onlyOneScript
        $controlDeLinies = (!empty($this->onlyOneInstruction) or !empty($this->onlyOneScript));
        // true, wenn onlyOneInstruction
        $controlInstruction = !empty($this->onlyOneInstruction);
        // true, wenn onlyOneScript
        $controlScript = !empty($this->onlyOneScript);


        /**
         * nur ein Typ von Aufdeck akzeptieren
         */
        if ($this->traceFormat != 1) {
            throw new \Exception("xdebug.trace_format in /etc/php5/conf.d/xdebug.ini must be 1");
        }


        /**
         * Process all lines
         */
        $fh   = fopen($this->logDirectory . '/' . $this->file, 'r');
        $nRow = 0;
        $eof  = true;

        while ($jLine = fgets($fh)) {
            $nRow++;

            if (!$controlDeLinies and $nRow < $iniLin) {
                continue;
            }

            $jData    = explode("\t", $jLine);
            $retval['rows'][] = $jData;
            /**
             * Wenn Sie die maximale Anzahl der Zeilen übersteigen, lassen
             */
            if (!$controlDeLinies and $nRow > $maxLin) {
                $eof = false;
                break;
            }

            $lastLine = $nRow;

        }

//        if (!$eof) {
//            $_GET['iniLin'] = $lastLine + 1;
//            echo "<br /><br><a href=\"{$_SERVER['SCRIPT_NAME']}?";
//            foreach ($_GET as $parm => $val) {
//                echo "{$parm}={$val}&";
//            }
//            echo "\">next 1024 lines</a>";
//        }
//        if (!$eof) {
//            $_GET['iniLin'] = $lastLine + 1;
//            echo "<br /><br><a href=\"{$_SERVER['SCRIPT_NAME']}?";
//            foreach ($_GET as $parm => $val) {
//                echo "{$parm}={$val}&";
//            }
//            echo "\">next 1024 lines</a>";
//        }
        return $retval;
    }

}
