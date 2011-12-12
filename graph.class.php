<?php

require 'noutrace.class.php';

class graph
  extends noutrace
{

  protected $aScripts = array();
  protected $aFunc = array();
  protected $totTim = 0;
  protected $totMem = 0;
  protected $totInstructions = 0;
  protected $md5File;

  /**
   * establim els paràmetres que ens arriben del formulari.
   */
  public function setParams()
  {
    if (isset($_GET['file']))
    {
      $this->file = basename($_GET['file']);
      $this->md5File = md5($this->file);

      /**
       * mirem que sigui un arxiu vàlid
       */
      if (!file_exists($this->logDirectory . '/' . $this->file))
      {
        throw new Exception("Can't access to file " . $this->logDirectory . '/' . $this->file);
      }

      $this->filesize = filesize($this->logDirectory . '/' . $this->file);
    }
  }

  private function output()
  {
    echo "<h2>Total time {$this->totTim} seconds</h2>";
    echo "<h2>Max memory " . number_format($this->totMem, 0) . " bytes</h2>";
    echo "<h2>Total instructions " . number_format($this->totInstructions, 0) . "</h2>";
    echo "<img src=\"plot_centa.php?file={$this->md5File}\" />";

    $class = 'odd';

    //echo "<h2>Scripts with time > 1 milisecond or memory > 1MB</h2><ul>";
    echo "<h2>Scripts</h2><table id=\"tableScripts\">";
    echo "<tr>
      <th class=\"tdfunc\">Function</th>
      <th class=\"tdmem\">Mem KB</th>
      <th class=\"tdtime\">Time µs</th>
      <th class=\"tdnum\">Count</th>
      </tr>";
    foreach ($this->aScripts as $script => $value)
    {
      // if ($value['tim'] > 0.001 or $value['mem'] > 1000000)
      if (true)
      {
        if ($class == 'odd')
        {
          $class = 'even';
        }
        else
        {
          $class = 'odd';
        }

        if ($value['tim'] > $this->totTim / 20 or $value['mem'] > $this->totMem / 20)
        {
          $alarm = 'alarm';
        }
        else
        {
          $alarm = '';
        }


        $value['tim'] = number_format($value['tim'] * 1000000, 0, ',', '.');
        $value['mem'] = number_format($value['mem'] / 1024, 0, ',', '.');
        echo "<tr class=\"${class} {$alarm}\">";

        echo "<td class=\"tdfunc\">{$script}</td>";
        echo "<td class=\"tdmem\">{$value['mem']}</td>";
        echo "<td class=\"tdtime\">{$value['tim']}</td>";
        echo "<td class=\"tdnum\">{$value['num']}</td>";

        echo "</tr>";
      }
    }
    echo "</table>";


    echo "<h2>Operations</h2><table id=\"tableOperations\">";
    echo "<tr>
      <th class=\"tdfunc\">Function</th>
      <th class=\"tdmem\">Mem KB</th>
      <th class=\"tdtime\">Time µs</th>
      <th class=\"tdnum\">Count</th>
      </tr>";
    //echo "<h2>Operations with acumulated time > 1 milisecond or memory > 1MB</h2><ul>";
    foreach ($this->aFunc as $script => $value)
    {
      // if ($value['tim'] > 0.001 or $value['mem'] > 1000000)
      if (true)
      {
        if ($class == 'odd')
        {
          $class = 'even';
        }
        else
        {
          $class = 'odd';
        }
        if ($value['tim'] > $this->totTim / 20 or $value['mem'] > $this->totMem / 20)
        {
          $alarm = 'alarm';
        }
        else
        {
          $alarm = '';
        }
        $value['tim'] = number_format($value['tim'] * 1000000, 0, ',', '.');
        $value['mem'] = number_format($value['mem'] / 1024, 0, ',', '.');
        echo "<tr class=\"${class} {$alarm}\">";

        echo "<td class=\"tdfunc\">{$script}</td>";
        echo "<td class=\"tdmem\">{$value['mem']}</td>";
        echo "<td class=\"tdtime\">{$value['tim']}</td>";
        echo "<td class=\"tdnum\">{$value['num']}</td>";

        echo "</tr>";
      }
    }
    echo "</table>";
  }

  public function trace()
  {
    /**
     * Process all lines
     */
    $fh = fopen($this->logDirectory . '/' . $this->file, 'r');
    $nRow = 0;
    $prevTim = 0;
    $prevCenta = 0;
    $prevMem = 0;
    $lastMem = 0;

    $_SESSION['plot_centa'][$this->md5File] = array();

    while ($jReadedLine = fgets($fh))
    {
      $nRow++;


      $jData = explode("\t", $jReadedLine);
      $jDataCnt = count($jData);

      /**
       * només volem els d'entrada
       */
      if ($jDataCnt == 1)
      {
        echo "<pre>$jReadedLine</pre>";
        continue;
      }
      elseif ($jDataCnt < 10)
      {
        continue;
      }

      /**
       * Afegim un al comptador d'instruccions
       */
      $this->totInstructions++;

      /**
       * 
       * si hi ha canvi de centèssima, li passem l'acumulat de consum de memòria
       */
      $nouCenta = (int) ($jData[3] * 100);

      if ($nouCenta != $prevCenta)
      {
        $latMem = 0;

        for ($i = $prevCenta; $i < $nouCenta; $i += 1)
        {
          $_SESSION['plot_centa'][$this->md5File][] = number_format($jData[4] / (1024 * 1024),
                                                                    1);
        }

        $prevCenta = (int) ($jData[3] * 100);

        /**
         * si és el primer cop, inicialitzem
         */
        if ($prevTim == 0)
        {
          $prevTim = $jData[3];
          $prevMem = $jData[4];
        }
      }
      else
      {
        $lastMem = (int) ($jData[4] / (1024 * 1024));
      }

      /**
       * afegim els temps i la memòria al comptador d'scripts
       */
      if (isset($this->aScripts[$jData[8]]))
      {
        $this->aScripts[$jData[8]]['tim'] += ($jData[3] - $prevTim);
        $this->aScripts[$jData[8]]['mem'] += ($jData[4] - $prevMem);
        $this->aScripts[$jData[8]]['num']++;
      }
      else
      {
        $this->aScripts[$jData[8]] = array('tim' => ($jData[3] - $prevTim),
          'mem' => ($jData[4] - $prevMem),
          'num' => 1);
      }


      /**
       * afegim els temps i la memòria al comptador de funcions
       */
      if (isset($this->aFunc[$jData[5]]))
      {
        $this->aFunc[$jData[5]]['tim'] += ($jData[3] - $prevTim);
        $this->aFunc[$jData[5]]['mem'] += ($jData[4] - $prevMem);
        $this->aFunc[$jData[5]]['num']++;
      }
      else
      {
        $this->aFunc[$jData[5]] = array('tim' => ($jData[3] - $prevTim),
          'mem' => ($jData[4] - $prevMem),
          'num' => 1);
      }

      $this->totTim = $jData[3];
      if ($jData[4] > $this->totMem)
      {
        $this->totMem = $jData[4];
      }

      $prevTim = $jData[3];
      $prevMem = $jData[4];
    }
    $_SESSION['plot_centa'][$this->md5File][] = $lastMem;


    $this->output();
  }

}
