<?php
/*
       
   Pagination 1.1.0

   Made by John Cartwright (2005)
   Tested and modified by members at http://forums.devnetwork.net
   Feel free to use/modify this script at will
   Please leave credit where due
 
   Sample Usage
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   $query = 'SELECT * FROM `table`';
   function pagination($query, $perPage, $current, $maxPage = 4, $delimeter = '>') 

   $paginate = new pagination($query, 15, (isset($_GET['start']) ? $_GET['start'] : 0));
   print_r($paginate->output());
 
   Output
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   Array
    (
       [0] => SELECT * FROM `news` LIMIT 0,2
       [1] => Prev | First | 1 | 2 | 3 | 4 | 5 | 6 | ... | Next | Last
       [2] => Viewing: [ 1 - 2 ] of 24
    )

   History
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   1.1.0 Fixes
                - Missing Number Bug Fixed
                - Added changable delimeter
   
   Upcoming     
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   1.2.0
    - Multiple DB support              
*/

class pagination
{
        var $query;          //Query to be modified for paginating
        var $perPage;        //Maximum rows per page
        var $maxPage;        //Maximum pages shown at once (not including selected)
        var $current;        //Current page selected
        var $result;         //Result of query       
        var $totalNumRows;   //Amount of rows selected (non paginated)
        var $totalNumPages;  //Amount of pages
        var $BoundaryMin;    //Minimum boundary of page numbers
        var $BoundaryMax;    //Maximum boundary of page numbers
        var $numbers;        //Collection of formated pages

		var $filter;		// filter var added, to browse by letter

        function pagination($query, $perPage, $current, $maxPage = 4, $delimeter = '>', $filter = NULL) {
                $this->query     = $query;
                $this->delimeter = $delimeter;
                $this->perPage   = $perPage;
                $this->maxPage   = $maxPage;
                $this->current   = $current < 1 ? 1 : $current;
				$this->filter    = $filter;
                $this->maxPageOffset();   
                $this->initialize();
        }
       
        function maxPageOffset() {
                if ($this->maxPage % 2 == 1 && $this->maxPage != 1) {
                        $this->maxPage--;
                }
        }
       
        function initialize() {
                if ($this->performDatabaseCall()) {
                        $this->postInitialize();               
                }
        }
       
        function postInitialize() {
                if ($this->current > $this->totalNumPages) {
                        $this->current = $this->totalNumpages;
                }
                $this->formatSQL();     
                $this->getBoundaries();
                $this->buildNumbers();         
        }
       
        function performDatabaseCall() {
                $this->result = mysql_query($this->query) or die(mysql_error());
               
                if ($this->result) {
                        $this->totalNumRows  = $this->getNumRows();
                        $this->totalNumPages = $this->getNumPages();
                        return true;
                }
        }

        function getNumRows() {
                return mysql_num_rows($this->result);
        }

        function getNumPages() {
                return ceil($this->totalNumRows / $this->perPage);
        }       

        function formatSQL() {   
                $this->query .= ' LIMIT '.(($this->current-1) * $this->perPage).','.$this->perPage;   
        }

        function viewing() {
                $this->newLimit = $this->current * $this->perPage;
                $this->viewing  = 'Viewing: [ ';
               
                if ($this->perPage != 1) {
                        $this->viewing .= (($this->newLimit - $this->perPage) + 1) .' - '.  ($this->newLimit > $this->totalNumRows ? ($this->totalNumRows) : $this->newLimit);
                }
                else {
                        $this->viewing .= $this->current;
                }
               
                $this->viewing .= ' ] of '. $this->totalNumRows;
                return $this->viewing;   
        }

        function balanceOffset() {           
                if ($this->BoundaryMin < 1) {
                        $this->BoundaryMin = 1;   
                       
                        if ($this->current < $this->maxDivided) {
                                $difference = $this->maxDivided - $this->current;
                        }

                        $this->BoundaryMax = $this->BoundaryMax + $difference + 1;
                }
               
                if ($this->BoundaryMax > $this->totalNumPages) {
                        $difference = ($this->BoundaryMax - $this->totalNumPages);
                        $this->BoundaryMin = ($this->BoundaryMin - $difference);
                        $this->BoundaryMax = ($this->BoundaryMax - $difference);
                }                     
        }

        function truncateOffset() {
                if ($this->BoundaryMin < 1) {
                        $this->BoundaryMin = 1;
                }
                if ($this->BoundaryMax > $this->totalNumPages) {
                        $this->BoundaryMax = $this->totalNumPages;
                }
        }
       
        function getBoundaries() {
                switch ($this->current) :
                        case ($this->totalNumPages) :
                                $this->BoundaryMax = $this->totalNumPages;
                                $this->BoundaryMin = (($this->BoundaryMax - $this->maxPage));               
                        break;
                        case (1) :
                                $this->BoundaryMin = 1;   
                                $this->BoundaryMax = ($this->current + $this->maxPage);
                        break;
                        default:
                                $this->maxDivided  = ceil($this->maxPage / 2);
                                $this->BoundaryMin = ($this->current - $this->maxDivided);
                                $this->BoundaryMax = ($this->current + $this->maxDivided);                                                           
                                $this->balanceOffset();$this->truncateOffset();
                                break; 
                endswitch;
               
                $this->truncateOffset();                       
        }

        function buildNumbers($string = '') {
                for ($x = $this->BoundaryMin; $x <= $this->BoundaryMax; $x++) {
                        if ($x == $this->BoundaryMin && $this->BoundaryMin != 1) {
                                $string .= '... '.$this->delimeter.' ';
                        }                            
                        if ($this->current == $x) {
                                $string .= '<span style="font-weight: bold">'.$x.'</span> '.$this->delimeter.' ';
                        }
                        else {
                                $string .= '<a href="?'.( strlen( $this->filter ) == 1 ? 'filter='. $this->filter .'&amp;' : '').'start='.$x.'">'.$x.'</a> '.$this->delimeter.' ';
                        }       
                        if (($x == $this->BoundaryMax) && ($this->BoundaryMax < $this->totalNumPages)) {
                                $string .= '... '.$this->delimeter.' ';
                        }                                          
                }
               
                $this->numbers = $string;
        }

        function buildOutput() {
                $output  = ($this->current == 1 ? 'Prev '.$this->delimeter.' ' : '<a href="?'.( strlen( $this->filter ) == 1 ? 'filter='. $this->filter .'&amp;' : '' ).'start='.($this->current - 1).'">Prev</a> '.$this->delimeter.' ');
                $output .= '<a href="?'.( strlen( $this->filter ) == 1 ? 'filter='. $this->filter .'&amp;' : '' ).'start=1">First</a> '.$this->delimeter.' ';               
                $output .= $this->numbers;
                $output .= ($this->current == ($this->totalNumPages) ? 'Next '.$this->delimeter.' ' : '<a href="?'.( strlen( $this->filter ) == 1 ? 'filter='. $this->filter .'&amp;' : '' ).'start='.($this->current + 1).'" >Next</a> '.$this->delimeter.' ');               
                $output .= '<a href="?'.( strlen( $this->filter ) == 1 ? 'filter='. $this->filter .'&amp;' : '' ).'start='.($this->totalNumPages).'">Last</a>';     
                return $output;
        }

        function output() {
                return array($this->query, $this->buildOutput(), $this->viewing());
        }
}

?> 
