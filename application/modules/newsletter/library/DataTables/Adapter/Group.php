<?php

/**
 * Newsletter_DataTables_Adapter_Group
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Newsletter_DataTables_Adapter_Group extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        return $q;
    }
}

