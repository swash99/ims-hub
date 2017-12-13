<?php
require_once "database_table.php";

class ContactsTable extends DatabaseTable{

    public static function add_new_contact($name, $email) {
        $sql = "INSERT INTO Contacts (name, email)
                VALUES('$name', '$email')";

        if (parent::query($sql)) {
            return true;
        } else {
            throw new Exception("add_new_contact query failed");
        }
    }

    public static function get_contacts() {
        $sql = "SELECT * FROM Contacts
                ORDER BY name ASC";

        return parent::query($sql);
    }

    public static function delete_contact($contact_id) {
        $sql = "DELETE FROM Contacts WHERE id = '$contact_id'";

        return parent::query($sql);
    }

    public static function update_contact_details($contact_id, $name, $email){
        $sql = "UPDATE Contacts 
                SET name = '$name',
                    email = '$email'
                WHERE id = $contact_id";

        return parent::query($sql);
    }

}
?>
