<?php

/**
 * @file FavModel Class.  Represents a favorite and handles CRUD operations.
 */
class FavModel {

  /**
   * Lat
   */
  var $lat = NULL;

  /**
   * Lon
   */
  var $lon = NULL;

  /**
   * Address
   */
  var $address = NULL;

  /**
   * Name
   */
  var $name = NULL;

  /**
   * Id
   */
  var $id = NULL;

  /**
   * Database connection.
   */
  var $db = NULL;

  /**
   * Constructor function. Loads model from database if the id is known.
   * 
   * @param $db
   *   Database connection
   * @param integer $id
   *   The id of the favorite, optional.
   */
  function __construct($db, $id = false) {
    $this->db = $db;
    if ($id) {
      $this->id = $id;
      $this->load();
    }
  }

  /**
   * Returns a property of the favorite.
   * 
   * @return id
   *   The favorite's id, false if not yet saved.
   */
  function get($property) {
    if (!empty($this->{$property})) {
      return $this->{$property};
    }
    else {
      return false;
    }
  }

  /**
   * Returns all user-entered properties of the favorite as an array.
   */
  function getAll() {
    return array(
      'name' => $this->get('name'),
      'lat' => $this->get('lat'),
      'lon' => $this->get('lon'),
      'address' => $this->get('address'),
      'id' => $this->get('id'),
    );
  }

  /**
   * Sets a property of the favorite.
   * 
   * @param $property
   *   The property name to be set.
   * @param $value
   *   The value of the property.
   */
  function set($property, $value) {
    $this->{$property} = $value;
  }

  /**
   * Sets multiple properties of a favorite.
   * 
   * @param $properties
   *   An array of property => value pairs to be set.
   */
  function setMultiple($properties) {
    foreach ($properties as $property => $value) {
      $this->set($property, $value);
    }
  }

  function save() {
    // If this is a new favorite...
    if (empty($this->id)) {

      $statement = $this->db->prepare('INSERT INTO favs (name, lat, lon, address) VALUES (:name, :lat, :lon, :address)');
      foreach (array('name', 'lat', 'lon', 'adress') as $property) {
        $placeholders[':' . $property] = $this->get('property');
      }
      var_export($placeholders);
      exit();
      $statement->execute($placeholders);
      $this->set('id', $this->db->lastInsertId());

      return $this->get('id');
    }
    // If this is an update to an existing favorite...
    else {

      // Prepare the SQL.
      $sql = 'UPDATE favs SET ';

      // Loop through each included field to prepare the piece of the SQL
      // statement and the array of PDO::execute() placeholders.
      foreach ($this->getAll() as $field => $value) {
        if (!empty($value)) {
          $updates[] = $field . " = :" . $field;
          $params[':' . $field] = $value;
        }
      }

      // Turn the update SQL into a string separated by commas and include the
      // where clause.
      $sql .= implode(', ', $updates);
      $sql .= " WHERE id = :id";

      // Execute the query.
      $statement = $this->db->prepare($sql);
      $statement->execute($params);

      return $this->get('id');
    }
  }

  /**
   * Load data from the db for an existing fav.
   */
  function load() {
    $statement = $this->db->prepare('SELECT * FROM favs WHERE id = :id');
    $statement->execute(array(':id' => $this->get('id')));
    $data = $statement->fetch(PDO::FETCH_ASSOC);
    $this->setMultiple($data);
  }

  /**
   * Delete this favorite.
   */
  function delete() {
    if ($id) {
      // Execute the delete query.
      $statement = $this->db->prepare('DELETE FROM favs WHERE id = :id');
      $statement->execute(array(':id' => $this->get('id')));
    }
  }

}


