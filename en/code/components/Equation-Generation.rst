

Equation Generation
===================

by %s on December 29, 2008

A component to convert latex mathematical equations to PNG. The
component provides a subset of cleaned-up, cake-ised code from the
LatexRender project.
Example Usage:

Controller Class:
`````````````````

::

    <?php 
    <?php
    $formula = 'a+b';
    $outputfile = 'equation01.png';
    $this->Equation->render($formula, $outputfile);
    ?>
    ?>


::

    
    <?php
    /**
     * Constants
     */
    define('CAKE_COMPONENT_EQUATION_ERROR_BLACKLIST', 1);
    define('CAKE_COMPONENT_EQUATION_ERROR_TMPFOLDER', 2);
    define('CAKE_COMPONENT_EQUATION_ERROR_WRITETEX',  3);
    define('CAKE_COMPONENT_EQUATION_ERROR_LATEX',     4);
    define('CAKE_COMPONENT_EQUATION_ERROR_DVIPS',     5);
    define('CAKE_COMPONENT_EQUATION_ERROR_CONVERT',   6);
    define('CAKE_COMPONENT_EQUATION_ERROR_WRITEPNG',  7);
     
    /**
     * Equation rendering component
     */
    class EquationComponent extends Object
    {
        var $latexPath    = '/usr/bin/latex';
        var $dvipsPath    = '/usr/bin/dvips';
        var $convertPath  = '/usr/bin/convert';
        var $tmpPath      = null;
        var $density      = 120;
    	var $fontSize     = 10;
    	var $latexClass   = 'article';
        var $errors = array(
            1 => 'Formula contained a blacklisted string',
            2 => 'Unable to write to the temp folder',
            3 => 'Unable to write the latex document',
            4 => 'Latex conversion failed',
            5 => 'Dvips conversion failed',
            6 => 'Convert conversion failed',
            7 => 'Unable to write to the output file');
    
        /**
         * Renders a LaTeX formula by the using the following method:
         *  - write the formula into a wrapped tex-file in a temporary directory
         *    and change to it
         *  - Create a DVI file using latex (tetex)
         *  - Convert DVI file to Postscript (PS) using dvips (tetex)
         *  - convert, trim and add transparancy by using 'convert' from the
         *    imagemagick package.
         *
         * @param string LaTeX formula
         * @returns true if the picture has been successfully saved to the picture
         *          cache directory
         */
        function render($formula, $outputfile)
        {
            // Validate formula
            if (!$this->validate($formula)) {
                $this->error = CAKE_COMPONENT_EQUATION_ERROR_BLACKLIST;
                return false;
            }
            
            // Create a temporary working directory
            if ($this->tmpPath === null) {
                $this->tmpPath = TMP;
            }
            if (!is_dir($this->tmpPath) || !is_writeable($this->tmpPath)) {
                return CAKE_COMPONENT_EQUATION_ERROR_TMPFOLDER;
            }
            chdir($this->tmpPath);
    
            // Make a mini latex document to render
            $latex = $this->wrap($formula);
           
            // Write the latex document to our tmp dir
            $tmpFilePrefix = $this->tmpPath . 'equation_' . md5($formula);
            $texDocumentPath = $tmpFilePrefix . '.tex';
            if (!file_put_contents($texDocumentPath, $latex)) {
                return CAKE_COMPONENT_EQUATION_ERROR_WRITETEX;
            }
            
            // Create the DVI file (latex)
            $cmd = sprintf('%s --interaction=nonstopmode %s',
                $this->latexPath,
                $texDocumentPath);
            exec($cmd, $output, $status);
            //debug(array($cmd, $output, $status));
            if ($status !== 0) { // zero is success, nfi about 1
                $this->clean($tmpFilePrefix);
                return CAKE_COMPONENT_EQUATION_ERROR_LATEX;
            }
    
            // Convert DVI file to postscript (dvips)
            $cmd = sprintf('%s -E %s -o %s',
                $this->dvipsPath,
                $tmpFilePrefix . '.dvi',
                $tmpFilePrefix . '.ps');
            exec($cmd, $output, $status);
            //debug(array($cmd, $output, $status));
            if ($status !== 0) {
                $this->clean($tmpFilePrefix);
                return CAKE_COMPONENT_EQUATION_ERROR_DVIPS;
            }
    
            // Convert the postscript to a PNG and trim (convert)
            $cmd = sprintf('%s -density %d -trim %s %s',
                $this->convertPath,
                $this->density,
                $tmpFilePrefix . '.ps',
                $tmpFilePrefix . '.png');
            exec($cmd, $output, $status);
            //debug(array($cmd, $output, $status));
            if ($status !== 0) {
                $this->clean($tmpFilePrefix);
                return CAKE_COMPONENT_EQUATION_ERROR_CONVERT;
            }
    
            // Move our new equation to the desired location
            $status = copy($tmpFilePrefix . '.png', $outputfile);
            if (!$status) {
                $this->clean($tmpFilePrefix);
                return CAKE_COMPONENT_EQUATION_ERROR_WRITEPNG;
            }
    
            // Clean up
            $this->clean($tmpFilePrefix);
    
            return true;
        }
        
        /**
         * Make sure our input is valid
         */
        function validate($formula)
        {
            // Define a list of invalid tags
            $blacklist = array(
                'include',
                'def',
                'command',
                'loop',
                'repeat',
                'open',
                'toks',
                'output',
                'input',
                'catcode',
                'name',
                '^^',
                '\\every',
                '\\errhelp',
                '\\errorstopmode',
                '\\scrollmode',
                '\\nonstopmode',
                '\\batchmode',
                '\\read',
                '\\write',
                'csname',
                '\\newhelp',
                '\\uppercase',
                '\\lowercase',
                '\\relax',
                '\\aftergroup',
                '\\afterassignment',
                '\\expandafter',
                '\\noexpand',
                '\\special'
                );
            
            // Iterate the tags and check they're not in our formula
            foreach ($blacklist as $black) {
                if (stristr($formula, $black)) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Wraps a minimalistic LaTeX document around the formula and returns a string
         * containing the whole document as string. Customize if you want other fonts for
         * example.
         *
         * @param string formula in LaTeX format
         * @returns minimalistic LaTeX document containing the given formula
         */
        function wrap($formula)
        {
            $string  = "\documentclass[".$this->fontSize."pt]{".$this->latexClass."}\n";
            $string .= "\usepackage[latin1]{inputenc}\n";
            $string .= "\usepackage{amsmath}\n";
            $string .= "\usepackage{amsfonts}\n";
            $string .= "\usepackage{amssymb}\n";
            $string .= "\pagestyle{empty}\n";
            $string .= "\begin{document}\n";
            $string .= "$" . $formula . "$\n";
            $string .= "\end{document}\n";
    
            return $string;
        }
    
        /**
         * Cleans the temporary directory
         */
        function clean($tmpFilePrefix)
        {
            $extensions = array('tex', 'aux', 'log', 'dvi', 'ps', 'png');
            foreach ($extensions as $extension) {
                $file = $tmpFilePrefix . '.' . $extension;
                if (file_exists($file)) {
                    unlink($file); 
                }
            }
            
            return true;
        }
    }


.. meta::
    :title: Equation Generation
    :description: CakePHP Article related to component,latex,equation,Components
    :keywords: component,latex,equation,Components
    :copyright: Copyright 2008 
    :category: components

