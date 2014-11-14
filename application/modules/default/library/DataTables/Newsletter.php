<?php

/**
 * Order_DataTables_Delivery
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Default_DataTables_Newsletter extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Default_DataTables_Adapter_Newsletter';
    }
}

