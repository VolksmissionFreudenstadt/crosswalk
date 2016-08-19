<?php

namespace VMFDS\Scribe;

class CSV
{

    protected $filename = '';
    protected $data = [];
    protected $columns = [];
    protected $separator = ';';
    protected $indexColumn = NULL;
    protected $decodeFunction = NULL;


    static public function load($filename, $separator=',', $indexColumn=NULL, $decodeFunction=NULL)
    {
        $object = new CSV();
        $object->setFilename($filename);
        $object->setSeparator($separator);
        $object->setIndexColumn($indexColumn);
        $object->setDecodeFunction($decodeFunction);
        $object->readData();
        return $object;
    }

    static public function get($filename, $separator=',', $indexColumn=NULL, $decodeFunction=NULL) {
        $object = self::load($filename, $separator, $indexColumn);
        return $object->getData();
    }

    public static function quote($text)
    {
        if (!is_numeric($text)) {
            $text = '"' . $text . '"';
        }
        return $text;
    }

    public static function unquote($text)
    {
        return trim($text, '\'"');
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * @param string $separator
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    /**
     * @return null
     */
    public function getIndexColumn()
    {
        return $this->indexColumn;
    }

    /**
     * @param null $indexColumn
     */
    public function setIndexColumn($indexColumn)
    {
        $this->indexColumn = $indexColumn;
    }

    /**
     * @return null
     */
    public function getDecodeFunction()
    {
        return $this->decodeFunction;
    }

    /**
     * @param null $decodeFunction
     */
    public function setDecodeFunction($decodeFunction)
    {
        $this->decodeFunction = $decodeFunction;
    }



    /**
     * Daten aus CSV-Datei einlesen
     */
    public function readData() {
        $raw = file_get_contents($this->filename);
        if (($this->decodeFunction) && (function_exists($this->decodeFunction))) $raw=$decodeFunction($raw);
        $recs = explode("\n", str_replace("\r\n", "\n", $raw));
        foreach ($recs as $key => $rec) {
            $recs[$key] = explode($this->separator, $rec);
        }
        $this->columns = $recs[0];
        unset ($recs[0]);
        $emptyRow = [];
        foreach ($this->columns as $column) {
            $emptyRow[$column] = '';
        }
        foreach ($recs as $rowKey => $rec) {
            $row = $emptyRow;
            foreach ($rec as $colKey => $col) {
                if (trim($col = CSV::unquote($col))) {
                    if (isset($this->columns[$colKey])) {
                        $row[$this->columns[$colKey]] = $col;
                    }
                }
            }
            if ((isset($this->indexColumn)) && (isset($row[$this->indexColumn]))) {
                $rowKey = $row[$this->indexColumn];
            }
            if (trim(join('', $row))) $this->data[$rowKey] = $row;
        }
        return $this->data;
    }

    public function addColumn($columnTitle) {
        $this->columns[] = $columnTitle;
    }

    public function removeColumn($columnTitle) {
        unset($this->columns[$columnTitle]);
        foreach ($this->data as $row => $rec) {
            unset($data[$row][$columnTitle]);
        }
    }

}