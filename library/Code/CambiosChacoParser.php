<?php

include_once('Amedia/simple_html_dom.php');
//require_once 'Zend/Application.php';

/**
*
*/
class Code_CambiosChacoParser
{
    function __construct()
    {
    }

    function ejecutar($fileURL)
    {
        // http://stackoverflow.com/questions/272361/how-can-i-handle-the-warning-of-file-get-contents-function-in-php

        // $html = file_get_html('http://www.cambioschaco.com.py/php/chaco_cotizaciones_nuevo.php');
        //$dom = new Code_SimpleHTMLDom();
        // $html = $dom->file_get_html('chaco.html');
        // $html = @file_get_html('chaco.html');
        $html = @file_get_html($fileURL);
        //$html = file_get_html('youtube.htm');
        //$html = file_get_html('Product.ibatis.xml');

        //$ret = $html->find('table table tr', 0)->children(0)->innertext;


        //echo '<pre>'; var_dump($ret); echo '</pre>'; die;


        // $ret = $html->find('table table tr', 0)->children(0)->innertext;

        if ($html === FALSE)
        {
            return FALSE;
        }

        $cont = 0;
        $patronNombre = '/;(\s*\w\s*)*</';
        $patronNumero = '/\d+(\.)?\d+\,00/';

        foreach ($html->find('table table tr') as $element)
        {

            if (strpos($element->innertext, "paises") !== false)
            {
                $cont++;

                $paisNombre = "";
                $valorVenta = "";
                $valorCompra = "";

                $paisNombreElemento = $element->children(1);

                // las filas de cabecera tienen un solo children td, entonces continuar
                if ($paisNombreElemento === null)
                {
                    continue;
                }

                // extraer el nombre del pais de la cotizacion si existiese
                preg_match($patronNombre, $paisNombreElemento->innertext, $coincidencias, PREG_OFFSET_CAPTURE);

                // si hay entonces quitar el nombre del pais
                if (count($coincidencias) > 0)
                {
                    // obtener el nombre del pais
                    foreach ($coincidencias as $encontrados)
                    {
                        $paisNombre = $encontrados[0];
                        $paisNombre = str_replace(" ", "", $paisNombre);
                        $paisNombre = str_replace(";", "", $paisNombre);
                        $paisNombre = str_replace("<", "", $paisNombre);
                        echo '<pre>'; var_dump($paisNombre); echo '</pre>';
                        break; // solo se obtiene el primer valor de coincidencia
                    }
                }

                if ($paisNombre === '')
                {
                    continue;
                }

                $numeroCompraElemento = $element->children(2);
                $numeroVentaElemento = $element->children(3);
                // las filas de cabecera tienen un solo children td, entonces continuar
                if ($numeroCompraElemento === null || $numeroVentaElemento === null)
                {
                    continue;
                }

                // extraer los montos en guaranies
                preg_match($patronNumero, $numeroCompraElemento->innertext, $coincidencias, PREG_OFFSET_CAPTURE);

                // si hay entonces quitar el nombre del pais
                if (count($coincidencias) > 0)
                {
                    // obtener el nombre del pais
                    foreach ($coincidencias as $encontrados)
                    {
                        $valorCompra = $encontrados[0];
                        $valorCompra = str_replace(" ", "", $valorCompra);
                        $valorCompra = str_replace(";", "", $valorCompra);
                        $valorCompra = str_replace("<", "", $valorCompra);
                        echo '<pre>'; var_dump($valorCompra); echo '</pre>';
                        break; // solo se obtiene el primer valor de coincidencia
                    }
                }

                        // extraer los montos en guaranies
                preg_match($patronNumero, $numeroVentaElemento->innertext, $coincidencias, PREG_OFFSET_CAPTURE);

                // si hay entonces quitar el nombre del pais
                if (count($coincidencias) > 0)
                {
                    // obtener el nombre del pais
                    foreach ($coincidencias as $encontrados)
                    {
                        $valorVenta = $encontrados[0];
                        $valorVenta = str_replace(" ", "", $valorVenta);
                        $valorVenta = str_replace(";", "", $valorVenta);
                        $valorVenta = str_replace("<", "", $valorVenta);
                        echo '<pre>'; var_dump($valorVenta); echo '</pre>';
                        break; // solo se obtiene el primer valor de coincidencia
                    }
                }

                //$completo = $paisNombre.';'.$valorVenta.';'.$valorCompra;

                //echo '<pre>'; var_dump($completo); echo '</pre>';


                // print_r($coincidencias);
                // echo '<pre>'; var_dump($coincidencias); echo '</pre>';

                // $patron = '/\d+(\.)?\d+\,00/';

                // foreach ($element->children() as $child)
                // {
                //     // preg_match($patrón, $sujeto, $coincidencias, PREG_OFFSET_CAPTURE, 3);
                // preg_match($patron, $child->innertext, $coincidencias, PREG_OFFSET_CAPTURE);
                // // print_r($coincidencias);
                // echo '<pre>'; var_dump($coincidencias); echo '</pre>';
                // }
            }


        //;(\s*\w\s*)*<

        }
        // echo '<pre>'; var_dump($cont); echo '</pre>'; die;
        //echo $element->src . '<br>';
    }


    function getparents(){
        return $this->parent;
    }

    function getAlowResources(){
        return $this->AlowResources;
    }
    function getAlowResourcesfor($index){
        if(isset($this->AlowResources[$index])){
            return $this->AlowResources[$index];
        } else {
            return null;
        }
    }
    function getRol(){
        return $this->rol;
    }


}
 ?>