<?php

class PractitionerAPI {

    protected $Organisation;

    function __construct(Organisation $Organisation) {
        $this->Organisation = $Organisation;
    }

    // not tested
    function DELETE($val) {
        $where = "Id = '$val'";
        $q = "DELETE FROM practitioners WHERE OrgID = '" . $this->Organisation->OrgID . "' AND " . $where;
        $stmt = MainDb::getInstance()->prepare($q);
        $stmt->execute();
        if($stmt->num_rows != 1) {
            throw new Exception($stmt->error);
        }
        else {
            return $stmt->num_rows;
        }
        
    }
    
    function GET($val = 'ignore') {

        $where = filter_input(INPUT_GET, 'where');
        $order = filter_input(INPUT_GET, 'order');
        $limit = filter_input(INPUT_GET, 'limit');

        $part1 = $this->parseWhere($where);
        $part2 = $this->parseOrder($order);
        
        return $this->selectFromPractitioners($part1 . ' ' . $part2);
    }

    protected function parseWhere($where) {
        $tokens = array(':', 'EQ', 'GT', 'GTE', 'LT', 'LTE', 'NE', ',');
        $replace = array(' ', '=', '>', '>=', '<', '<=', '!=', ' AND ');

        $where = str_replace($tokens, $replace, $where);
        return $where;
    }
    
    protected function parseOrder($order) {
        if(!($order)) {
            return "";
        }
        $tokens = array(':', 'order');
        $replace = array(' ', 'order by ');
        $result = str_replace($tokens, $replace, $order);
        return ' ORDER BY ' . $result;
    }

    
    protected function selectFromPractitioners($where) {
        $q = "SELECT * FROM practitioners WHERE OrgID = '" . $this->Organisation->OrgID . "' AND " . $where;
        $sql = MainDb::getInstance();
        $myArray = array();
        $response = "";
        if ($result = $sql->query($q)) {

            while ($row = $result->fetch_array(MYSQL_ASSOC)) {
                $myArray[] = $row;
            }
        }

        $result->close();
        $sql->close();
        return $myArray;
    }

}
