<?php

/**
 * AdapterInterface
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
interface Default_DataTables_Adapter_AdapterInterface {

    public function getTable();
    public function getQuery();
    public function getData();
    public function getColumns();
    public function getSearchFields();
}

