<?php

/**
 * CContent
 * Module to present content from database inside pagecontrollers.
 */
class CPage {

    /**
     * Properties
     */
    private $options;
    private $res;

    /**
     * Constructor
     */
    public function __construct($options) {
        $this->res = array(
            new stdClass(),
        );
        $default = array(
            'database' => null,
            'url' => null,
        );
        $this->options = array_merge($default, $options);

        if (isset($options['url'])) {
            $this->db = new CDatabase($this->options['database']);

            $sql = "SELECT * FROM RM_Content WHERE url = ?;";
            $params = array(
                $this->options['url'],
            );
            $this->res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
        } else {
            $this->res[0]->data = "No data to display.";
            $this->res[0]->title = "Alert!";
        }
    }

    /**
     * The page content for the main element.
     * @return string of html formatted text
     */
    public function getHtmlMain() {
        $data = htmlentities($this->res[0]->data, null, 'UTF-8');


        if (isset($this->res[0]->filter)) {
            $filter = htmlentities($this->res[0]->filter, null, 'UTF-8');
            $data = CTextFilter::doFilter($data, $filter);
        }

        return $data;
    }

    /**
     * The title string.
     * @return string of html formatted title
     */
    public function getHtmlTitle() {
        $title = htmlentities($this->res[0]->title, null, 'UTF-8');
        return $title;
    }

    /**
     * The date string.
     * @return string of html formatted title
     */
    public function getHtmlDate() {
        if (isset($this->res[0]->updated)) {
            $date .= "<small><em>Last updated: "
                    . $this->res[0]->updated . "</em></small>";
        }
        return $date;
    }

}
