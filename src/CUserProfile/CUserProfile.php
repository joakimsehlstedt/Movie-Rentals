<?php

/**
 * CUserProfile
 * Module to present content from database inside Ami pagecontrollers.
 */
class CUserProfile {

    /**
     * Properties
     */
    private $options;
    private $html;
    private $db;
    private $content;
    private $current_user;

    /**
     * Constructor
     * @param type $options, array of class specific options.
     */
    public function __construct($options) {
        $default = array('database' => null,);
        $this->options = array_merge($default, $options);
        $this->db = new CDatabase($this->options['database']);

        $this->html = array(
            'list' => null,
            'form' => null,
            'status' => null,
        );

        // Pick up user input
        $this->checkUserInput();

        // Save content    
        if ($this->content['save'] && isset($this->content['user'])) {
            $this->getContentFromCurrentUser();
            $this->saveForm();
        }

        if (isset($this->content['view']) && is_numeric($this->content['view'])) {
            $this->viewProfile();
        } else {
            // List website users
            $this->contentList();
            // Show user details editor form
            if (isset($this->content['user'])) {
                $this->getContentFromCurrentUser();
                $this->editorForm();
            }
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
     * saveForm
     * Saves the data from the form to the Ami_Content table.
     */
    private function viewProfile() {
        $sql = "SELECT acronym, name, admin, info FROM RM_User WHERE id = ?;";
        $params = array($this->content['view'],);
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);

        $admin = $res[0]->admin ? 'yes' : 'no';
        $this->html['list'] = "<article class='nine columns'>"
                . "<strong>Id: </strong>" . $this->content['view'] . "<br>"
                . "<strong>Acronym: </strong>" . $res[0]->acronym . "<br>"
                . "<strong>Name: </strong>" . $res[0]->name . "<br>"
                . "<strong>Administrator: </strong>" . $admin . "<br>"
                . "<strong>Short presentation: </strong>" . $res[0]->info . "<br>"
                . "<a href='users.php'><< Back to list</a>"
                . "</article>";
    }

    /**
     * saveForm
     * Saves the data from the form to the Ami_Content table.
     */
    private function saveForm() {
        $sql = "
                UPDATE RM_User SET
                  name    = ?,
                  info = ?
                WHERE 
                  id = ?
              ;";

        $params = array(
            $this->content['name'],
            $this->content['info'],
            $this->current_user['id'],
        );
        
        $res = $this->db->ExecuteQuery($sql, $params);
        if ($res) {
            $this->html['status'] = 'Content updated.';
        } else {
            $this->html['status'] = 'Content was NOT updated.';
        }
    }

    /**
     * Renders a list of all the content in the table.
     */
    private function contentList() {
        $sql = "SELECT id, acronym, name, admin FROM RM_User;";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

        // draw table
        if ($res) {
            // display table headings
            $html = "<table><tr><th>Id</th><th>Acronym</th><th>Name</th>"
                    . "<th>Administrator</th><th>View profile</th></tr>";

            // display table data
            foreach ($res as $key => $entry) {
                $html .= "<tr>";
                foreach ($entry as $key => $cell) {
                    if ($key == 'admin') {
                        $admin = $cell ? 'yes' : 'no';
                        $html .= "<td>{$admin}</td>";
                    } else {
                        $html .= "<td>{$cell}</td>";
                    }
                }
                $html .= "<td><a href='users.php?view={$entry->id}'>[VIEW]</a></td>";
                $html .= "</tr>";
            }
            $html .= "</table>";
        } else {
            $html = "<p>No hits.</p>";
        }

        $this->html['list'] = $html;
    }

    /**
     * Get id matching content from database. 
     */
    private function getContentFromCurrentUser() {
        $sql = 'SELECT * FROM RM_User WHERE acronym = ?';
        $params = array($this->content['user'],);
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
        $this->current_user = (array) $res[0];
    }

    /**
     * Renders the editor in the html array.
     */
    private function editorForm() {
        // Sanitize content before using it.
        $id = htmlentities($this->current_user['id'], null, 'UTF-8');
        $acronym = htmlentities($this->current_user['acronym'], null, 'UTF-8');
        $name = htmlentities($this->current_user['name'], null, 'UTF-8');
        $admin = $this->current_user['admin'] ? 'yes' : 'no';
        $info = htmlentities($this->current_user['info'], null, 'UTF-8');

        $this->html['form'] = <<<EOD
  <form method=post>
  <fieldset>
  <legend>Edit my userprofile</legend>
  <input type='hidden' name='id' value='{$id}'/>
  <label>Acronym:</label><input type='text' size=10 name='acronym' value='{$acronym}' readonly/>
  <label>Name:</label><input type='text' size=50 name='name' value='{$name}'/>
  <label>Administrator:</label><input type='text' size=10 name='admin' value='{$admin}' readonly/>
  <label>Short presentation:</label><textarea name='info' rows='5' cols='50'>{$info}</textarea>
  <p class=buttons><input type='submit' name='save' value='Save'/> <input type='reset' value='Reset'/></p>
  </fieldset>
</form>
EOD;
    }

    /**
     * Collects user input in content array.
     */
    private function checkUserInput() {
        $this->content = array(
            'user' => isset($_SESSION['user']) ? $_SESSION['user']->acronym : null,
            'name' => isset($_POST['name']) ? $_POST['name'] : null,
            'admin' => isset($_POST['admin']) ? $_POST['admin'] : null,
            'save' => isset($_POST['save']) ? true : false,
            'view' => isset($_GET['view']) ? $_GET['view'] : null,
            'acronym' => isset($_POST['acronym']) ? $_POST['acronym'] : null,
            'info' => isset($_POST['info']) ? $_POST['info'] : null,
        );
    }

}
