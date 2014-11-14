<?php

/**
 * Offer_DataTables_OfferSerwis1
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Offer_DataTables_Offer extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Offer_DataTables_Adapter_Offer';
    }
}

