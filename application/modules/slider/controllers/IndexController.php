<?php

class Slider_IndexController extends MF_Controller_Action {
    
    
    public function sliderAction() {
        $this->_helper->viewRenderer->setResponseSegment('slider');
        $sliderService = $this->_service->getService('Slider_Service_Slider');
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        $mainSliderSlides = $sliderService->getAllForSlider("main");
        $mainSlides = array();
        foreach($mainSliderSlides[0]['Slides'] as $slide):
            $layers = $slideLayerService->getLayersForSlide($slide['id']);
            $slide['Layers'] = $layers;
            $mainSlides[] = $slide;
        endforeach;
        $this->view->assign('mainSlides',$mainSlides);
        $this->_helper->viewRenderer->setResponseSegment('slider');
    }
    
	   
    
    
    
}
?>
