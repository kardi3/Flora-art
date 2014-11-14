<?php

/**
 * Slider_DataTables_SlideLayer
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Slider_DataTables_SlideLayer extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Slider_DataTables_Adapter_SlideLayer';
    }
}

