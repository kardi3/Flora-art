<?php

/**
 * News_DataTables_adapter_Tag
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class News_DataTables_Adapter_Tag extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        return $q;
    }
}

