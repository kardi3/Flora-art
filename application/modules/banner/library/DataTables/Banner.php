<?php

/**
 * Banner_DataTables_Banner
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Banner_DataTables_Banner extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Banner_DataTables_Adapter_Banner';
    }
}

