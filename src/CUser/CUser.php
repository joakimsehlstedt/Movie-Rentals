<?php

/*
 * CUser
 * 
 * Compare user login details with database and set the $_SESSION['user'] if authentic.
 * 
 * --
 * -- Table for user
 * --
 * DROP TABLE IF EXISTS Ami_User;
 *
 * CREATE TABLE Ami_User
 * (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   acronym CHAR(12) UNIQUE NOT NULL,
 *   name VARCHAR(80),
 *   password CHAR(32),
 *   salt INT NOT NULL
 * ) ENGINE INNODB CHARACTER SET utf8;
 * 
 * INSERT INTO Ami_User (acronym, name, salt) VALUES 
 *   ('doe', 'John/Jane Doe', unix_timestamp()),
 *   ('admin', 'Administrator', unix_timestamp())
 * ;
 * 
 * UPDATE Ami_User SET password = md5(concat('doe', salt)) WHERE acronym = 'doe';
 * UPDATE Ami_User SET password = md5(concat('admin', salt)) WHERE acronym = 'admin';
 */

Class CUser {

    /**
     * Members
     */
    private $options;   // Options used when creating the CUser object.

    /**
     * Constructor creating a PDO object connecting to a choosen database.
     *
     * @param array $options containing details for connecting to the database.
     *
     */

    public function __construct($options) {
        $default = array(
            'acronym' => null,
            'name' => null,
            'password' => null,
            'database' => null,
        );
        $this->options = array_merge($default, $options);

        if (isset($this->options['acronym']) && isset($this->options['password'])) {
            $query = "SELECT acronym, name, admin FROM RM_User WHERE acronym = ? AND password = md5(concat(?, salt))";

            $params = array(
                $this->options['acronym'],
                $this->options['password'],
            );

            $db = new CDatabase($this->options['database']);
            $result = $db->ExecuteSelectQueryAndFetchAll($query, $params);

            if (isset($result[0])) {
                $_SESSION['user'] = $result[0];
            } else {
                throw new Exception('Could not login.');
            }
        }
    }

    /**
     * Check if theres a valid and active user logged in in session.
     */
    public function UserStatusBool() {
        if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if there is an authorised user in session.
     * @return string
     */
    public function UserStatusString() {
        if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
            $output = "You are logged in as: {$_SESSION['user']->acronym} ({$_SESSION['user']->name})";
        } else {
            $output = "You are NOT logged in.";
        }
        return $output;
    }

    /**
     * Logout user, unset session variable
     */
    public function LogoutUser() {
        unset($_SESSION['user']);
    }

}
