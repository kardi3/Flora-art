<?php

/**
 * News_DataTables_Tag
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class News_DataTables_Tag extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'News_DataTables_Adapter_Tag';
    }
}

