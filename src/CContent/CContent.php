<?php

/**
 * CContent
 * Module to present content from database inside Ami pagecontrollers.
 */
class CContent {

    /**
     * Properties
     */
    private $options;
    private $html;
    private $db;
    private $content;

    /**
     * Constructor
     * @param type $options, array of class specific options.
     */
    public function __construct($options) {
        $default = array('database' => null,);
        $this->options = array_merge($default, $options);
        $this->db = new CDatabase($this->options['database']);

        $this->html = array(
            'status' => null,
            'list' => null,
            'form' => null,
        );

        // Pick up user input
        $this->checkUserInput();

        // Check that incoming parameters are valid
        isset($this->content['acronym']) or header("Location: login.php");

        if ($this->content['admin']) {
            // Save content    
            if ($this->content['save']) {
                $this->saveForm();
            }

            // Delete content
            if (isset($this->content['doDelete']) &&
                    is_numeric($this->content['doDelete'])) {
                $this->deleteEntry();
            }

            // Add content
            if (isset($this->content['newType'])) {
                $this->addNewEntry();
            }
        } else {
            $this->html['status'] = 'Only administrators can change content.';
        }

        // Choose view depending on user input
        if (isset($this->content['id']) && is_numeric($this->content['id'])) {
            $this->getContentFromCurrentId();
            $this->editorForm();
        } else {
            $this->contentList();
        }
    }

    /**
     * Get the current html content in CContent object.
     * @return type array of strings
     */
    public function getHtml() {
        return $this->html;
    }

