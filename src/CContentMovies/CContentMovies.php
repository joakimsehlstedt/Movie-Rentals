<?php

/**
 * CContentMovies
 * For altering the movie database.
 */
class CContentMovies {

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
        
        // If user is administrator
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
            if (isset($this->content['newTitle'])) {
                $this->addNewEntry();
            }
        } else {
            $this->html['status'] = 'Only administrators can change content.';
        }
        
        // Choose view depending on user input
        if (isset($this->content['id']) && is_numeric($this->content['id'])) {
            $this->getContentFromCurrentId();
            $this->editorForm();
        } else if (isset($this->content['view']) && is_numeric($this->content['view'])) {
            header("Location: view.php?id={$this->content['view']}");
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
        $sql = "INSERT INTO RM_Movie(title, updated) VALUES (?, NOW());";
        $params = array($this->content['newTitle'],);
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
        $sql = "DELETE FROM RM_Movie WHERE id = ? LIMIT 1;";
        $params = array($this->content['doDelete'],);
        $res = $this->db->ExecuteQuery($sql, $params);
        header("Location: editmovies.php");
    }

    /**
     * saveForm
     * Saves the data from the form to the Ami_Content table.
     */
    private function saveForm() {
        $sql = '
                UPDATE RM_Movie SET
                  title   = ?,
                  director    = ?,
                  length     = ?,
                  year    = ?,
                  plot    = ?,
                  image  = ?,
                  subtext = ?,
                  speech = ?,
                  quality = ?,
                  format = ?,
                  price = ?,
                  updated = NOW()
                WHERE 
                  id = ?
              ;';

        $url = empty($url) ? null : $url;

        $params = array(
            $this->content['title'],
            $this->content['director'],
            $this->content['length'],
            $this->content['year'],
            $this->content['plot'],
            $this->content['image'],
            $this->content['subtext'],
            $this->content['speech'],
            $this->content['quality'],
            $this->content['format'],
            $this->content['price'],
            $this->content['id'],
        );

        $res = $this->db->ExecuteQuery($sql, $params);
        if ($res) {
            $this->html['status'] = "Content id = {$this->content['id']} updated.";
        } else {
            $this->html['status'] = 'Content was NOT updated.';
        }
    }

    /**
     * Renders a list of all the content in the Ami_Content table.
     */
    private function contentList() {
        $sql = "SELECT id, title, director, year, price FROM RM_Movie;";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

        // draw table
        if ($res) {
            // display table headings
            $html = "<table><tr><th>Id</th><th>Title</th><th>Director</th><th>Year</th>"
                    . "<th>Price</th><th>Edit</th><th>Delete</th><th>View</th></tr>";

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
                        . "<td><a href='" . getQueryString(array('view' => $entry->id))
                        . "'>[VIEW]</a></td>";
                $html .= "</tr>";
            }
            $html .= "</table>";
        } else {
            $html = "<p>No hits.</p>";
        }

        $this->html['list'] = $html;

        $this->html['form'] = "<form method=post><fieldset><legend>Add movie</legend>"
                . "<p><input type='text' name='newTitle' placeholder='Enter title'>"
                . "<p><input type='submit' name='doAdd' value='Add'/></p>"
                . "</fieldset></form>";
    }

    /**
     * Get id matching content from database. 
     */
    private function getContentFromCurrentId() {
        $sql = 'SELECT * FROM RM_Movie WHERE id = ?';
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
        $director = htmlentities($this->content['director'], null, 'UTF-8');
        $lenght = htmlentities($this->content['length'], null, 'UTF-8');
        $year = htmlentities($this->content['year'], null, 'UTF-8');
        $plot = htmlentities($this->content['plot'], null, 'UTF-8');
        $image = htmlentities($this->content['image'], null, 'UTF-8');
        $subtext = htmlentities($this->content['subtext'], null, 'UTF-8');
        $speech = htmlentities($this->content['speech'], null, 'UTF-8');
        $quality = htmlentities($this->content['quality'], null, 'UTF-8');
        $format = htmlentities($this->content['format'], null, 'UTF-8');
        $price = htmlentities($this->content['price'], null, 'UTF-8');
        $updated = htmlentities($this->content['updated'], null, 'UTF-8');
        $querystring = getQueryString(array('id' => null));

        $this->html['form'] = <<<EOD
  <form method=post>
  <fieldset>
  <legend>Edit content</legend>
  <input type='hidden' name='id' value='{$id}'/>
  <label>Title:</label><input type='text' size=100 name='title' value='{$title}'/>
  <label>Director:</label><input type='text' size=50 name='director' value='{$director}'/>
  <label>Lenght:</label><input type='text' size=50 name='length' value='{$lenght}'/>
  <label>Year:</label><input type='text' size=50 name='year' value='{$year}'/>
  <label>Plot:</label><textarea name='plot' rows='20' cols='100'>{$plot}</textarea>
  <label>Image:</label><input type='text' size=50 name='image' value='{$image}' />
  <label>Subtext:</label><input type='text' size=50 name='subtext' value='{$subtext}'/>
  <label>Speech:</label><input type='text' size=50 name='speech' value='{$speech}'/>
  <label>Quality:</label><input type='text' size=50 name='quality' value='{$quality}'/>
  <label>Format:</label><input type='text' size=50 name='format' value='{$format}'/>
  <label>Price:</label><input type='text' size=50 name='price' value='{$price}'/>
  <label>Updated:</label><input type='text' size=50 name='updated' value='{$updated}'/>
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
            'director' => isset($_POST['director']) ? $_POST['director'] : null,
            'length' => isset($_POST['length']) ? strip_tags($_POST['length']) : null,
            'year' => isset($_POST['year']) ? $_POST['year'] : null,
            'plot' => isset($_POST['plot']) ? strip_tags($_POST['plot']) : null,
            'image' => isset($_POST['image']) ? $_POST['image'] : null,
            'subtext' => isset($_POST['subtext']) ? $_POST['subtext'] : null,
            'speech' => isset($_POST['speech']) ? $_POST['speech'] : null,
            'quality' => isset($_POST['quality']) ? $_POST['quality'] : null,
            'format' => isset($_POST['format']) ? $_POST['format'] : null,
            'price' => isset($_POST['price']) ? $_POST['price'] : null,
            'updated' => isset($_POST['updated']) ? strip_tags($_POST['updated']) : null,
            'save' => isset($_POST['save']) ? true : false,
            'doDelete' => isset($_GET['doDelete']) ? strip_tags($_GET['doDelete']) : null,
            'acronym' => isset($_SESSION['user']) ? $_SESSION['user']->acronym : null,
            'admin' => isset($_SESSION['user']->admin) ? 1 : null,
            'newTitle' => isset($_POST['newTitle']) ? strip_tags($_POST['newTitle']) : null,
            'view' => isset($_GET['view']) ? strip_tags($_GET['view']) : null,
        );
    }

}
