<?php

/**
 * User_DataTables_Agent
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class User_DataTables_Agent extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'User_DataTables_Adapter_Agent';
    }
}

