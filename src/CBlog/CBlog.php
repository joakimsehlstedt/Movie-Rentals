<?php

/**
 * CContent
 * Module to present content from database inside pagecontrollers.
 */
class CBlog {

    /**
     * Properties
     */
    private $options;
    private $res;
    private $html;

    /**
     * Constructor
     */
    public function __construct($options) {
        $this->html = "";
        $this->res = array(
            new stdClass(),
        );
        $default = array(
            'database' => null,
            'slug' => null,
            'limit' => null,
        );
        $this->options = array_merge($default, $options);
        $this->db = new CDatabase($this->options['database']);

        if ($this->options['slug'] != null) {
            $sql = "SELECT * FROM RM_Content WHERE slug = ?;";
            $params = array(
                $this->options['slug'],
            );
            $this->res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
            $this->html .= $this->htmlPerPost(0);
        } else {
            $sql = "SELECT * FROM RM_Content WHERE type = 'post' ORDER BY published DESC";
            if (isset($this->options['limit']) && is_numeric($this->options['limit'])) {
                $sql .= " LIMIT " . $this->options['limit'];
            }
            $sql .= " ;";

            $params = array(
                $this->options['slug'],
            );
            $this->res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);

            foreach ($this->res as $key => $post) {
                $this->html .= $this->htmlBlogRoll($key);
            }
        }
    }

    /**
     * Get the CBlog html string for display.
     * 
     * @return type string html with all relevant posts.
     */
    public function getHtml() {
        return $this->html;
    }

    /**
     * Convert database post to html string.
     * 
     * @param type $index the post to convert
     * @return string the indexed post html code.
     */
    private function htmlPerPost($index) {
        $data = htmlentities($this->res[$index]->data, null, 'UTF-8');

        if (isset($this->res[$index]->filter)) {
            $filter = htmlentities($this->res[$index]->filter, null, 'UTF-8');
            $data = CTextFilter::doFilter($data, $filter);
        }

        $html = "<h3>" . htmlentities($this->res[$index]->title, null, 'UTF-8') . "</h3>";
        $html .= "<p>";
        if (isset($this->res[$index]->image)) {
            $html .= "<img class='blogpost scale-with-grid' src='img.php?src="
                    . htmlentities($this->res[$index]->image, null, 'UTF-8');
            $html .= "&amp;width=200&amp;height=300&amp;crop-to-fit&amp;"
                    . "save-as=jpg&amp;quality=50' alt='N/A'/>";
        }
        $html .= $data . "</p>";
        if (isset($this->res[$index]->created)) {
            $html .= "<small><strong>Published: </strong><em>"
                    . substr($this->res[$index]->published, 0, 10)
                    . "</em><br><strong>Created: </strong><em>"
                    . substr($this->res[$index]->created, 0, 10) . "</em></small>";
        }
        return $html;
    }

    /**
     * Convert database post to short html string.
     * 
     * @param type $index the post to convert
     * @return string the indexed post html code.
     */
    public function htmlBlogRoll($index) {
        $data = htmlentities($this->res[$index]->data, null, 'UTF-8');

        if (isset($this->res[$index]->filter)) {
            $filter = htmlentities($this->res[$index]->filter, null, 'UTF-8');
            $data = CTextFilter::doFilter($data, $filter);
        }

        $data = (strlen($data) > 50) ? substr($data, 0, 43) . "...<span class='disclaimer'> read more</span>" : $data;
        $title = htmlentities($this->res[$index]->title, null, 'UTF-8');
        $title = (strlen($title) > 60) ? substr($title, 0, 57) . '...' : $title;
        $date = htmlentities($this->res[$index]->published, null, 'UTF-8');
        $date = substr($date, 0, 10);

        $html = "<a href='blog.php?slug=" . htmlentities($this->res[$index]->slug, null, 'UTF-8') . "'>";
        $html .= "<img src='img.php?src=" . htmlentities($this->res[$index]->image, null, 'UTF-8');
        $html .= "&amp;width=25&amp;height=25&amp;crop-to-fit&amp;save-as=jpg&amp;quality=50' alt='N/A'/>";
        $html .= " | " . $date;
        $html .= " | <strong>" . $title . "</strong>";
        $html .= " | " . $data . "</a><br>";

        return $html;
    }

}
