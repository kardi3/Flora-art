<?php

/**
 * DataTablesInterface
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
interface Default_DataTables_DataTablesInterface {
    
    public function setAdapter(Default_DataTables_Adapter_AdapterInterface $adapter);
    public function getAdapterClass();
    public function getResult();
}

