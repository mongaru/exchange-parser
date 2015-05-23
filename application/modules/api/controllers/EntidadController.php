<?php

class Api_EntidadController extends Zend_Controller_Action
{
    public function cotizacionAction()
    {
        $entidad = $this->_request->getParam('entidad', '');

        if ($entidad == '')
        {
            $this->_helper->json(array('status' => 'error', 'message' =>'Entidad no encontrada.'));
            return;
        }

        $parser = new Code_CambiosChacoParser();

        $cotizaciones = $parser->ejecutar('http://www.exchange.dev/chaco.html');

        $this->_helper->json(array('status' => 'correcto', 'fecha' => date('d-m-Y H:i:s'), 'datos' => $cotizaciones));
    }

    public function cambioschacoAction()
    {
        $parser = new Code_CambiosChacoParser();

        $cotizaciones = $parser->ejecutar('http://www.exchange.dev/chaco.html');

        $this->_helper->json(array('status' => 'correcto', 'fecha' => date('d-m-Y H:i:s'), 'datos' => $cotizaciones));
    }
}