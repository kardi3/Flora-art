<?php

/**
 * User_DataTables_Client
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class User_DataTables_Client extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'User_DataTables_Adapter_Client';
    }
}

