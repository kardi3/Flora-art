<?php

/**
 * User_DataTables_Group
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class User_DataTables_Group extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'User_DataTables_Adapter_Group';
    }
}

