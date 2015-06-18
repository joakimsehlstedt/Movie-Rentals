<?php

/**
 * CDBTableView
 * Search function for SQL tables.
 * With user interface.
 */
Class CDBTableView {

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
            'orderby' => null,
            'order' => null,
        );
        $this->options = array_merge($default, $options);
        $this->db = new CDatabase($this->options['database']);
        $this->min = 1;  // Startpage, usually 0 or 1, what you feel is convienient

        $this->searchForm();
        $this->runSelectQuery();

        $this->max = ceil($this->rowCount / $this->options['hits']);
        $this->displayTable();
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

        $sql = "SELECT id, image, title, director, genre, length, year, format, quality, price FROM "
                . "{$this->options['tablename']} ";
        if ($sqlYear && $sqlSearch) {
            $sql .= "WHERE " . $sqlSearch . " AND " . $sqlYear;
        } elseif ($sqlYear) {
            $sql .= "WHERE " . $sqlYear;
        } elseif ($sqlSearch) {
            $sql .= "WHERE " . $sqlSearch;
        } else {
            $sql .= "";
        }

        // Order by column
        if (isset($this->options['order']) && isset($this->options['orderby'])) {
            $sql .= "ORDER BY {$this->options['orderby']} {$this->options['order']}";
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
    private function displayTable() {
        // display hits per page menu
        $html = $this->getHitsPerPage(array(2, 4, 8));

        // draw table
        if ($this->result) {
            // display table headings
            $html .= "<table class='full-width'><tr>";
            foreach ($this->result[0] as $key => $value) {
                if ($key == 'image') {
                    $html .= "<th>{$key}</th>";
                } else if ($key != 'id') {
                    $html .= "<th>" . $this->orderBy($key) . "</th>";
                }
            }
            $html .= "</tr>";

            // display table data
            foreach ($this->result as $key => $value) {
                $html .= "<tr>";
                foreach ($value as $key => $cell) {
                    if ($key == 'image') {
                        $html .= "<td><img src='img.php?src=" . $cell
                                . "&amp;width=30&amp;height=45&amp;crop-to-fit"
                                . "&amp;save-as=jpg&amp;quality=50' alt=''/></td>";
                    } else if ($key == 'title') {
                        $html .= "<td><a href='view.php?id={$value->id}'>{$cell}</a></td>";
                    } else if ($key != 'id') {
                        $html .= "<td>{$cell}</td>";
                    }
                }
                $html .= "</tr>";
            }
            $html .= "</table>"
                    . $this->getPageNavigation($this->options['hits'], $this->options['page'], $this->max);
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
        $nav = "<div class='sixteen columns'>Hits per page: ";
        foreach ($hits AS $val) {
            $nav .= "<a href='" . getQueryString(array('hits' => $val, 'page' => 1)) . "'>$val</a> ";
        }
        $nav .= "</div>";
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
        $nav = "<div class='sixteen columns'>";
        $nav .= "<a href='" . getQueryString(array('page' => $min)) . "'>&lt;&lt;</a> ";
        $nav .= "<a href='" . getQueryString(array('page' => ($page > $min ? $page - 1 : $min))) . "'>&lt;</a> ";

        for ($i = $min; $i <= $max; $i++) {
            $nav .= "<a href='" . getQueryString(array('page' => $i)) . "'>$i</a> ";
        }

        $nav .= "<a href='" . getQueryString(array('page' => ($page < $max ? $page + 1 : $max))) . "'>&gt;</a> ";
        $nav .= "<a href='" . getQueryString(array('page' => $max)) . "'>&gt;&gt;</a> ";
        $nav .= "</div>";
        return $nav;
    }

    /**
     * Formats html string to display a search form
     */
    private function searchForm() {
        $p = isset($_GET['p']) ? htmlentities($_GET['p']) : null;
        $html = "<div class='sixteen columns'><form><fieldset><legend>Search</legend>"
                . "<input type='hidden' name='p' value='{$p}'>"
                . "<p><label>Produced between the years: </label>"
                . "<input type='search' size='10' name='year1' placeholder='{$this->options['year1']}'/>and"
                . "<input type='search' size='10' name='year2' placeholder='{$this->options['year2']}'/></p>"
                . "<p><label>Text (for substring, use % as *): </label>"
                . "<input type='search' name='search' placeholder='{$this->options['search']}'/></p>"
                . "<input type='submit' value='Search'/>"
                . " or <a href='list.php?list=1'>Show all</a></fieldset></form></div>";

        // update this objects html propery
        $this->html .= $html;
    }

    /**
     * Create links for sorting
     */
    private function orderBy($column) {
        $html = "<span class='orderby'>"
                . "<a href='"
                . getQueryString(array('orderby' => $column, 'order' => 'asc'))
                . "'>&darr;</a> "
                . "{$column} "
                . "<a href='"
                . getQueryString(array('orderby' => $column, 'order' => 'desc'))
                . "'>&uarr;</a>"
                . "</span>";
        return $html;
    }

}