    /**
     * Add a new row in Ami_Content table
     */
    private function addNewEntry() {
        $t = time();
        if ($this->content['newType'] == 'post') {

            $sql = "INSERT INTO RM_Content(slug, type, created) VALUES (" . $t . ", ?, NOW());";

        } elseif ($this->content['newType'] == 'page') {

            $sql = "INSERT INTO RM_Content(url, type, created) VALUES (" . $t . ", ?, NOW());";

        }

        $params = array($this->content['newType'],);
        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $this->html['status'] = 'New entry added.';
        } else {
            $this->html['status'] = 'Could not add new entry.';
        }
    }

    /**
     * Delete the choosen entry from Ami_Content table.
     */
    private function deleteEntry() {
        $sql = "DELETE FROM RM_Content WHERE id = ? LIMIT 1;";
        $params = array($this->content['doDelete'],);
        $res = $this->db->ExecuteQuery($sql, $params);
        header("Location: edit.php");
    }

    /**
     * saveForm
     * Saves the data from the form to the Ami_Content table.
     */
    private function saveForm() {
        $sql = "UPDATE RM_Content SET\n";
        if ($this->content['published'] == null) {
            $sql .= "published = NOW(),\n";
        } else {
            $sql .= "published = '" . $this->content['published'] . "',\n";
        }
        $sql .= " title   = ?,
                  slug    = ?,
                  url     = ?,
                  data    = ?,
                  type    = ?,
                  filter  = ?,
                  category = ?,
                  updated = NOW()
                WHERE 
                  id = ?
              ;";

        $params = array(
            $this->content['title'],
            $this->content['slug'],
            $this->content['url'],
            $this->content['data'],
            $this->content['type'],
            $this->content['filter'],
            $this->content['category'],
            $this->content['id'],
        );
        
        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $this->html['status'] = 'Content updated.';
        } else {
            $this->html['status'] = 'Content was NOT updated.';
        }
    }

    /**
     * Renders a list of all the content in the Ami_Content table.
     */
    private function contentList() {
        $sql = "SELECT id, title, type, url, slug FROM RM_Content;";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

        // draw table
        if ($res) {
            // display table headings
            $html = "<table><tr><th>Id</th><th>Title</th><th>Type</th><th>Url</th>"
                    . "<th>Slug</th><th>Edit</th><th>Delete</th><th>View</th></tr>";

            // display table data
            foreach ($res as $key => $entry) {
                $html .= "<tr>";
                foreach ($entry as $cell) {
                    $html .= "<td>{$cell}</td>";
                }
                $html .= "<td><a href='" . getQueryString(array('id' => $entry->id))
                        . "'>[EDIT]</a></td>"
                        . "<td><a href='" . getQueryString(array('doDelete' => $entry->id))
                        . "' onclick='return confirmDelete()'>[DELETE]</a></td>"
                        . "<td><a href='" . $this->getUrlToContent((array) $entry)
                        . "'>[VIEW]</a></td>";
                $html .= "</tr>";
            }
            $html .= "</table>";
        } else {
            $html = "<p>No hits.</p>";
        }

        $this->html['list'] = $html;

        $this->html['form'] = "<form method=post><fieldset><legend>Add content</legend>"
                . "<p><input type='radio' name='newType' value='page'>Page content<br>"
                . "<input type='radio' name='newType' value='post'>Blog post</p>"
                . "<p><input type='submit' name='doAdd' value='Add'/></p>"
                . "</fieldset></form>";
    }

    /**
     * Get id matching content from database. 
     */
    private function getContentFromCurrentId() {
        $sql = 'SELECT * FROM RM_Content WHERE id = ?';
        $params = array($this->content['id'],);
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
        $this->content = array_merge($this->content, (array) $res[0]);
    }

    /**
     * Renders the editor in the html array.
     */
    private function editorForm() {
        // Sanitize content before using it.
        $id = htmlentities($this->content['id'], null, 'UTF-8');
        $title = htmlentities($this->content['title'], null, 'UTF-8');
        $slug = htmlentities($this->content['slug'], null, 'UTF-8');
        $url = htmlentities($this->content['url'], null, 'UTF-8');
        $data = htmlentities($this->content['data'], null, 'UTF-8');
        $type = htmlentities($this->content['type'], null, 'UTF-8');
        $filter = htmlentities($this->content['filter'], null, 'UTF-8');
        $published = htmlentities($this->content['published'], null, 'UTF-8');
        $category = htmlentities($this->content['category'], null, 'UTF-8');
        $querystring = getQueryString(array('id' => null));

        $this->html['form'] = <<<EOD
  <form method=post>
  <fieldset>
  <legend>Edit content</legend>
  <input type='hidden' name='id' value='{$id}'/>
  <label>Title:</label><input type='text' size=100 name='title' value='{$title}'/>
EOD;

        if ($type == 'post') {
            $this->html['form'] .= "<label>Slug: <grey>(*uniqe name required)</grey>"
                    . "</label><input type = 'text' size = 50 name = 'slug' value = '{$slug}'/>";
        } elseif ($type == 'page') {
            $this->html['form'] .= "<label>Url: <grey>(*uniqe name required)</grey>"
                    . "</label><input type = 'text' size = 50 name = 'url' value = '{$url}'/>";
        }

        $this->html['form'] .= <<<EOD
  <label>Category:</label><input type='text' size=50 name='category' value='{$category}'/>
  <label>Text:</label><textarea name='data' rows='20' cols='100'>{$data}</textarea>
  <label>Type:</label><input type='text' size=50 name='type' value='{$type}' readonly/>
  <label>Filter:</label><input type='text' size=50 name='filter' placeholder='bbcode,link,markdown,nl2br' value='{$filter}'/>
  <label>Publishing date:</label><input type='text' size=50 name='published' value='{$published}'/>
  <p class=buttons><input type='submit' name='save' value='Save'/> <input type='reset' value='Reset'/></p>
  <p><a href='{$querystring}'><< Back to list</a></p>
  </fieldset>
</form>
EOD;
    }

    /**
     * Collects user input in content array.
     */
    private function checkUserInput() {
        $this->content = array(
            'id' => isset($_POST['id']) ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null),
            'title' => isset($_POST['title']) ? $_POST['title'] : null,
            'slug' => isset($_POST['slug']) ? $_POST['slug'] : null,
            'url' => isset($_POST['url']) ? strip_tags($_POST['url']) : null,
            'data' => isset($_POST['data']) ? $_POST['data'] : null,
            'type' => isset($_POST['type']) ? strip_tags($_POST['type']) : null,
            'filter' => isset($_POST['filter']) ? $_POST['filter'] : null,
            'published' => isset($_POST['published']) ? strip_tags($_POST['published']) : null,
            'save' => isset($_POST['save']) ? true : false,
            'doDelete' => isset($_GET['doDelete']) ? strip_tags($_GET['doDelete']) : null,
            'acronym' => isset($_SESSION['user']) ? $_SESSION['user']->acronym : null,
            'admin' => isset($_SESSION['user']->admin) ? 1 : null,
            'newType' => isset($_POST['newType']) ? strip_tags($_POST['newType']) : null,
            'category' => isset($_POST['category']) ? strip_tags($_POST['category']) : null,
        );
    }

    /**
     * Creates Ami_Content table in database.
     * Drops and recreates if already existing.
     * 
     * @return string, createTable successful or not.
     */
    public function createTable() {
        $sql = "DROP TABLE IF EXISTS RM_Content;";
        $this->db->ExecuteQuery($sql);

        $sql = "CREATE TABLE RM_Content (
              id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
              slug CHAR(80) UNIQUE,
              url CHAR(80) UNIQUE,

              type CHAR(80),
              title VARCHAR(80),
              data TEXT,
              filter CHAR(80),

              published DATETIME,
              created DATETIME,
              updated DATETIME,
              deleted DATETIME

            ) ENGINE INNODB CHARACTER SET utf8;";
        $res = $this->db->ExecuteQuery($sql);

        if ($res) {
            $output = 'New table created in database.';
        } else {
            $output = 'Could not create new table.';
        }
        return $output;
    }

    /**
     * Create a link to the content, based on its type.
     *
     * @param object $content to link to.
     * @return string with url to display content.
     */
    private function getUrlToContent($content) {
        switch ($content['type']) {
            case 'page': return "page.php?url={$content['url']}";
                break;
            case 'post': return "blog.php?slug={$content['slug']}";
                break;
            default: return null;
                break;
        }
    }

}
