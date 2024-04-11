<?php

namespace FwTest\Classes;

class Shop
{
    /**
     * The table name
     *
     * @access  protected
     * @var     string
     */
    protected static $table_name = 'magasin';

    /**
     * The primary key name
     *
     * @access  protected
     * @var     string
     */
    protected static $pk_name = 'magasin_id';

    /**
     * The object datas
     *
     * @access  private
     * @var     array
     */
    private $_array_datas = array();

    /**
     * The object id
     *
     * @access  private
     * @var     int
     */
    private $id;

    /**
     * The lang id
     *
     * @access  private
     * @var     int
     */
    private $lang_id = 1;

    /**
     * The link to the database
     *
     * @access  public
     * @var     object
     */
    public $db;

    /**
     * Shop constructor.
     *
     * @param      $db
     * @param      $datas
     *
     * @throws Class_Exception
     */
    public function __construct($db, $datas)
    {
        if (($datas != intval($datas)) && (!is_array($datas))) {
            throw new Class_Exception('The given datas are not valid.');
        }

        $this->db = $db;

        if (is_array($datas)) {
            $this->_array_datas = array_merge($this->_array_datas, $datas);
        } else {
            $this->_array_datas[self::$pk_name] = $datas;
        }
    }

    /**
     * Get the list of shop.
     *
     * @param      $db
     * @param      $begin
     * @param      $end
     *
     * @return     array of Shop
     */
    public static function getAll($db, $begin = 0, $end = 15, $filters = [])
    {
        $sql_get = "SELECT s.* FROM " . self::$table_name . " s";
        
        $params = [];

        $array_shop = [];
        
        if (count($filters) > 0) {
            $count = 0;
            foreach ($filters as $column => $value) {
                if ($count == 0)
                    $sql_get .= " WHERE " . $column . " = :" . $column;
                else
                    $sql_get .= " AND " . $column . " = :" . $column;
                $params = [...$params, $column => $value];
                $count++;
            }
        }

        $sql_get .= " LIMIT " . $begin . ", " . $end;

        $result = $db->fetchAll($sql_get, $params);

        if (!empty($result)) {
            foreach ($result as $shop) {
                $shop = new Shop($db, $shop);

                $array_shop[] = [
                    "magasin_nom" => $shop->__get("magasin_nom"),
                    "magasin_type" => $shop->__get("magasin_type"),
                ];
            }
        }

        return $array_shop;
    }

    /**
     * Get one shop.
     *
     * @param      $db
     * @param      $filters
     *
     * @return     array of Shop
     */
    public static function getOne($db, $id, $filters = [])
    {
        $sql_get = "SELECT s.* FROM " . self::$table_name . " s WHERE " . self::$pk_name . " = :magasin_id";

        $params = ["magasin_id" => $id];
        /*
        if(count($filters) > 0) {
            foreach($filters as $column => $value) {
                $sql_get .= " AND " . $column . " = :".$column;
                $params = [...$params, $column => $value];
            }
        }
        */

        return $db->fetchOne($sql_get, $params);
    }

    /**
     * Delete a shop.
     *
     * @return     bool if succeed
     */
    public function delete()
    {
        $id = $this->getId();
        $sql_delete = "DELETE FROM " . self::$table_name . " WHERE " . self::$pk_name . " = ?";

        return $this->db->query($sql_delete, [$id]);
    }

    /**
     * Get the primary key
     *
     * @return     int
     */
    public function getId()
    {
        return $this->_array_datas[self::$pk_name];
    }

    /**
     * Access properties.
     *
     * @param      $param
     *
     * @return     string
     */
    public function __get($param)
    {

        $array_datas = $this->_array_datas;

        // Let's check if an ID has been set and if this ID is valid
        if (!empty($array_datas[self::$pk_name])) {

            // If it has been set, then try to return the data
            if (array_key_exists($param, $array_datas)) {
                return $array_datas[$param];
            }

            // Let's dispatch all the values in $_array_datas
            $this->_dispatch();

            $array_datas = $this->_array_datas;

            if (array_key_exists($param, $array_datas)) {

                return $array_datas[$param];
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    private function _dispatch()
    {
        $array_datas = $this->_array_datas;

        if (empty($array_datas)) {
            return false;
        }

        $sql_dispatch = "SELECT s.*, 
                                IF (magasin_lang_nom IS NULL, magasin_nom, magasin_lang_nom) magasin_nom,
                                IF (magasin_lang_type IS NULL, magasin_type, magasin_lang_type) magasin_type,              
            FROM magasin s, magasin_lang sl
            WHERE s.magasin_id = :magasin_id
            AND sl.fk_lang_id = :lang_id
            AND sl.fk_magasin_id = s.magasin_id";

        $params = [
            'magasin_id' => $array_datas['magasin_id'],
            'lang_id' => 1,
        ];

        $array_shop = $this->db->fetchRow($sql_dispatch, $params);

        // If the request has been executed, so we read the result and set it to $_array_datas
        if (is_array($array_shop)) {
            $this->_array_datas = array_merge($array_datas, $array_shop);
            return true;
        }

        return false;
    }

    /**
     * Create a new shop
     *
     * @return     bool
     */
    static function post($db, $datas)
    {
        $sql_post = "INSERT magasin (magasin_nom, magasin_type) VALUES (?, ?)";

        return  $db->insertRow($sql_post, [$datas['magasin_nom'], $datas['magasin_type']]);
    }

    /**
     * Update a new shop
     *
     * @return     bool
     */
    public function update($datas)
    {
        $id = $this->getId();

        $sql_update = "UPDATE magasin SET magasin_nom = ?, magasin_type = ? WHERE magasin_id = ?";

        return  $this->db->query($sql_update, [$datas['magasin_nom'], $datas['magasin_type'], $id]);
    }
}
