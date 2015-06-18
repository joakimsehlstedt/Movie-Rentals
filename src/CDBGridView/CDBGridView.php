<?php

/**
 * CDBTableView
 * Search function for SQL tables.
 * With user interface.
 */
Class CDBGridView {

    /**
     * Properties
     */
    private $options;   // Options used when creating the CDBTableView object.
    private $db;        // Database connection
    private $min;       // First page number
    private $max;       // Last page number 
    private $html;      // Output html string
    private $result;    // SQL Query result
    private $rowCount;  // All rows in current search

    /**
     * Con
     * @param type $options
     */

    public function __construct($options) {
        $default = array(
            'database' => null,
            'tablename' => null,
            'hits' => null,
            'page' => null,
            'year1' => null,
            'year2' => null,
            'search' => null,
            'columnToSearch' => null,
            'genre' => null,
        );
        $this->options = array_merge($default, $options);
        $this->db = new CDatabase($this->options['database']);
        $this->min = 1;  // Startpage, usually 0 or 1, what you feel is convienient
        
        $this->runSelectQuery();
        $this->max = ceil($this->rowCount / $this->options['hits']);
        
        $this->displayGrid();
    }

    /**
     * Get the html string for userinterface
     * 
     * @return type String
     */
    public function getHtml() {
        return $this->html;
    }

    private function runSelectQuery() {
        // Do SELECT from a table
        if ($this->options['year1'] && $this->options['year2']) {
            $sqlYear = "year >= {$this->options['year1']} AND "
                    . "year <= {$this->options['year2']} ";
        } elseif ($this->options['year1']) {
            $sqlYear = "year >= {$this->options['year1']} ";
        } elseif ($this->options['year2']) {
            $sqlYear = "year <= {$this->options['year2']} ";
        } else {
            $sqlYear = null;
        }

        if ($this->options['search']) {
            $sqlSearch = "{$this->options['columnToSearch']} LIKE '{$this->options['search']}' ";
        } else {
            $sqlSearch = null;
        }


        $sql = "SELECT * FROM {$this->options['tablename']} ";
        if ($sqlYear && $sqlSearch) {
            $sql .= "WHERE " . $sqlSearch . " AND " . $sqlYear;
        } elseif ($sqlYear) {
            $sql .= "WHERE " . $sqlYear;
        } elseif ($sqlSearch) {
            $sql .= "WHERE " . $sqlSearch;
        } else {
            $sql .= "";
        }


        if ($this->options['genre']) {
            $sql = "SELECT 
                        M.*,
                        G.id AS genre
                      FROM RM_Movie AS M
                        LEFT OUTER JOIN RM_Movie2Genre AS M2G
                          ON M.id = M2G.idMovie
                        LEFT OUTER JOIN RM_Genre AS G
                          ON M2G.idGenre = G.id
                      WHERE G.id = {$this->options['genre']}
                      ;";
        }

        // Count the number of rows matching search result
        $sqlRow = $sql . ";";
        $resultRowCount = $this->db->ExecuteSelectQueryAndFetchAll($sqlRow);
        $this->rowCount = count($resultRowCount);

        // Finalise $sql and limit search to displayoptions
        $sql .= " LIMIT " . $this->options['hits'] . " OFFSET "
                . (($this->options['page'] - 1) * $this->options['hits']) . ";";
        $this->result = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    }

    /**
     * 
     */
    private function displayGrid() {
        // display hits per page menu
        $html = "<div class='sixteen columns'>" . $this->getHitsPerPage(array(8, 16, 32)) . "</div>";

        // draw table
        if ($this->result) {
            foreach ($this->result as $key => $value) {
                $title = (strlen($value->title) > 28) ?
                        substr($value->title, 0, 25) . '...' : $value->title;
                $html .= "<a href='view.php?id={$value->id}'><figure class='two columns'><img class='scale-with-grid' src='img.php?src=" . $value->image;
                $html .= "&amp;width=100&amp;height=148&amp;crop-to-fit&amp;save-as=jpg&amp;quality=60' alt=''/>";
                $html .= "<figcaption>{$title}</figcaption></figure></a>";
            }
            $html .= "<div class='sixteen columns'>" . $this->getPageNavigation($this->options['hits'], $this->options['page'], $this->max) . "</div>";
        } else {
            $html = "<p>No hits.</p>";
        }
        $this->html .= $html;
    }

    /**
     * Create links for hits per page.
     * Usage: echo getHitsPerPage(array(2, 4, 8));
     *
     * @param array $hits a list of hits-options to display.
     * @return string as a link to this page.
     */
    private function getHitsPerPage($hits) {
        $nav = "Hits per page: ";
        foreach ($hits AS $val) {
            $nav .= "<a href='" . getQueryString(array('hits' => $val, 'page' => 1)) . "'>$val</a> ";
        }
        return $nav;
    }

    /**
     * Create navigation among pages.
     * Usage: echo getPageNavigation($hits, $page, $max);
     * 
     * @param integer $hits per page.
     * @param integer $page current page.
     * @param integer $max number of pages. 
     * @param integer $min is the first page number, usually 0 or 1. 
     * @return string as a link to this page.
     */
    private function getPageNavigation($hits, $page, $max, $min = 1) {
        $nav = "<a href='" . getQueryString(array('page' => $min)) . "'>&lt;&lt;</a> ";
        $nav .= "<a href='" . getQueryString(array('page' => ($page > $min ? $page - 1 : $min))) . "'>&lt;</a> ";

        for ($i = $min; $i <= $max; $i++) {
            $nav .= "<a href='" . getQueryString(array('page' => $i)) . "'>$i</a> ";
        }

        $nav .= "<a href='" . getQueryString(array('page' => ($page < $max ? $page + 1 : $max))) . "'>&gt;</a> ";
        $nav .= "<a href='" . getQueryString(array('page' => $max)) . "'>&gt;&gt;</a> ";
        return $nav;
    }

    /**
     * Formats html string to display a search form
     */
    private function searchForm() {
        $p = isset($_GET['p']) ? htmlentities($_GET['p']) : null;
        $html = "<form class='sixteen columns'><fieldset><legend>Search</legend>"
                . "<input type='hidden' name='p' value='{$p}'>"
                . "<p><label>Produced between the years: "
                . "<input type='search' size='10' name='year1' placeholder='{$this->options['year1']}'/>and"
                . "<input type='search' size='10' name='year2' placeholder='{$this->options['year2']}'/></label>"
                . "<label>Text (for substring, use % as *): "
                . "<input type='search' name='search' placeholder='{$this->options['search']}'/></label></p>"
                . "<input type='submit' value='Search'/>"
                . " or <a href='?p={$p}'>Show all</a></fieldset></form>";

        // update this objects html propery
        $this->html .= $html;
    }

}
