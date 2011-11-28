<?php

class noutrace
{

  public $logDirectory;
  public $traceFormat;
  public $file;
  public $memoryAlarm = 0.3;
  public $timeAlarm = 0.03;
  public $customNamespace = 'Corretge\\';
  protected $defFN;

  public function __construct()
  {
    $this->logDirectory = ini_get('xdebug.trace_output_dir');
    $this->traceFormat = ini_get('xdebug.trace_format');
    ini_set('xdebug.auto_trace', 'Off');
  }

  public function rtvFiles()
  {
    $ret = '';
    $files = new DirectoryIterator($this->logDirectory);
    foreach ($files as $file)
    {

      if (substr_count($file->getFilename(), '.xt') == 0)
      {
        continue;
      }

      $date = explode('.', $file->getFilename());
      $date = date('Y-m-d H:i:s', $file->getCTime());


      $ret .= '<option value="' . $file->getFilename() . '"> ' . $date . ' - ' . $file->getFilename() . '- ' . number_format($file->getSize() / 1024,
                                                                                                                             0,
                                                                                                                             ',',
                                                                                                                             '.') . ' KB</option>';
    }

    return $ret;
  }

  public function aryComp($a, $b)
  {
    if ($a['cnt'] == $b['cnt'])
    {
      return 0;
    }
    /**
     * fem-ho desc
     */
    return ($a['cnt'] > $b['cnt']) ? -1 : 1;
  }

  public function usortByArrayKey(&$array, $key, $asc=SORT_ASC)
  {
    $sort_flags = array(SORT_ASC, SORT_DESC);
    if (!in_array($asc, $sort_flags))
      throw new InvalidArgumentException('sort flag only accepts SORT_ASC or SORT_DESC');
    $cmp = function(array $a, array $b) use ($key, $asc, $sort_flags)
      {
        if (!is_array($key))
        { //just one key and sort direction 
          if (!isset($a[$key]) || !isset($b[$key]))
          {
            throw new Exception('attempting to sort on non-existent keys');
          }
          if ($a[$key] == $b[$key])
            return 0;
          return ($asc == SORT_ASC xor $a[$key] < $b[$key]) ? 1 : -1;
        } else
        { //using multiple keys for sort and sub-sort 
          foreach ($key as $sub_key => $sub_asc)
          {
            //array can come as 'sort_key'=>SORT_ASC|SORT_DESC or just 'sort_key', so need to detect which 
            if (!in_array($sub_asc, $sort_flags))
            {
              $sub_key = $sub_asc;
              $sub_asc = $asc;
            }
            //just like above, except 'continue' in place of return 0 
            if (!isset($a[$sub_key]) || !isset($b[$sub_key]))
            {
              throw new Exception('attempting to sort on non-existent keys');
            }
            if ($a[$sub_key] == $b[$sub_key])
              continue;
            return ($sub_asc == SORT_ASC xor $a[$sub_key] < $b[$sub_key]) ? 1 : -1;
          }
          return 0;
        }
      };
    usort($array, $cmp);
  }

  /**
   * establim els paràmetres que ens arriben del formulari.
   */
  public function setParams()
  {
    $this->file = basename($_GET['file']);
    $this->memoryAlarm = (float) $_GET['memory'];
    $this->timeAlarm = (float) $_GET['time'];

    /**
     * mirem que sigui un arxiu vàlid
     */
    if (!file_exists($this->logDirectory . '/' . $this->file))
    {
      throw new Exception("Can't access to file " . $this->logDirectory . '/' . $this->file);
    }
  }

