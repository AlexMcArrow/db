<?php

/**
 * @copyright Alex McArrow 2013
 * @author Alex McArrow
 * @package DB
 * @version 1.41
 */
class DB {

    private static $LINK;
    private static $DBNAME;
    private static $DSN;
    public static $QL;

    function __construct ($DSN) {
        if (!is_array ($DSN)) {
            throw new Exception ('DB: wrong DSN array', 1);
        }
        self::$DSN = $DSN;
        self::$DBNAME = $DSN['base'];
        self::_connect ();
        return TRUE;
    }

    public static function query ($sql) {
        self::$QL[] = $sql;
        return self::$LINK->multi_query ($sql);
    }

    public static function lastid () {
        return self::$LINK->insert_id;
    }

    public static function key_value ($result, $key, $value) {
        $out = array ();
        if (is_array ($result)) {
            foreach ($result as $rk => $rv) {
                $out[$rv[$key]] = $rv[$value];
            }
        }
        return $out;
    }

    public static function count ($result) {
        if (isset ($result['count'])) {
            return $result['count'];
        }
        return 0;
    }

    public static function fetch_query ($sql, $id_field = FALSE, $id_subfield = FALSE) {
        self::$QL[] = $sql;
        $out = array ();
        if (self::$LINK->multi_query ($sql)) {
            do {
                if ($result = self::$LINK->store_result ()) {
                    if ($result->num_rows > 1) {
                        while ($row = $result->fetch_assoc ()) {
                            if ($id_field && $id_field !== TRUE) {
                                if ($id_subfield && $id_subfield !== TRUE) {
                                    if (!isset ($out[$row[$id_field]])) {
                                        $out[$row[$id_field]] = array ();
                                    }
                                    $out[$row[$id_field]][$row[$id_subfield]] = $row;
                                } else {
                                    $out[$row[$id_field]] = $row;
                                }
                            } else {
                                $out[] = $row;
                            }
                        }
                    } else {
                        if ($result->num_rows > 0) {
                            if ($id_field && $id_field !== TRUE) {
                                $row = $result->fetch_assoc ();
                                if ($id_subfield && $id_subfield !== TRUE) {
                                    if (!isset ($out[$row[$id_field]])) {
                                        $out[$row[$id_field]] = array ();
                                    }
                                    $out[$row[$id_field]][$row[$id_subfield]] = $row;
                                } else {
                                    $out[$row[$id_field]] = $row;
                                }
                            } else {
                                if ($id_field === FALSE) {
                                    $out[] = $result->fetch_assoc ();
                                } else {
                                    $out = $result->fetch_assoc ();
                                }
                            }
                        } else {
                            $out = FALSE;
                        }
                    }
                    $result->free ();
                }
            } while ((self::$LINK->more_results ()) ? self::$LINK->next_result () : FALSE);
        }
        return $out;
    }

    private static function _connect () {
        self::$LINK = new mysqli (self::$DSN['host'], self::$DSN['user'], self::$DSN['pass'], self::$DSN['base']);
        if (mysqli_connect_errno ()) {
            throw new Exception ('DB: ' . mysqli_connect_error (), 500);
        }
        self::query ('SET NAMES ' . self::$DSN['char']);
    }

}