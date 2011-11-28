<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

/**
 * include_path crtphpfwk
 * @link http://code.google.com
 */
require_once 'crtml/crtml.www.class.php';

/**
 * and forms
 */
require_once 'crtml/crtml.sform.php';

class www extends crtmlWWW
{
    /**
     * Construct a web object
     */
    public function __construct()
    {
        
        /**
         * standard construction
         */
        parent::__construct();
        
        /**
         * Create basics elements
         */
        $this->head = new crtmlHEAD();
        $this->body = new crtmlBODY();
        
        
        /**
         * Draw title application
         */
        $this->init('Xdebug Trace File Parser');
        $this->drawForm();
        $this->drawParse();
    
        /**
         * output its
         */
        echo $this;
    }
    
    
    /**
     * General Pourpose initialitation
     * 
     * @param style $titol title 
     */
    protected function init($titol)
    {
        
        /**
         * sets UTF8 as charset
         */
        $this->setCharSet('utf-8');
        
        /**
         * add CSS
         */
        $css = new crtmlLINK('trace.css');
        $css->set_type('text/css');
        $css->set_rel('stylesheet');
        $this->head->addContingut($css);
        
        
        $title = new crtmlTITLE($titol);
        $this->head->addContingut($title);
        
        
        $this->body->addContingut("<h1>{$titol}</h1>");
        
    }
    
    
    protected function drawForm()
    {
        $form = new sFormTable('Settings', 'xtrace');
        
        $form->setMethod('GET');
        
        $form->
        
        
        $this->body->addContingut($form);
          
        
    }
    
    
    protected function drawParse()
    {}

    /**
     * Renderize the web object
     * 
     * @return string
     */
    public function __toString()
    {
        $this->header();
        return parent::__toString();
    }
    
    
}



/**
 * Instantiate the web object and print it.
 */
new www();


?>