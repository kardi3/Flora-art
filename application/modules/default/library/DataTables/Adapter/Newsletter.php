<?php

/**
 * Order_DataTables_Adapter_Delivery
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Default_DataTables_Adapter_Newsletter extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('n');
        $q->select('n.*');
        return $q;
    }
}