  /**
   * la mare dels ous, la traça
   * 
   * Sense que serveixi de precedents i per un tema de performance, aquest
   * mètode escriurà a stdoutput directament.
   */
  public function trace()
  {
    /**
     * recuperem la llista de funcions, les pròpies de PHP hi
     * seran sota ['internal']
     */
    $this->defFN = get_defined_functions();

    /**
     * counter
     */
    $jCnt = 0;

    /**
     * Sumary
     */
    $aSumary = array();
    $aSumaryS = array();


    /**
     * només acceptarem tipus de traça 1
     */
    if ($this->traceFormat != 1)
    {
      throw new Exception("xdebug.trace_format in /etc/php5/conf.d/xdebug.ini must be 1");
    }


    $aSteps = array();

    /**
     * Process all lines
     */
    $fh = fopen($this->logDirectory . '/' . $this->file, 'r');
    while ($jReadedLine = fgets($fh))
    {

      $jData = explode("\t", $jReadedLine);
      $jDataCnt = count($jData);
      
      /**
       * si es tracta de la capçalera de l'arxiu, la mostrem com a info
       */
      if ($jDataCnt == 1)
      {
        echo "<pre>$jReadedLine</pre>";
        continue;
        
      } 
      /**
       * si és el registre de finalització d'una instrucció, la processem
       */
      elseif ($jDataCnt == 5)
      {
//        list($jFLevel, $jFId, $jFPoint, $jFTime, $jFMemory) = $jData;
        

        /**
         * Si és el final de tot, no el comptarem, doncs no tenim cap id d'incic
         * el mostrarem directament
         */
        if ($jData[0] == '')
        {
          echo "<h3>TOTAL " . number_format(count($aSteps), 0) . 
            " function/method calls in ". number_format($jData[3],6) . " ms with " . 
            number_format(((int) $jData[4]) / 1024,3) . " KB's </h3>";
        }
        else
        {
          /**
           * li restem el temps i la memòria
           */
          $aSteps[$jData[1]][3] = number_format((float) $jData[3] - (float) $aSteps[$jData[1]][3], 6);
          $aSteps[$jData[1]][4] = number_format((float) $jData[4] - (float) $aSteps[$jData[1]][4], 0);
        }
        
      }
      /**
       * En qualsevol altre cas, és un registre d'inici d'instrucció
       */
      else
      {
//        list($jILevel, $jIId, $jIPoint, $jITime, $jIMemory, $jIFunction, 
//          $jIType, $jIFile, $jIFilename, $jILine, $jINumParms) = $jData;
        
        /**
         * iniciem un registre a l'array que ho te tot
         */
        $aSteps[$jData[1]] = $jData;
        
      }

      
    }
    
    /**
     * inicialitzem alguns camps
     */
    $prevLvl = 0;
    $TimeLastLvl = 0;
    $MemLastLvl = 0;
    $class = 'odd';
    
    /**
     * procedim a fer la sortida
     */
    foreach($aSteps as $step)
    {
      /**
       * si hi ha un canvi de nivell, en funció de si és 
       * més petit o més gran, 
       */
      if ($prevLvl < $step[0])
      {
        echo "<ul>";
      }
      elseif ($prevLvl > $step[0])
      {
        echo str_repeat("</ul>", $prevLvl - $step[0]);
      }
      
      
      $prevLvl = $step[0];
      
      echo "<li class=\"{$class}\">";
      
    
      if ($class == 'odd')
      {
        $class = 'even';
      }
      else
      {
        $class = 'odd';
      }

      
      
      echo '<span class="line">';
      echo $step[9];
      echo "</span>";

            echo '<span class="time">';
      echo $step[3];
      echo "</span>";
      
            echo '<span class="mem">';
      echo $step[4];
      echo "</span>";
      
      
      
//        list($jILevel, $jIId, $jIPoint, $jITime, $jIMemory, $jIFunction, 
//          $jIType, $jIFile, $jIFilename, $jILine, $jINumParms) = $jData;

      echo '<span class="func">';
      echo "<b>{$step[5]}</b><br/>";
      
      if ($step[10] > 0)
      {
        echo "<ul>";
        
        for ($jI=11; $jI <= 10 + $step[10]; $jI++)
        {
          echo "<li class=\"parm\">{$step[$jI]}</li>";
        }
        echo "</ul>";
        
      }
      elseif (!empty($step[8]))
      {
        echo "<ul><li class=\"parm\">{$step[8]}</li></ul>";
      }
      
      echo "<br/>";
      
      echo "<i class=\"pgm\">{$step[7]}</i>";
      
      
      echo '</span>';
      
      
      echo "</li>";
      
    }
    
  }
  
  public function debugMem($line, $method = null)
  {
    echo "<!-- line {$line} memory " . number_format(memory_get_usage(true),0);
    
    if (isset($method))
    {
      echo ' method ' . $method;
    }
    
    echo  " -->";
  }

}