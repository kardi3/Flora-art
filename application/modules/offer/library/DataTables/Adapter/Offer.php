<?php

/**
 * Offer_DataTables_adapter_OfferSerwis1
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Offer_DataTables_Adapter_Offer extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        $q->addSelect('xt.*');
        $q->leftJoin('x.Translation xt');
        return $q;
    }
}

