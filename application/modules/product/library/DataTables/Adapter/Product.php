<?php

/**
 * Product_DataTables_Adapter_Product
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Product_DataTables_Adapter_Product extends Default_DataTables_Adapter_AdapterAbstract {
    protected function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->leftJoin('x.Translation t');
        $q->leftJoin('x.Category cat');
        $q->leftJoin('cat.Translation ct');
        
        return $q;
    }
}

