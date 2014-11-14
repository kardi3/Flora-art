<?php

/**
 * Product_DataTables_Adapter_Category
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Product_DataTables_Adapter_Category extends Default_DataTables_Adapter_AdapterAbstract {
    
    protected function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->leftJoin('x.Translation t');
        $q->addWhere('x.level > 0');
        return $q;
    }
    
}

